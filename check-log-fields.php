<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AttendanceLog;

$logs = AttendanceLog::limit(5)->get();
echo "Sample attendance logs:\n";
foreach ($logs as $log) {
    echo "ID: {$log->id}\n";
    echo "  Badge: {$log->badge_number}\n";
    echo "  DateTime: {$log->log_datetime}\n";
    echo "  Status: " . ($log->status ?? 'NULL') . "\n";
    echo "  Punch Type: " . ($log->punch_type ?? 'NULL') . "\n";
    echo "\n";
}
