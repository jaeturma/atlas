<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(\Illuminate\Contracts\Http\Kernel::class)->handle(
    $request = \Illuminate\Http\Request::capture()
);

$device = \App\Models\Device::first();
$service = new \App\Services\ZKTecoService($device);

echo "Testing connection to: {$device->name} ({$device->ip_address}:{$device->port})\n";
$result = $service->testConnection();

echo "Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n";
