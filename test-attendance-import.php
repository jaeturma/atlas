<?php
/**
 * Test script for attendance logs import functionality
 * Tests the Save All and Save Selected buttons
 */

require 'vendor/autoload.php';

use App\Models\AttendanceLog;
use App\Models\Device;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=" . str_repeat("=", 70) . "\n";
echo "ATTENDANCE LOGS IMPORT TEST\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Get first device or create a test one
$device = Device::first();
if (!$device) {
    echo "[ERROR] No devices found in database. Please create at least one device first.\n";
    exit(1);
}
$testDeviceId = $device->id;

echo "[1] Testing with device: {$device->name} (ID: {$testDeviceId})\n\n";

// Test 1: Insert a single record
echo "[2] Test 1: Insert single attendance log\n";
$log1 = AttendanceLog::firstOrCreate(
    [
        'device_id' => $testDeviceId,
        'badge_number' => '12345',
        'log_datetime' => Carbon::parse('2025-01-15 08:30:00'),
    ],
    ['status' => 'In', 'punch_type' => 'Fingerprint']
);

if ($log1->wasRecentlyCreated) {
    echo "✓ New record created\n";
    echo "  ID: {$log1->id}\n";
    echo "  Badge: {$log1->badge_number}\n";
    echo "  DateTime: {$log1->log_datetime}\n";
    echo "  Date (generated): {$log1->log_date}\n";
    echo "  Time (generated): {$log1->log_time}\n";
} else {
    echo "✓ Record already exists: ID {$log1->id}\n";
}

// Test 2: Try to insert duplicate (should skip)
echo "\n[3] Test 2: Attempt duplicate insert\n";
$log2 = AttendanceLog::firstOrCreate(
    [
        'device_id' => $testDeviceId,
        'badge_number' => '12345',
        'log_datetime' => Carbon::parse('2025-01-15 08:30:00'),
    ],
    ['status' => 'In', 'punch_type' => 'Fingerprint']
);

if ($log2->wasRecentlyCreated) {
    echo "✗ Duplicate was created (unexpected)\n";
} else {
    echo "✓ Duplicate detected and skipped correctly\n";
    echo "  Same ID: {$log2->id}\n";
}

// Test 3: Bulk insert simulation
echo "\n[4] Test 3: Bulk insert (simulating Save All)\n";
$testRecords = [
    [
        'badge' => '12346',
        'logged_at' => '2025-01-15 09:15:00',
        'deviceId' => $testDeviceId,
    ],
    [
        'badge' => '12347',
        'logged_at' => '2025-01-15 10:30:00',
        'deviceId' => $testDeviceId,
    ],
    [
        'badge' => '12348',
        'logged_at' => '2025-01-15 11:45:00',
        'deviceId' => $testDeviceId,
    ],
];

$saved = 0;
$skipped = 0;

foreach ($testRecords as $row) {
    $badge = $row['badge'];
    $loggedAt = Carbon::parse($row['logged_at']);
    $deviceId = $row['deviceId'] ?? 1;

    if (!$badge || !$loggedAt || !$deviceId) {
        $skipped++;
        continue;
    }

    $created = AttendanceLog::firstOrCreate(
        [
            'device_id' => $deviceId,
            'badge_number' => $badge,
            'log_datetime' => $loggedAt,
        ],
        ['status' => null, 'punch_type' => null]
    );

    if ($created->wasRecentlyCreated) {
        $saved++;
        echo "  ✓ Saved: Badge {$badge} at {$loggedAt}\n";
    } else {
        $skipped++;
        echo "  ○ Skipped: Badge {$badge} (duplicate)\n";
    }
}

echo "  Result: {$saved} saved, {$skipped} skipped\n";

// Test 4: Verify database has correct generated columns
echo "\n[5] Test 4: Verify generated columns\n";
$latestLog = AttendanceLog::where('device_id', $testDeviceId)->latest()->first();
if ($latestLog) {
    echo "  Latest log ID: {$latestLog->id}\n";
    echo "  log_datetime: {$latestLog->log_datetime}\n";
    echo "  log_date: {$latestLog->log_date} (generated)\n";
    echo "  log_time: {$latestLog->log_time} (generated)\n";
    
    if ($latestLog->log_date && $latestLog->log_time) {
        echo "  ✓ Generated columns working correctly\n";
    } else {
        echo "  ✗ Generated columns NOT working - they are NULL\n";
    }
}

// Summary
echo "\n" . "=" . str_repeat("=", 70) . "\n";
echo "TEST COMPLETED\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "\nIf generated columns are NULL, check if the migration ran correctly:\n";
echo "  Run: php artisan migrate:status\n";
echo "  Run: php artisan migrate\n\n";
?>
