<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendancePushController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Push Protocol Endpoints (No auth required - devices push directly)
Route::post('/attendance/push', [AttendancePushController::class, 'receivePush'])->name('api.attendance.push');
Route::get('/attendance/push/health', [AttendancePushController::class, 'healthCheck'])->name('api.attendance.push.health');
// Authenticated API Routes
Route::middleware('auth')->group(function () {
    // Face Recognition API
    Route::post('/face-recognition/match', [\App\Http\Controllers\Api\FaceRecognitionController::class, 'match'])->name('api.face-recognition.match');
    
    // Employees API
    Route::get('/employees', function () {
        $employees = \App\Models\Employee::select('id', 'first_name', 'last_name', 'badge_number')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        return response()->json(['employees' => $employees]);
    });
});