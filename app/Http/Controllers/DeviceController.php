<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\AttendanceLog;

use Illuminate\Http\Request;
use CodingLibs\ZktecoPhp\Libs\ZKTeco;
use App\Services\ZKTecoWrapper;
use App\Services\ZKTecoService;
use App\Services\ADMSProtocol;
use App\Services\DeviceProtocolManager;
use App\Services\AttendanceSyncService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = Device::paginate(15);
        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'protocol' => 'nullable|string|in:auto,adms,zkem,ngteco',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $created = Device::create($validated);

        return redirect()->route('devices.index')->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Device $device)
    {
        $recentLogs = $device->logs()->latest('log_datetime')->limit(20)->get();
        return view('devices.show', compact('device', 'recentLogs'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Device $device)
    {
        return view('devices.edit', compact('device'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'protocol' => 'nullable|string|in:auto,adms,zkem,ngteco',
            'ip_address' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $device->update($validated);

        return redirect()->route('devices.show', $device)->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device deleted successfully.');
    }

    /**
     * Update per-device live sync mode preference
     */
    public function updateSyncMode(Request $request, Device $device)
    {
        $validated = $request->validate([
            'sync_mode' => 'required|string|in:zk,auto',
        ]);

        $device->live_sync_mode = $validated['sync_mode'];
        $device->save();

        return response()->json([
            'success' => true,
            'message' => 'Sync mode updated',
            'device_id' => $device->id,
            'live_sync_mode' => $device->live_sync_mode,
        ]);
    }

    /**
     * Test connection to the device
     */
    public function testConnection(Request $request, ?Device $device = null)
    {
        // Handle JSON request for form (POST /devices/test-connection)
        if ($request->isJson()) {
            $validated = $request->validate([
                'ip_address' => 'required|ip',
                'port' => 'required|integer|min:1|max:65535',
            ]);

            try {
                $tempDevice = new Device([
                    'ip_address' => $validated['ip_address'],
                    'port' => $validated['port'],
                ]);

                // Use the same logic as getStatus() for consistency
                $status = [
                    'success' => false,
                    'message' => 'Device unreachable',
                    'connection' => [
                        'status' => 'offline',
                        'ping' => false,
                        'socket' => false,
                    ],
                ];

                // Check ping
                $ping_cmd = 'ping -n 1 -w 1000 ' . escapeshellarg($tempDevice->ip_address);
                exec($ping_cmd, $output, $result_code);
                $status['connection']['ping'] = ($result_code === 0);

                // Check socket
                $socket = @fsockopen($tempDevice->ip_address, $tempDevice->port, $errno, $errstr, 2);
                $status['connection']['socket'] = ($socket !== false);
                if ($socket) {
                    fclose($socket);
                }

                // Determine status
                if ($status['connection']['ping'] && $status['connection']['socket']) {
                    $status['success'] = true;
                    $status['connection']['status'] = 'online';
                    $status['message'] = 'Device is reachable and port is open';
                } elseif ($status['connection']['ping']) {
                    $status['connection']['status'] = 'reachable_port_closed';
                    $status['message'] = 'Device is reachable but port is closed';
                } else {
                    $status['connection']['status'] = 'offline';
                    $status['message'] = 'Device is not reachable';
                }
                
                return response()->json($status);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
        }

        // Handle existing device (POST /devices/{device}/test-connection)
        if ($device) {
            try {
                // Use the same logic as getStatus() for consistency
                $status = [
                    'success' => false,
                    'message' => 'Device unreachable',
                    'connection' => [
                        'status' => 'offline',
                        'ping' => false,
                        'socket' => false,
                    ],
                ];

                // Check ping
                $ping_cmd = 'ping -n 1 -w 1000 ' . escapeshellarg($device->ip_address);
                exec($ping_cmd, $output, $result_code);
                $status['connection']['ping'] = ($result_code === 0);

                // Check socket
                $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 2);
                $status['connection']['socket'] = ($socket !== false);
                if ($socket) {
                    fclose($socket);
                }

                // Determine status
                if ($status['connection']['ping'] && $status['connection']['socket']) {
                    $status['success'] = true;
                    $status['connection']['status'] = 'online';
                    $status['message'] = 'Device is reachable and port is open';
                } elseif ($status['connection']['ping']) {
                    $status['connection']['status'] = 'reachable_port_closed';
                    $status['message'] = 'Device is reachable but port is closed';
                } else {
                    $status['connection']['status'] = 'offline';
                    $status['message'] = 'Device is not reachable';
                }
                
                return response()->json($status);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'No device specified'
        ], 400);
    }

    /**
     * Download and store attendance logs from device
     * Supports preview-first flow (confirm=false) before persisting
     */
    public function downloadLogs(Request $request, Device $device)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'confirm' => 'sometimes|boolean',
            'preview_limit' => 'sometimes|integer|min:1|max:50000',
            'logs' => 'sometimes|array',
            'logs.*.user_id' => 'required',
            'logs.*.record_time' => 'required',
            'logs.*.state' => 'nullable',
            'logs.*.type' => 'nullable',
        ]);

        $confirm = (bool)($validated['confirm'] ?? false);
        $previewLimit = isset($validated['preview_limit']) ? (int)$validated['preview_limit'] : 0;
        if ($previewLimit > 0) {
            $previewLimit = max(1, min(50000, $previewLimit));
        }

        try {
            $service = new ZKTecoService($device);

            // Preview mode: fetch logs only, do not store
            if (!$confirm) {
                \Log::info("Previewing logs from device {$device->name}");

                $preview = $service->fetchAttendanceLogs($validated['start_date'], $validated['end_date']);

                if (!($preview['success'] ?? false)) {
                    return response()->json([
                        'success' => false,
                        'message' => $preview['message'] ?? 'Unable to fetch logs',
                    ], 500);
                }

                $logs = $preview['logs'] ?? [];
                $total = $preview['logs_total'] ?? count($logs);
                $previewSlice = $previewLimit > 0 ? array_slice($logs, 0, $previewLimit) : $logs;
                
                // Get device date range info
                $deviceDateRange = $preview['device_log_date_range'] ?? null;
                $message = $previewLimit > 0
                    ? "Preview only. Showing first {$previewLimit} of {$total} log(s). Call with confirm=true to store."
                    : "Logs extracted. Review and select logs to store.";
                
                // Add helpful message if no logs found in requested range
                if ($total === 0 && $deviceDateRange && $deviceDateRange['total_in_device'] > 0) {
                    $message = "No logs found in date range {$validated['start_date']} to {$validated['end_date']}. " .
                               "Device has {$deviceDateRange['total_in_device']} log(s) from {$deviceDateRange['earliest']} to {$deviceDateRange['latest']}. " .
                               "Adjust your date range to match device data.";
                } elseif ($total === 0 && $deviceDateRange && $deviceDateRange['total_in_device'] === 0) {
                    $message = "Device has no undownloaded logs. The device buffer only contains NEW attendance records that haven't been downloaded yet. " .
                               "If you're looking for historical data, it may have been downloaded already or cleared from the device.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'logs' => $previewSlice,
                    'logs_preview' => $previewSlice,
                    'logs_total' => $total,
                    'preview_limit' => $previewLimit > 0 ? $previewLimit : $total,
                    'confirm_required' => true,
                    'start_date' => $validated['start_date'],
                    'end_date' => $validated['end_date'],
                    'device_log_date_range' => $deviceDateRange,
                ]);
            }

            // Confirmed: proceed to store selected logs
            if (isset($validated['logs']) && is_array($validated['logs'])) {
                \Log::info("Storing selected logs for device {$device->name}", ['count' => count($validated['logs'])]);
                
                $created = 0;
                $skipped = 0;
                $rows = [];
                $now = now();
                
                // Sort logs chronologically to correctly apply midday IN/OUT sequencing per employee
                $logsToStore = $validated['logs'];
                usort($logsToStore, fn($a, $b) => Carbon::parse($a['record_time'])->timestamp <=> Carbon::parse($b['record_time'])->timestamp);

                // Track midday OUT/IN transitions per employee per date
                $middayTracker = [];
                
                foreach ($logsToStore as $logData) {
                    // Remove internal _id field if present
                    unset($logData['_id']);
                    
                    // Parse the record_time
                    $logDateTime = Carbon::parse($logData['record_time']);
                    
                    // Determine status
                    $status = 'OUT';
                    if (isset($logData['state'])) {
                        $status = $logData['state'] == 1 ? 'IN' : 'OUT';
                    } elseif (isset($logData['status'])) {
                        $status = strtoupper($logData['status']);
                    }

                    // Apply time-based status rules (per employee/day)
                    $trackerKey = $logData['user_id'] . '|' . $logDateTime->toDateString();
                    if (!isset($middayTracker[$trackerKey])) {
                        $middayTracker[$trackerKey] = [
                            'seen_midday_out' => false,
                            'midday_return_logged' => false,
                        ];
                    }

                    $timeString = $logDateTime->format('H:i');

                    // Morning arrival window
                    if ($timeString >= '04:00' && $timeString <= '09:30') {
                        $status = 'IN';
                    }
                    // Afternoon departure window
                    elseif ($timeString >= '15:00' && $timeString <= '21:00') {
                        $status = 'OUT';
                    }
                    // Midday logic: first log OUT (12:00-12:59), second log IN (12:10-13:00)
                    elseif ($timeString >= '12:00' && $timeString <= '12:59') {
                        if ($middayTracker[$trackerKey]['seen_midday_out'] && !$middayTracker[$trackerKey]['midday_return_logged'] && $timeString >= '12:10') {
                            $status = 'IN';
                            $middayTracker[$trackerKey]['midday_return_logged'] = true;
                        } elseif (!$middayTracker[$trackerKey]['seen_midday_out']) {
                            $status = 'OUT';
                            $middayTracker[$trackerKey]['seen_midday_out'] = true;
                        }
                        // If a return has already been logged, keep the previously determined status
                    } elseif (
                        $timeString >= '12:10' &&
                        $timeString <= '13:00' &&
                        $middayTracker[$trackerKey]['seen_midday_out'] &&
                        !$middayTracker[$trackerKey]['midday_return_logged']
                    ) {
                        $status = 'IN';
                        $middayTracker[$trackerKey]['midday_return_logged'] = true;
                    }
                    
                    $rows[] = [
                        'device_id' => $device->id,
                        'badge_number' => $logData['user_id'],
                        'log_datetime' => $logDateTime,
                        'status' => $status,
                        'punch_type' => $logData['type'] ?? 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                $totalToInsert = count($rows);
                if ($totalToInsert > 0) {
                    foreach (array_chunk($rows, 1000) as $chunk) {
                        $created += DB::table('attendance_logs')->insertOrIgnore($chunk);
                    }
                    $skipped = $totalToInsert - $created;
                }
                
                $message = "Successfully stored {$created} log(s).";
                if ($skipped > 0) {
                    $message .= " Skipped {$skipped} duplicate(s).";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'logs_count' => $created,
                    'skipped_count' => $skipped,
                ]);
            }

            // Fallback: fetch and store all logs in date range
            \Log::info("Attempting to download logs from device {$device->name}");
            $result = $service->downloadAttendanceRealtime($validated['start_date'], $validated['end_date'], $device->id);
            \Log::info("Download logs result: " . json_encode($result));

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error("Download logs error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error downloading logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device status (JSON API)
     * Uses multi-protocol support for different ZKTeco device types
     */
    public function getStatus(Device $device)
    {
        $status = [
            'id' => $device->id,
            'name' => $device->name,
            'model' => $device->model,
            'ip_address' => $device->ip_address,
            'port' => $device->port,
            'protocol' => $device->protocol ?? 'auto',
            'is_active' => $device->is_active,
            'connection' => [
                'status' => 'unknown',
                'ping' => false,
                'socket' => false,
                'protocol' => false,
                'protocol_type' => null,
                'last_checked' => null,
            ],
            'device_info' => null,
        ];

        try {
            // Check ping
            $ping_cmd = 'ping -n 1 -w 1000 ' . escapeshellarg($device->ip_address);
            exec($ping_cmd, $output, $result_code);
            $status['connection']['ping'] = ($result_code === 0);

            // Check socket
            $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 2);
            $status['connection']['socket'] = ($socket !== false);
            if ($socket) {
                fclose($socket);
            }

            // Try to connect using multi-protocol manager
            if ($status['connection']['socket']) {
                try {
                    $manager = new DeviceProtocolManager($device);
                    $connection_result = $manager->connect();
                    
                    if ($connection_result['success']) {
                        $status['connection']['protocol'] = true;
                        $status['connection']['protocol_type'] = $connection_result['protocol'];
                        
                        // Try to get device info (works better with ZKEM)
                        $handler = $manager->getHandler();
                        if (method_exists($handler, 'vendorName')) {
                            $status['device_info'] = [
                                'vendor' => @$handler->vendorName() ?: 'Unknown',
                                'model' => @$handler->deviceName() ?: 'Unknown',
                                'version' => @$handler->version() ?: 'Unknown',
                                'serial' => @$handler->serialNumber() ?: 'Unknown',
                                'platform' => @$handler->platform() ?: 'Unknown',
                            ];
                        }
                        
                        $manager->disconnect();
                    } else {
                        // Protocol failed, but socket is working
                        // Log for debugging but don't fail - device is still online
                        \Log::debug('Protocol connection failed for device ' . $device->id, [
                            'ip' => $device->ip_address,
                            'error' => $connection_result['error'] ?? 'Unknown',
                        ]);
                    }
                } catch (\Exception $e) {
                    // Protocol error, but socket is working
                    \Log::debug('Protocol exception for device ' . $device->id . ': ' . $e->getMessage());
                }
            }

            // Determine overall status
            if ($status['connection']['protocol']) {
                $status['connection']['status'] = 'online_protocol_ok';
            } elseif ($status['connection']['socket'] && $status['connection']['ping']) {
                $status['connection']['status'] = 'online_no_protocol';
            } elseif ($status['connection']['ping']) {
                $status['connection']['status'] = 'reachable_port_closed';
            } else {
                $status['connection']['status'] = 'offline';
            }

            $status['connection']['last_checked'] = now();
        } catch (\Exception $e) {
            $status['connection']['status'] = 'error';
            $status['connection']['error'] = $e->getMessage();
        }

        return response()->json($status);
    }

    /**
     * Sync time from server to device
     * Uses multi-protocol support for ADMS and ZKEM devices
     */
    public function syncTime(Device $device)
    {
        try {
            // First, verify the device is reachable
            $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 2);
            $device_reachable = ($socket !== false);
            if ($socket) {
                fclose($socket);
            }

            if (!$device_reachable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Device is offline or unreachable.',
                    'device_ip' => $device->ip_address,
                    'device_port' => $device->port,
                ], 400);
            }

            // Use multi-protocol manager
            $manager = new DeviceProtocolManager($device);
            $connection = $manager->connect();
            
            if (!$connection['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to establish protocol connection: ' . ($connection['error'] ?? 'Unknown error'),
                    'device_ip' => $device->ip_address,
                    'protocol_attempted' => $connection['protocol'] ?? null,
                ], 400);
            }

            // Set device time using user timezone
            $userTz = config('app.user_timezone', 'UTC');
            $deviceTime = now($userTz);
            
            $result = $manager->setTime($deviceTime->timestamp);
            $manager->disconnect();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => "Device time synchronized to {$deviceTime->format('Y-m-d H:i:s')} ({$userTz}) using {$connection['protocol']} protocol",
                    'device_time' => $deviceTime->format('Y-m-d H:i:s'),
                    'timestamp' => $deviceTime->timestamp,
                    'timezone' => $userTz,
                    'protocol' => $connection['protocol'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to set device time. Device may not support time synchronization.',
                    'device_ip' => $device->ip_address,
                    'protocol' => $connection['protocol'],
                    'error' => $manager->getLastError(),
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get device time
     * Uses multi-protocol support (ADMS/PUSH for WL10, ZKEM for legacy devices)
     */
    public function getDeviceTime(Device $device)
    {
        try {
            // First, verify the device is reachable
            $socket = @fsockopen($device->ip_address, $device->port, $errno, $errstr, 2);
            $device_reachable = ($socket !== false);
            if ($socket) {
                fclose($socket);
            }

            if (!$device_reachable) {
                // Device is not reachable - don't try protocol
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device - device may be offline or unreachable',
                    'device_ip' => $device->ip_address,
                    'device_port' => $device->port,
                    'troubleshooting' => [
                        'Check that device IP address is correct: ' . $device->ip_address,
                        'Check that device port is correct: ' . $device->port,
                        'Verify device is powered on and connected to network',
                        'Ensure firewall allows communication on port ' . $device->port,
                        'Try using Test Connection button to verify connectivity',
                    ]
                ], 400);
            }

            // Device is reachable, try to get device time using multi-protocol manager
            try {
                $manager = new DeviceProtocolManager($device);
                $connection = $manager->connect();
                
                if ($connection['success']) {
                    $device_time_raw = $manager->getTime();

                    if ($device_time_raw) {
                        // Parse device time and convert to proper timezone
                        $device_time_obj = null;
                        
                        // Try to parse the device time
                        try {
                            // Handle timestamp (seconds since epoch)
                            if (is_numeric($device_time_raw) && (int)$device_time_raw > 0) {
                                $device_time_obj = \Carbon\Carbon::createFromTimestamp((int)$device_time_raw);
                            } else {
                                // Try Y-m-d H:i:s format first
                                $device_time_obj = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $device_time_raw);
                            }
                        } catch (\Exception $e) {
                            // Try other common formats
                            try {
                                $device_time_obj = \Carbon\Carbon::parse($device_time_raw);
                            } catch (\Exception $e2) {
                                // If parsing fails, log it but still try to return the raw value
                                \Log::warning('Could not parse device time: ' . $device_time_raw, [
                                    'device_ip' => $device->ip_address,
                                    'protocol' => $connection['protocol'],
                                ]);
                                $device_time_obj = null;
                            }
                        }

                        // Get server time in user timezone
                        $user_tz = config('app.user_timezone', 'UTC');
                        $server_time_obj = now($user_tz);
                        
                        // Device returns UTC timestamp
                        // Convert it to the device's configured timezone for display
                        $device_timezone = $device->timezone ?? config('app.user_timezone', 'UTC');
                        
                        if ($device_time_obj) {
                            // The timestamp from the device is UTC-based
                            // Convert it to device timezone for display
                            $device_time_obj->setTimezone($device_timezone);
                        }
                        
                        $manager->disconnect();
                        
                        $time_diff = $device_time_obj ? ($device_time_obj->timestamp - $server_time_obj->timestamp) : null;
                        
                        return response()->json([
                            'success' => true,
                            'device_time' => $device_time_obj ? $device_time_obj->format('Y-m-d H:i:s') : $device_time_raw,
                            'device_time_raw' => $device_time_raw,
                            'device_timestamp' => $device_time_obj ? $device_time_obj->timestamp : null,
                            'server_time' => $server_time_obj->format('Y-m-d H:i:s'),
                            'server_timestamp' => $server_time_obj->timestamp,
                            'timestamp' => $server_time_obj->timestamp,
                            'device_timezone' => $device_timezone,
                            'server_timezone' => $user_tz,
                            'time_difference_seconds' => $time_diff,
                            'time_difference_hours' => $time_diff ? round($time_diff / 3600, 2) : null,
                            'is_synced' => $time_diff ? abs($time_diff) < 60 : null,
                            'protocol' => $connection['protocol'],
                        ]);
                    } else {
                        // Connected but couldn't get time - try fallback
                        \Log::debug('Connected to device but could not retrieve time', [
                            'device_ip' => $device->ip_address,
                            'protocol' => $connection['protocol'],
                            'error' => $manager->getLastError(),
                        ]);
                    }
                } else {
                    // Protocol connection failed - log and try fallback
                    $connection_error = $connection['error'] ?? 'Connection failed (unknown reason)';
                    \Log::debug('Device protocol connection failed: ' . $connection_error, [
                        'device_ip' => $device->ip_address,
                        'device_port' => $device->port,
                        'protocol' => $connection['protocol'] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // Protocol error - log and try fallback
                \Log::debug('Device protocol error: ' . $e->getMessage(), [
                    'device_ip' => $device->ip_address,
                ]);
            }

            // Fallback: Since device is reachable but protocol failed,
            // return server time as a fallback with a note about protocol limitations
            return response()->json([
                'success' => true,
                'device_time' => now()->format('Y-m-d H:i:s'),
                'server_time' => now()->format('Y-m-d H:i:s'),
                'timestamp' => now(),
                'note' => 'Device is online but protocol did not respond. Using server time as fallback.',
                'device_ip' => $device->ip_address,
                'device_port' => $device->port,
                'device_timezone' => $device->timezone ?? 'UTC',
                'server_timezone' => config('app.timezone'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download users from device
     */
    public function downloadUsers(Device $device)
    {
        try {
            $zk = new ZKTecoWrapper($device->ip_address, (int)$device->port, false, 10);
            
            if (!$zk->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device',
                ], 400);
            }

            $users = @$zk->getUsers();

            if ($users && is_array($users) && count($users) > 0) {
                return response()->json([
                    'success' => true,
                    'users_count' => count($users),
                    'users' => $users,
                    'message' => 'Downloaded ' . count($users) . ' users from device',
                    'timestamp' => now(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found on device',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download attendance logs from device
     */
    public function downloadDeviceLogs(Request $request, Device $device)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        try {
            // Use multi-protocol manager to handle both ZKEM and ADMS devices
            $manager = new DeviceProtocolManager($device);
            $connection = $manager->connect();

            if (!$connection['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device: ' . ($connection['error'] ?? 'Unknown error'),
                ], 400);
            }

            $filteredLogs = [];
            $totalLogsFromDevice = 0;
            $sampleDeviceLogTimes = [];
            $sampleLimit = 5;
            $deviceTz = $device->timezone
                ?? config('app.user_timezone', config('app.timezone', 'UTC'));

            $start = Carbon::parse($validated['start_date'], $deviceTz)->startOfDay();
            $end = Carbon::parse($validated['end_date'], $deviceTz)->endOfDay();

            $now = now();

            $result = $manager->getAttendances(function($data) use (
                &$filteredLogs,
                $device,
                $start,
                $end,
                &$totalLogsFromDevice,
                &$sampleDeviceLogTimes,
                $sampleLimit,
                $now
            ) {
                $totalLogsFromDevice++;

                $log_datetime = is_numeric($data['record_time']) ?
                    Carbon::createFromTimestamp($data['record_time'], $deviceTz) :
                    Carbon::parse($data['record_time'], $deviceTz);

                if (count($sampleDeviceLogTimes) < $sampleLimit) {
                    $sampleDeviceLogTimes[] = $log_datetime->toDateTimeString();
                }

                if ($log_datetime->between($start, $end)) {
                    $filteredLogs[] = [
                        'device_id' => $device->id,
                        'badge_number' => $data['user_id'] ?? $data['uid'] ?? null,
                        'log_datetime' => $log_datetime,
                        'status' => ($data['state'] ?? 0) == 1 ? 'IN' : 'OUT',
                        'punch_type' => $data['type'] ?? 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                return true;
            });

            $manager->disconnect();

            // Filter logs by requested date range
            // Debug logging
            \Log::debug('downloadDeviceLogs filtering', [
                'requested_start' => $start->toDateTimeString(),
                'requested_end' => $end->toDateTimeString(),
                'total_logs_from_device' => $totalLogsFromDevice,
                'filtered_logs' => count($filteredLogs),
                'first_log_datetime' => !empty($sampleDeviceLogTimes) ? $sampleDeviceLogTimes[0] : null,
                'first_log_type' => !empty($sampleDeviceLogTimes) ? gettype($sampleDeviceLogTimes[0]) : null,
                'device_timezone' => $deviceTz,
            ]);

            if ($result !== false && count($filteredLogs) > 0) {
                $created = 0;
                $totalToInsert = count($filteredLogs);

                if ($totalToInsert > 0) {
                    foreach (array_chunk($filteredLogs, 1000) as $chunk) {
                        $created += DB::table('attendance_logs')->insertOrIgnore($chunk);
                    }
                }

                $skipped = $totalToInsert - $created;

                return response()->json([
                    'success' => true,
                    'logs_count' => $created,
                    'logs_skipped' => $skipped,
                    'message' => 'Downloaded and saved ' . $created . ' new attendance log(s)' . ($skipped ? " ($skipped skipped)" : ''),
                    'timestamp' => now(),
                    'device_logs_total' => $totalLogsFromDevice,
                    'date_range' => [
                        'start' => $validated['start_date'],
                        'end' => $validated['end_date'],
                    ],
                ]);
            }

            // Debug info when no logs found
            return response()->json([
                'success' => false,
                'message' => 'No logs found in selected date range. Device has ' . $totalLogsFromDevice . ' log(s) total.',
                'device_logs_total' => $totalLogsFromDevice,
                'filtered_logs_count' => count($filteredLogs),
                'date_range' => [
                    'start' => $validated['start_date'],
                    'end' => $validated['end_date'],
                ],
                'sample_device_log_times' => $sampleDeviceLogTimes,
                'filtering_range' => [
                    'start' => $start->toDateTimeString(),
                    'end' => $end->toDateTimeString(),
                ],
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear device attendance logs
     */
    public function clearLogs(Device $device)
    {
        try {
            $zk = new ZKTecoWrapper($device->ip_address, (int)$device->port, false, 10);
            
            if (!$zk->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device',
                ], 400);
            }

            $result = @$zk->clearAttendance();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Device attendance logs cleared successfully',
                    'timestamp' => now(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear device logs',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restart device
     */
    public function restart(Device $device)
    {
        try {
            $zk = new ZKTecoWrapper($device->ip_address, (int)$device->port, false, 10);
            
            if (!$zk->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device',
                ], 400);
            }

            $result = @$zk->restart();

            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Device restart command sent',
                    'timestamp' => now(),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to restart device',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
