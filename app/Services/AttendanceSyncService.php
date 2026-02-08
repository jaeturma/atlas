<?php

namespace App\Services;

use App\Models\Device;
use App\Models\AttendanceLog;
use Exception;
use Carbon\Carbon;

/**
 * Attendance Sync Service
 * 
 * Handles real-time attendance log synchronization using multi-protocol support.
 * Supports both modern (ADMS) and legacy (ZKEM) ZKTeco devices.
 * 
 * Uses DeviceProtocolManager for intelligent protocol selection and fallback.
 */
class AttendanceSyncService
{
    private $device;
    private $protocolManager;
    private $lastError = null;
    private $protocol_used = null;

    public function __construct(Device $device)
    {
        $this->device = $device;
        $this->protocolManager = new DeviceProtocolManager($device);
    }

    /**
     * Get the protocol used in the last operation
     */
    public function getProtocolUsed(): ?string
    {
        return $this->protocol_used;
    }

    /**
     * Download and sync attendance logs in real-time
     * Uses multi-protocol manager to support both ADMS and ZKEM devices
     */
    public function downloadAttendanceRealtime($startDate, $endDate): array
    {
        try {
            // Use protocol manager to connect to device
            $result = $this->protocolManager->connect();
            
            if (!$result['success']) {
                $this->lastError = $result['message'] ?? 'Failed to connect';
                return [
                    'success' => false,
                    'message' => 'Error syncing logs: ' . $this->lastError,
                    'protocol' => null,
                ];
            }

            $this->protocol_used = $result['protocol'] ?? 'unknown';
            $handler = $this->protocolManager->getHandler();

            error_log("Device {$this->device->name}: Connected via {$this->protocol_used} protocol");

            // Fetch attendance logs from device
            $logs = $this->getAttendanceLogs($handler);
            
            error_log("Device {$this->device->name}: Retrieved " . count($logs) . " logs total from {$this->protocol_used}");
            
            $logsCreated = 0;
            $logsSkipped = 0;

            foreach ($logs as $log) {
                try {
                    // Create Carbon instance from timestamp
                    $logDateTime = Carbon::createFromTimestamp($log['record_time']);
                    
                    // Filter by date range
                    if (!$logDateTime->between(
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    )) {
                        continue;
                    }

                    // Check if log already exists
                    $exists = AttendanceLog::where('device_id', $this->device->id)
                        ->where('badge_number', $log['user_id'])
                        ->where('log_datetime', $logDateTime)
                        ->exists();

                    if (!$exists) {
                        AttendanceLog::create([
                            'device_id' => $this->device->id,
                            'badge_number' => $log['user_id'],
                            'employee_id' => null,
                            'log_datetime' => $logDateTime,
                            'status' => $this->getStatusText($log['state']),
                            'punch_type' => $this->getPunchType($log['type']),
                        ]);
                        $logsCreated++;
                    } else {
                        $logsSkipped++;
                    }
                } catch (Exception $e) {
                    error_log("Error processing log: " . $e->getMessage());
                    continue;
                }
            }

            return [
                'success' => true,
                'message' => "Synced $logsCreated new attendance log(s)" . ($logsSkipped > 0 ? " ($logsSkipped skipped)" : ""),
                'logs_count' => $logsCreated,
                'logs_skipped' => $logsSkipped,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'protocol' => $this->protocol_used,
            ];
            
        } catch (Exception $e) {
            error_log("Download attendance realtime error: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return [
                'success' => false,
                'message' => 'Error syncing logs: ' . $e->getMessage(),
                'protocol' => null,
            ];
        }
    }

