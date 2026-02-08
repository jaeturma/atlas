<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Device;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Attendance Push Controller
 * 
 * Handles incoming attendance log pushes from ADMS/Cloud devices (WL10, etc.)
 * These devices push data to the server via HTTP instead of being polled.
 */
class AttendancePushController extends Controller
{
    /**
     * Receive pushed attendance logs from ADMS/Cloud devices
     * 
     * Expected payload formats:
     * 1. ZKTeco ADMS format
     * 2. Cloud API format
     * 3. Generic JSON format
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receivePush(Request $request)
    {
        try {
            $payload = $request->all();
            
            Log::info('Attendance push received', [
                'ip' => $request->ip(),
                'payload' => $payload,
                'headers' => $request->headers->all(),
            ]);

            // Determine format and parse
            $logs = $this->parsePayload($payload, $request);

            if (empty($logs)) {
                Log::warning('No valid logs found in push payload', ['payload' => $payload]);
                return response()->json([
                    'success' => false,
                    'message' => 'No valid logs found in payload',
                ], 400);
            }

            $stored = 0;
            $skipped = 0;

            foreach ($logs as $logData) {
                // Find device by serial number or IP
                $device = $this->findDevice($logData['device_sn'] ?? null, $request->ip());

                if (!$device) {
                    Log::warning('Device not found for pushed log', [
                        'device_sn' => $logData['device_sn'] ?? 'unknown',
                        'ip' => $request->ip(),
                    ]);
                    $skipped++;
                    continue;
                }

                // Parse log datetime
                $logDateTime = $this->parseDateTime($logData['timestamp'] ?? $logData['record_time'] ?? null);

                if (!$logDateTime) {
                    Log::warning('Invalid timestamp in pushed log', ['log' => $logData]);
                    $skipped++;
                    continue;
                }

                // Determine status (IN/OUT) based on time-based rules
                $status = $this->determineStatus($logDateTime, $logData);

                // Check for duplicates
                $exists = AttendanceLog::where('device_id', $device->id)
                    ->where('badge_number', $logData['user_id'] ?? $logData['badge_number'])
                    ->where('log_datetime', $logDateTime)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Store the log
                AttendanceLog::create([
                    'device_id' => $device->id,
                    'badge_number' => $logData['user_id'] ?? $logData['badge_number'],
                    'log_datetime' => $logDateTime,
                    'status' => $status,
                    'punch_type' => $logData['type'] ?? $logData['punch_type'] ?? 0,
                ]);

                $stored++;
            }

            Log::info('Attendance push processed', [
                'stored' => $stored,
                'skipped' => $skipped,
                'total' => count($logs),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Processed {$stored} log(s), skipped {$skipped}",
                'stored' => $stored,
                'skipped' => $skipped,
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance push error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing push: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse incoming payload to extract attendance logs
     */
    private function parsePayload(array $payload, Request $request): array
    {
        $logs = [];

        // Format 1: Direct array of logs
        if (isset($payload['logs']) && is_array($payload['logs'])) {
            return $payload['logs'];
        }

        // Format 2: Single log
        if (isset($payload['user_id']) || isset($payload['badge_number'])) {
            return [$payload];
        }

        // Format 3: ZKTeco ADMS format
        if (isset($payload['attlog'])) {
            return is_array($payload['attlog']) ? $payload['attlog'] : [$payload['attlog']];
        }

        // Format 4: Cloud API format with data array
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        // Format 5: Try to parse as single record
        if (!empty($payload)) {
            return [$payload];
        }

        return [];
    }

    /**
     * Find device by serial number or IP address
     */
    private function findDevice(?string $serialNumber, string $ip): ?Device
    {
        // Try to find by serial number first
        if ($serialNumber) {
            $device = Device::where('serial_number', $serialNumber)->first();
            if ($device) {
                return $device;
            }
        }

        // Fall back to IP address
        return Device::where('ip_address', $ip)->first();
    }

    /**
     * Parse datetime from various formats
     */
    private function parseDateTime($timestamp): ?Carbon
    {
        if (!$timestamp) {
            return null;
        }

        try {
            // Unix timestamp
            if (is_numeric($timestamp)) {
                return Carbon::createFromTimestamp($timestamp);
            }

            // ISO 8601 or standard datetime string
            return Carbon::parse($timestamp);
        } catch (\Exception $e) {
            Log::error('Failed to parse datetime', ['timestamp' => $timestamp, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Determine status (IN/OUT) based on time-based rules
     * Same rules as DeviceController for consistency
     */
    private function determineStatus(Carbon $logDateTime, array $logData): string
    {
        // Check if status is already provided
        if (isset($logData['status'])) {
            $status = strtoupper($logData['status']);
            if (in_array($status, ['IN', 'OUT'])) {
                return $status;
            }
        }

        if (isset($logData['state'])) {
            return $logData['state'] == 1 ? 'IN' : 'OUT';
        }

        // Apply time-based rules
        $timeString = $logDateTime->format('H:i');

        // Morning arrival window (04:00-09:30)
        if ($timeString >= '04:00' && $timeString <= '09:30') {
            return 'IN';
        }

        // Afternoon departure window (15:00-21:00)
        if ($timeString >= '15:00' && $timeString <= '21:00') {
            return 'OUT';
        }

        // Midday window (12:00-13:00) - default to OUT
        if ($timeString >= '12:00' && $timeString <= '13:00') {
            return 'OUT';
        }

        // Default to OUT
        return 'OUT';
    }

    /**
     * Health check endpoint for devices to verify connectivity
     */
    public function healthCheck(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Push server is online',
            'timestamp' => now()->toIso8601String(),
            'your_ip' => $request->ip(),
        ]);
    }
}

