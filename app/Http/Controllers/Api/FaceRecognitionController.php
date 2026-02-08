<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\FaceTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceRecognitionController extends Controller
{
    private string $pythonServiceUrl;

    public function __construct()
    {
        $this->pythonServiceUrl = env('FACE_RECOGNITION_SERVICE_URL', 'http://127.0.0.1:5000');
    }

    /**
     * Match a captured face against enrolled templates via Python backend.
     * Returns the matched employee or a "no match" message.
     */
    public function match(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'string'],
        ]);

        try {
            // Call Python face recognition service
            $response = Http::timeout(30)->post($this->pythonServiceUrl . '/match', [
                'image' => $data['image'],
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if ($result['success'] && (isset($result['employee']) || isset($result['matches']))) {
                    $employee = null;
                    if (isset($result['employee'])) {
                        $employee = [
                            'id' => $result['employee']['employee_id'],
                            'first_name' => explode(' ', $result['employee']['name'])[0],
                            'last_name' => implode(' ', array_slice(explode(' ', $result['employee']['name']), 1)),
                            'badge_number' => $result['employee']['badge_number'],
                            'confidence' => $result['employee']['confidence'],
                            'face_index' => $result['employee']['face_index'] ?? 0,
                        ];
                    }

                    $matches = [];
                    if (isset($result['matches']) && is_array($result['matches'])) {
                        foreach ($result['matches'] as $match) {
                            $matches[] = [
                                'id' => $match['employee_id'],
                                'first_name' => explode(' ', $match['name'])[0],
                                'last_name' => implode(' ', array_slice(explode(' ', $match['name']), 1)),
                                'badge_number' => $match['badge_number'],
                                'confidence' => $match['confidence'],
                                'face_index' => $match['face_index'] ?? 0,
                            ];
                        }
                    }

                    return response()->json([
                        'success' => true,
                        'employee' => $employee,
                        'matches' => $matches,
                    ], 200);
                }

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'No face match found.',
                ], 200);
            } else {
                // Python service error
                return response()->json([
                    'success' => false,
                    'message' => 'Face recognition service error. Is the Python backend running?',
                ], 200);
            }
        } catch (\Exception $e) {
            // Connection error - fall back to demo mode
            return response()->json([
                'success' => false,
                'message' => 'Face recognition service unavailable. Running in demo mode.',
            ], 200);
        }
    }

    /**
     * Enroll a face for an employee (sends to Python backend).
     */
    public function enroll(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'image' => ['required', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        try {
            // Call Python enrollment service
            $response = Http::timeout(30)->post($this->pythonServiceUrl . '/enroll', [
                'employee_id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'badge_number' => $employee->badge_number,
                'image' => $data['image'],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success']) {
                    return redirect()->route('face-recognition.index')
                        ->with('success', $result['message'] ?? 'Face enrolled successfully.');
                } else {
                    return redirect()->back()
                        ->with('error', $result['message'] ?? 'Face enrollment failed.');
                }
            } else {
                return redirect()->back()
                    ->with('error', 'Face recognition service error.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Face recognition service unavailable. Is the Python backend running?');
        }
    }

    /**
     * Verify and log time (verify endpoint).
     */
    public function verify(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'image' => ['required', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        try {
            // Call Python matching service
            $response = Http::timeout(30)->post($this->pythonServiceUrl . '/match', [
                'image' => $data['image'],
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if ($result['success'] && $result['employee']['employee_id'] == $employee->id) {
                    // Match confirmed, log time
                    \App\Models\AttendanceLog::create([
                        'device_id' => null,
                        'badge_number' => $employee->badge_number,
                        'employee_id' => $employee->id,
                        'log_datetime' => now(),
                        'status' => 'In',
                        'punch_type' => 'Face',
                    ]);

                    return redirect()->route('face-recognition.index')
                        ->with('success', 'Time logged successfully.');
                } else {
                    return redirect()->back()
                        ->with('error', 'Face verification failed or does not match selected employee.');
                }
            } else {
                return redirect()->back()
                    ->with('error', 'Face verification failed.');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Face recognition service unavailable.');
        }
    }
}

