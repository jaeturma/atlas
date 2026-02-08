<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();
$kernel->handle($request);

echo "=== Devices in Database ===\n\n";

$devices = \App\Models\Device::all();

foreach ($devices as $device) {
    echo "ID: {$device->id}\n";
    echo "Name: {$device->name}\n";
    echo "Model: {$device->model}\n";
    echo "IP: {$device->ip_address}\n";
    echo "Port: {$device->port}\n";
    echo "Protocol: " . ($device->protocol ?? 'auto') . "\n";
    echo "Active: " . ($device->is_active ? 'Yes' : 'No') . "\n";
    echo "\n";
}

if ($devices->isEmpty()) {
    echo "No devices found. Add a device first:\n";
    echo "Device::create([\n";
    echo "    'name' => 'Test Device',\n";
    echo "    'model' => 'ZKTeco WL10',\n";
    echo "    'ip_address' => '10.0.0.25',\n";
    echo "    'port' => 4370,\n";
    echo "    'protocol' => 'auto',\n";
    echo "    'is_active' => true,\n";
    echo "]);\n";
}