    /**
     * Get attendance logs from device using protocol handler
     * Supports both ADMS and ZKEM protocols
     */
    private function getAttendanceLogs($handler): array
    {
        try {
            $logs = [];

            // Get protocol type from handler class name
            $handlerClass = class_basename(get_class($handler));

            if ($handlerClass === 'ADMSProtocol') {
                // ADMS protocol - use getStatus which includes attendance info
                $logs = $this->getLogsFromADMS($handler);
            } else {
                // ZKEM protocol or fallback - use legacy method
                $logs = $this->getLogsFromZKEM($handler);
            }

            error_log("Retrieved " . count($logs) . " logs from device {$this->device->name} via {$this->protocol_used}");
            return $logs;
            
        } catch (Exception $e) {
            error_log("Get attendance logs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get logs from ADMS protocol device
     */
    private function getLogsFromADMS($handler): array
    {
        $logs = [];
        
        try {
            // ADMS protocol is primarily for real-time push notifications
            // For log retrieval, we need to use ZKEM or direct ZKTeco protocol
            // Fall back to using ZKTecoWrapper directly for log fetching
            
            error_log("ADMS device detected. Using ZKEM fallback for attendance log retrieval");
            
            $zktecoHandler = new ZKTecoWrapper(
                $this->device->ip_address,
                $this->device->port ?? 4370,
                false,
                25,
                0
            );
            
            // Try to connect and fetch logs using ZKEM
            $connected = @$zktecoHandler->connect();
            if (!$connected) {
                error_log("Failed to connect to ADMS device using ZKTeco wrapper. Error: " . ($zktecoHandler->getLastError() ?? 'Unknown'));
                return [];
            }
            
            error_log("Connected to device via ZKTeco wrapper for ADMS log retrieval");
            
            // Try to get attendances
            $result = @$zktecoHandler->getAttendances(function($data) use (&$logs) {
                error_log("Attendance record received: " . json_encode($data));
                $logs[] = [
                    'record_time' => $data['record_time'] ?? time(),
                    'user_id' => $data['user_id'] ?? $data['uid'] ?? null,
                    'state' => ($data['state'] ?? 0) == 1 ? 1 : 0, // 1 for IN, 0 for OUT
                    'type' => $data['type'] ?? 0,
                ];
                return true;
            });

            error_log("getAttendances result: " . ($result === false ? 'FALSE' : 'TRUE') . ", Logs collected: " . count($logs));
            
            if ($result === false) {
                $error = $zktecoHandler->getLastError();
                error_log("ZKTecoWrapper getAttendances failed for ADMS device. Error: " . ($error ?? 'Unknown error'));
            } else {
                error_log("Successfully fetched " . count($logs) . " logs from ADMS device using ZKEM");
            }
            
            return $logs;
        } catch (Exception $e) {
            error_log("Error getting ADMS logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get logs from ZKEM protocol device (legacy)
     */
    private function getLogsFromZKEM($handler): array
    {
        $logs = [];
        
        try {
            // For ZKEM devices, use the ZKTeco library getAttendances method
            // The handler is a ZKTecoWrapper that delegates to the underlying ZKTeco instance
            $handlerClass = class_basename(get_class($handler));
            $lastErrorBefore = method_exists($handler, 'getLastError') ? @$handler->getLastError() : null;
            $connected = null;
            if (method_exists($handler, 'connect')) {
                $connected = @$handler->connect();
            }
            \Log::debug('[ZKEM] connect', [
                'device' => $this->device->name,
                'handler' => $handlerClass,
                'connected' => $connected,
                'last_error_before' => $lastErrorBefore,
            ]);
            
            $result = @$handler->getAttendances(function($data) use (&$logs) {
                $logs[] = [
                    'record_time' => $data['record_time'] ?? time(),
                    'user_id' => $data['user_id'] ?? $data['uid'] ?? null,
                    'state' => ($data['state'] ?? 0) == 1 ? 1 : 0, // 1 for IN, 0 for OUT
                    'type' => $data['type'] ?? 0,
                ];
                return true;
            });

            if ($result === false) {
                $error = $handler->getLastError();
                \Log::warning("ZKTecoWrapper getAttendances returned false", [
                    'device' => $this->device->name,
                    'error' => $error,
                ]);
                return [];
            }
            $lastErrorAfter = method_exists($handler, 'getLastError') ? @$handler->getLastError() : null;
            \Log::debug('[ZKEM] getAttendances', [
                'device' => $this->device->name,
                'handler' => $handlerClass,
                'result' => $result,
                'logs_collected' => count($logs),
                'last_error_after' => $lastErrorAfter,
            ]);

            \Log::info("Successfully fetched " . count($logs) . " logs from ZKEM device", [
                'device' => $this->device->name,
            ]);
            return $logs;
        } catch (Exception $e) {
            \Log::error("Error getting ZKEM logs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Convert attendance state to text
     * 1 = Check In (In)
     * 2 = Check Out (Out)
     * Other = In (default)
     */
    private function getStatusText($state): string
    {
        return $state == 2 ? 'Out' : 'In';
    }

    /**
     * Get punch/verification type description
     */
    private function getPunchType($type): string
    {
        $types = [
            0 => 'Fingerprint',
            1 => 'Card',
            2 => 'Password',
            3 => 'Face',
            4 => 'Palmprint',
        ];

        return $types[$type] ?? 'Unknown';
    }

    /**
     * Download attendance logs for a date range
     * Wrapper that calls downloadAttendanceRealtime
     */
    public function downloadAttendance($startDate, $endDate, $deviceId): array
    {
        return $this->downloadAttendanceRealtime($startDate, $endDate);
    }

    /**
     * Get last error message
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Test connection using protocol manager
     */
    public function testConnection(): array
    {
        try {
            $result = $this->protocolManager->connect();
            
            if ($result['success']) {
                $this->protocol_used = $result['protocol'] ?? 'unknown';
                return [
                    'success' => true,
                    'message' => "Connected to device via {$this->protocol_used} protocol",
                    'protocol' => $this->protocol_used,
                    'device' => [
                        'ip' => $this->device->ip_address,
                        'port' => $this->device->port,
                        'protocol' => $this->protocol_used,
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to connect',
                'protocol' => null,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'protocol' => null,
            ];
        }
    }
}
