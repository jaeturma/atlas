<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

$device = \App\Models\Device::first();

echo "=== Simulating DeviceController::getStatus() ===\n\n";

if ($device) {
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
                $manager = new \App\Services\DeviceProtocolManager($device);
                $connection_result = $manager->connect();
                
                echo "Manager Connection Result:\n";
                echo json_encode($connection_result, JSON_PRETTY_PRINT) . "\n\n";
                
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

    echo "Final Status Response:\n";
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    
} else {
    echo "No devices found\n";
}
