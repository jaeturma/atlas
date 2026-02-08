<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceLogsUploadController extends Controller
{
    public function uploadFile(Request $request)
    {
        \Log::info('uploadFile called', [
            'hasFile' => $request->hasFile('file'),
            'files' => array_keys($request->allFiles()),
        ]);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400', // 100MB
        ]);

        if ($validator->fails()) {
            \Log::warning('Validation failed', ['errors' => $validator->errors()->all()]);
            return response()->json([
                'success' => false,
                'error' => 'Invalid file. Maximum size is 100MB.',
            ], 422);
        }

        try {
            if (!$request->hasFile('file')) {
                throw new \Exception('No file in request');
            }

            $file = $request->file('file');
            
            if (!$file->isValid()) {
                throw new \Exception('Uploaded file is not valid: ' . $file->getErrorMessage());
            }

            $extension = strtolower($file->getClientOriginalExtension());

            \Log::info('File received', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'extension' => $extension,
                'mimeType' => $file->getMimeType(),
                'isValid' => $file->isValid(),
            ]);

            // Validate file extension
            if (!in_array($extension, ['csv', 'log', 'dat', 'txt'])) {
                throw new \Exception('File must be CSV, LOG, DAT, or TXT format');
            }

            // Create uploads directory if it doesn't exist
            $uploadsDir = storage_path('app/uploads/temp');
            if (!is_dir($uploadsDir)) {
                if (!mkdir($uploadsDir, 0755, true)) {
                    throw new \Exception('Could not create uploads directory');
                }
            }

            // Generate a unique filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $fullPath = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

            \Log::info('About to save file', [
                'from' => $file->getPathname(),
                'to' => $fullPath,
                'sourceExists' => file_exists($file->getPathname()),
            ]);

            // Move the uploaded file
            if (!$file->move($uploadsDir, $filename)) {
                throw new \Exception('Failed to move uploaded file');
            }

            // Verify the file exists
            if (!file_exists($fullPath)) {
                throw new \Exception('File not found after move operation');
            }

            \Log::info('File saved successfully', [
                'fullPath' => $fullPath,
                'exists' => file_exists($fullPath),
                'size' => filesize($fullPath),
            ]);

            return response()->json([
                'success' => true,
                'filePath' => $fullPath,
                'fileName' => $file->getClientOriginalName(),
                'message' => 'File uploaded successfully',
            ]);
        } catch (\Throwable $e) {
            \Log::error('File upload error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
