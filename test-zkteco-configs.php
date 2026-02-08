<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$device = \App\Models\Device::first();

echo "Testing ZKTeco connection with different parameters\n\n";

// Try different configurations
$configs = [
    ['shouldPing' => false, 'password' => 0, 'name' => 'No ping, no password'],
    ['shouldPing' => true, 'password' => 0, 'name' => 'With ping, no password'],
    ['shouldPing' => false, 'password' => 0, 'name' => 'No ping, password 0'],
    ['shouldPing' => true, 'password' => '', 'name' => 'With ping, empty password'],
];

foreach ($configs as $config) {
    echo "\nTrying: {$config['name']}\n";
    echo "---\n";
    
    try {
        $zk = new ZKTeco(
            $device->ip_address,
            (int)$device->port,
            $config['shouldPing'],
            25,
            $config['password']
        );
        
        echo "1. Checking socket creation...\n";
        if (!$zk->_zkclient || !is_resource($zk->_zkclient)) {
            echo "   Socket creation failed\n";
            continue;
        }
        echo "   Socket OK\n";
        
        echo "2. Testing ping...\n";
        $ping = @$zk->ping(false);
        echo "   Ping result: " . ($ping ? 'Success' : 'Failed') . "\n";
        
        echo "3. Attempting connect...\n";
        $connect = @$zk->connect();
        echo "   Connect result: " . ($connect ? 'Success' : 'Failed') . "\n";
        
        if ($connect) {
            echo "4. Getting version...\n";
            $version = @$zk->version();
            echo "   Version: " . ($version ? $version : 'N/A') . "\n";
            
            echo "5. Trying getAttendances...\n";
            $logs = [];
            $result = @$zk->getAttendances(function($data) use (&$logs) {
                $logs[] = $data;
                return true;
            });
            echo "   Result: " . ($result ? 'Success' : 'Failed') . "\n";
            echo "   Logs received: " . count($logs) . "\n";
            
            $zk->disconnect();
        }
        
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
}

echo "\n\nDone.\n";
