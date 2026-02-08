<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\FaceTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaceRecognitionController extends Controller
{
    private string $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('FACE_RECOGNITION_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    public function index()
    {
        $employees = Employee::select('id', 'first_name', 'last_name', 'badge_number')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('face-recognition.index', compact('employees'));
    }

    public function capture()
    {
        return view('face-recognition.capture');
    }

    public function enroll(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'image' => ['required', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        try {
            // Send to Python backend for enrollment
            $response = Http::timeout(30)->post($this->pythonServiceUrl . '/enroll', [
                'employee_id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'badge_number' => $employee->badge_number,
                'image' => $data['image'],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    // Also store a backup copy locally
                    $imageContent = $this->decodeImage($data['image']);
                    $relativePath = 'face_templates/' . $employee->id . '_' . Str::uuid() . '.png';
                    Storage::disk('public')->put($relativePath, $imageContent);

                    FaceTemplate::create([
                        'employee_id' => $employee->id,
                        'image_path' => $relativePath,
                        'embedding' => null,
                    ]);

                    return redirect()->route('face-recognition.index')
                        ->with('success', 'Face enrolled successfully. (' . ($result['message'] ?? 'Enrollment successful') . ')');
                } else {
                    return redirect()->back()
                        ->with('error', 'Enrollment failed: ' . ($result['message'] ?? 'Unknown error'));
                }
            } else {
                return redirect()->back()
                    ->with('error', 'Face recognition service error. HTTP ' . $response->status());
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Face recognition service unavailable. Is the Python backend running on port 5000? Error: ' . $e->getMessage());
        }
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'image' => ['required', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        try {
            // Send to Python backend for verification
            $response = Http::timeout(30)->post($this->pythonServiceUrl . '/match', [
                'image' => $data['image'],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success'] && $result['employee']['employee_id'] == $employee->id) {
                    // Face matches the selected employee
                    $now = Carbon::now();

                    AttendanceLog::create([
                        'device_id' => null,
                        'badge_number' => $employee->badge_number,
                        'employee_id' => $employee->id,
                        'log_datetime' => $now,
                        'status' => 'In',
                        'punch_type' => 'Face',
                    ]);

                    return redirect()->route('face-recognition.index')
                        ->with('success', 'Face verified! Time logged. (Confidence: ' . round($result['employee']['confidence']) . '%)');
                } else {
                    return redirect()->back()
                        ->with('error', 'Face does not match the selected employee or no face found.');
                }
            } else {
                return redirect()->back()
                    ->with('error', 'Face verification failed.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Face recognition service unavailable. Is the Python backend running? Error: ' . $e->getMessage());
        }
    }

    public function clear(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        // Delete local FaceTemplate records and files
        $templates = FaceTemplate::where('employee_id', $employee->id)->get();
        foreach ($templates as $tpl) {
            if ($tpl->image_path && Storage::disk('public')->exists($tpl->image_path)) {
                Storage::disk('public')->delete($tpl->image_path);
            }
            $tpl->delete();
        }

        // Ask Python backend to clear embeddings for this employee
        try {
            $resp = Http::timeout(10)->asForm()->post($this->pythonServiceUrl . '/clear/' . $employee->id);
            if (!$resp->successful()) {
                // Try DELETE as fallback
                $resp = Http::timeout(10)->delete($this->pythonServiceUrl . '/clear/' . $employee->id);
            }
        } catch (\Exception $e) {
            // Ignore backend error; local cleanup already done
        }

        return redirect()->route('face-recognition.index')
            ->with('success', 'Enrollment cleared for ' . $employee->last_name . ', ' . $employee->first_name . '.');
    }

    private function decodeImage(string $dataUrl): string
    {
        if (strpos($dataUrl, ',') !== false) {
            [$meta, $content] = explode(',', $dataUrl, 2);
            return base64_decode($content);
        }

        return base64_decode($dataUrl);
    }
}

