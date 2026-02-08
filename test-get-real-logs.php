<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device = \App\Models\Device::first();

echo "Testing getAttendances with ZKTeco\n";
echo "Device: {$device->name} ({$device->ip_address}:{$device->port})\n\n";

try {
    echo "1. Creating ZKTeco instance...\n";
    $zk = new ZKTeco($device->ip_address, (int)$device->port, false, 25);
    echo "   OK\n";
    
    echo "2. Attempting getAttendances...\n";
    $logs = [];
    $count = 0;
    
    $result = $zk->getAttendances(function($data) use (&$logs, &$count) {
        $count++;
        if ($count <= 5) {
            echo "   Log #$count: User " . $data['user_id'] . " at " . date('Y-m-d H:i:s', $data['record_time']) . " - State: " . $data['state'] . "\n";
        }
        $logs[] = [
            'user_id' => $data['user_id'],
            'record_time' => $data['record_time'],
            'state' => $data['state'],
            'type' => $data['type'] ?? 0,
        ];
        return true;
    });
    
    echo "   getAttendances returned: " . ($result ? 'True' : 'False') . "\n";
    echo "   Total logs retrieved: " . count($logs) . "\n";
    
    if (count($logs) > 5) {
        echo "   ... and " . (count($logs) - 5) . " more logs\n";
    }
    
    if (count($logs) > 0) {
        echo "\n✓ SUCCESS - Retrieved " . count($logs) . " real attendance logs from device!\n";
        
        echo "\nSample logs:\n";
        foreach (array_slice($logs, 0, 3) as $log) {
            $dt = date('Y-m-d H:i:s', $log['record_time']);
            $status = $log['state'] == 1 ? 'IN' : 'OUT';
            echo "  Badge: {$log['user_id']}, Time: {$dt}, Status: {$status}\n";
        }
    } else {
        echo "\nNo attendance logs found on device\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Class: " . get_class($e) . "\n";
}
