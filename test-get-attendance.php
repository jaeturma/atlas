<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device = \App\Models\Device::first();

echo "Trying getAttendances directly without explicit connect...\n\n";

$zk = new ZKTeco($device->ip_address, $device->port, false, 25);

$logs = [];
try {
    echo "Calling getAttendances...\n";
    $result = @$zk->getAttendances(function($data) use (&$logs) {
        $logs[] = $data;
        echo "Log received: User {$data['user_id']} at " . date('Y-m-d H:i:s', $data['record_time']) . " - State: {$data['state']}\n";
        return true;
    });
    
    echo "\ngetAttendances result: " . ($result ? 'Success' : 'Failed') . "\n";
    echo "Total logs retrieved: " . count($logs) . "\n";
    
    if (count($logs) > 0) {
        echo "\nFirst 3 logs:\n";
        foreach (array_slice($logs, 0, 3) as $log) {
            echo "  - User: {$log['user_id']}, Time: " . date('Y-m-d H:i:s', $log['record_time']) . ", State: {$log['state']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString();
}
