<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

$device = \App\Models\Device::first();
if ($device) {
    echo "Testing with device: {$device->ip_address}:{$device->port}\n";
    $controller = new \App\Http\Controllers\DeviceController();
    $response = $controller->getDeviceTime($device);
    echo "Response status: " . $response->getStatusCode() . "\n";
    echo "Response body: " . $response->getContent() . "\n";
} else {
    echo "No device found\n";
}
