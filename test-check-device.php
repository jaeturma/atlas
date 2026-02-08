<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();

if ($device) {
    echo "Device in Database:\n";
    echo "  Name: {$device->name}\n";
    echo "  Model: {$device->model}\n";
    echo "  Serial: {$device->serial_number}\n";
    echo "  IP: {$device->ip_address}\n";
    echo "  Port: {$device->port}\n";
    echo "  Location: {$device->location}\n";
    echo "  Active: " . ($device->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "No device found in database\n";
}
