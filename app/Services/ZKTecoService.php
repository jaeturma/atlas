<?php

namespace App\Services;

use App\Models\Device;
use App\Models\AttendanceLog;
use Exception;
use Carbon\Carbon;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;

class ZKTecoService
{
    private $device;
    const TIMEOUT = 25;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Test connection to device using ZKTeco UDP protocol
     */
    public function testConnection(): array
    {
        try {
            // Initialize ZKTeco UDP client
            $zk = new ZKTeco(
                $this->device->ip_address,
                (int)$this->device->port,
                false,
                self::TIMEOUT
            );

            // Connect to device
            $connected = @$zk->connect();
            
            if ($connected) {
                // Try to get device version as additional verification
                $version = @$zk->version();
                @$zk->disconnect();
                
                return [
                    'success' => true,
                    'message' => 'Successfully connected to device via UDP',
                    'device' => [
                        'ip' => $this->device->ip_address,
                        'port' => $this->device->port,
                        'protocol' => 'ZKTeco UDP',
                        'version' => $version ?? 'Connected'
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Unable to establish connection to device',
                'device' => null
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
                'device' => null
            ];
        }
    }

    /**
     * Download attendance logs for real-time sync
     * Fetches actual logs from device via UDP protocol
     */
    public function downloadAttendanceRealtime($startDate, $endDate, $deviceId): array
    {
        try {
            // Initialize ZKTeco UDP client
            $zk = new ZKTeco(
                $this->device->ip_address,
                (int)$this->device->port,
                false,
                self::TIMEOUT
            );

            // Sync time with device
            $this->syncTime($zk);

            $deviceTz = $this->device->timezone
                ?? config('app.user_timezone', config('app.timezone', 'UTC'));

            $start = Carbon::parse($startDate, $deviceTz)->startOfDay();
            $end = Carbon::parse($endDate, $deviceTz)->endOfDay();

            // Fetch attendance logs from device (filtered in callback)
            $stats = ['total' => 0, 'earliest' => null, 'latest' => null];
            $logs = $this->getAttendanceLogs($zk, $start, $end, $stats);
            
            error_log("Device {$this->device->name}: Retrieved " . ($stats['total'] ?? 0) . " logs total; filtered " . count($logs));
            
            $logsCreated = 0;
            $logsSkipped = 0;

            foreach ($logs as $log) {
                try {
                    // Parse log time in device timezone
                    $logDateTime = Carbon::parse($log['log_datetime'] ?? $log['record_time'], $deviceTz);

                    // Insert only when this device/badge/timestamp does not yet exist (DB unique index backs this)
                    $entry = AttendanceLog::firstOrCreate(
                        [
                            'device_id' => $deviceId,
                            'badge_number' => $log['user_id'],
                            'log_datetime' => $logDateTime,
                        ],
                        [
                            'employee_id' => null,
                            'status' => $this->getStatusText($log['state']),
                            'punch_type' => $this->getPunchType($log['type']),
                        ]
                    );

                    if ($entry->wasRecentlyCreated) {
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
                'end_date' => $endDate
            ];
            
        } catch (Exception $e) {
            error_log("Download attendance realtime error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error syncing logs: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fetch attendance logs without storing them (preview only)
     */
    public function fetchAttendanceLogs($startDate, $endDate): array
    {
        try {
            $zk = new ZKTeco(
                $this->device->ip_address,
                (int)$this->device->port,
                false,
                self::TIMEOUT
            );

            // Sync time with device but do not persist anything
            $this->syncTime($zk);

            $deviceTz = $this->device->timezone
                ?? config('app.user_timezone', config('app.timezone', 'UTC'));

            $start = Carbon::parse($startDate, $deviceTz)->startOfDay();
            $end = Carbon::parse($endDate, $deviceTz)->endOfDay();

            $stats = ['total' => 0, 'earliest' => null, 'latest' => null];
            $filtered = $this->getAttendanceLogs($zk, $start, $end, $stats);

            $earliestLog = $stats['earliest'] ? $stats['earliest']->format('Y-m-d') : null;
            $latestLog = $stats['latest'] ? $stats['latest']->format('Y-m-d') : null;

            \Log::debug('ZK fetchAttendanceLogs filtered', [
                'device' => $this->device->name,
                'requested_start' => $startDate,
                'requested_end' => $endDate,
                'total_logs_from_device' => $stats['total'] ?? 0,
                'device_earliest_log' => $earliestLog,
                'device_latest_log' => $latestLog,
                'logs_after_filter' => count($filtered),
                'device_timezone' => $deviceTz,
            ]);

            return [
                'success' => true,
                'logs_total' => count($filtered),
                'logs' => $filtered,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'device_log_date_range' => [
                    'earliest' => $earliestLog,
                    'latest' => $latestLog,
                    'total_in_device' => $stats['total'] ?? 0,
                ],
            ];
        } catch (Exception $e) {
            \Log::error('Fetch attendance logs error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching logs: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get attendance logs from ZKTeco device via UDP
     */
    private function getAttendanceLogs($zk, ?Carbon $start = null, ?Carbon $end = null, ?array &$stats = null): array
    {
        try {
            $logs = [];
            $stats = $stats ?? ['total' => 0, 'earliest' => null, 'latest' => null];
            $deviceTz = $this->device->timezone
                ?? config('app.user_timezone', config('app.timezone', 'UTC'));
            
            // Connect to device first
            $connected = @$zk->connect();
            $lastError = method_exists($zk, 'getLastError') ? @$zk->getLastError() : null;
            \Log::debug("ZK connect", [
                'device' => $this->device->name,
                'connected' => $connected,
                'last_error' => $lastError,
            ]);
            
            // Use callback to process logs as they come in from device
            // Method is getAttendances() not getAttendance()
            $result = @$zk->getAttendances(function($data) use (&$logs, $start, $end, &$stats, $deviceTz) {
                if (isset($data['user_id']) && isset($data['record_time'])) {
                    $stats['total']++;
                    $recordTime = $data['record_time'];
                    try {
                        $logDateTime = is_numeric($recordTime)
                            ? Carbon::createFromTimestamp($recordTime, $deviceTz)
                            : Carbon::parse($recordTime, $deviceTz);
                    } catch (Exception $e) {
                        return $data;
                    }

                    if (!$stats['earliest'] || $logDateTime->lt($stats['earliest'])) {
                        $stats['earliest'] = $logDateTime->copy();
                    }
                    if (!$stats['latest'] || $logDateTime->gt($stats['latest'])) {
                        $stats['latest'] = $logDateTime->copy();
                    }

                    if ($start && $end && !$logDateTime->between($start, $end)) {
                        return $data;
                    }

                    $logs[] = [
                        'user_id' => (string)$data['user_id'],
                        'state' => isset($data['state']) ? $data['state'] : 1,
                        'record_time' => $data['record_time'],
                        'type' => isset($data['type']) ? $data['type'] : 0,
                        'log_datetime' => $logDateTime->toDateTimeString(),
                        'status' => isset($data['state']) ? $this->getStatusText($data['state']) : $this->getStatusText(1),
                        'punch_type' => $this->getPunchType(isset($data['type']) ? $data['type'] : 0),
                    ];
                }
                return $data;
            });
            $lastErrorAfterGet = method_exists($zk, 'getLastError') ? @$zk->getLastError() : null;
            \Log::debug("ZK getAttendances", [
                'device' => $this->device->name,
                'result' => $result,
                'logs_collected' => count($logs),
                'last_error' => $lastErrorAfterGet,
            ]);
            
            // Disconnect
            @$zk->disconnect();
            
            \Log::debug("ZK getAttendanceLogs done", [
                'device' => $this->device->name,
                'logs_collected' => count($logs),
            ]);
            return $logs;
            
        } catch (Exception $e) {
            \Log::error("Get attendance logs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Sync device time with system time
     */
    private function syncTime($zk): bool
    {
        try {
            $currentTime = date('Y-m-d H:i:s');
            @$zk->setTime($currentTime);
            error_log("Time synced with device {$this->device->name}: {$currentTime}");
            return true;
        } catch (Exception $e) {
            error_log("Time sync error for {$this->device->name}: " . $e->getMessage());
            return false;
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
     * Download attendance logs from device and store in database
     */
    public function downloadAttendance($startDate, $endDate, $deviceId): array
    {
        return $this->downloadAttendanceRealtime($startDate, $endDate, $deviceId);
    }

    /**
     * Get device information
     */
    public function getDeviceInfo(): array
    {
        try {
            $zk = new ZKTeco(
                $this->device->ip_address,
                (int)$this->device->port,
                false,
                self::TIMEOUT
            );
            
            $info = @$zk->getDeviceInfo();
            
            return [
                'success' => true,
                'message' => 'Device info retrieved',
                'info' => $info ?? 'Connected'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting device info: ' . $e->getMessage()
            ];
        }
    }
}
