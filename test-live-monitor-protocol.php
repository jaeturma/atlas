<?php

/**
 * Test Live Attendance Monitor with Protocol Support
 * 
 * Tests the updated live-monitor implementation that shows protocol information
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = \Illuminate\Http\Request::capture();

use App\Models\Device;
use App\Models\AttendanceLog;
use App\Services\DeviceProtocolManager;
use Carbon\Carbon;

echo "=== Live Attendance Monitor Test with Protocol Support ===\n\n";

// Get active devices
$devices = Device::where('is_active', true)->get();
echo "Active Devices: " . $devices->count() . "\n";

foreach ($devices as $device) {
    echo "\n📱 Device: {$device->name} ({$device->ip_address}:{$device->port})\n";
    
    // Create protocol manager
    $protocolManager = new DeviceProtocolManager($device);
    $protocol = $protocolManager->detectProtocol();
    echo "   Detected Protocol: " . strtoupper($protocol) . "\n";
    
    // Simulate protocol connection
    $result = $protocolManager->connect();
    echo "   Connection Status: " . ($result['success'] ? '✅ Connected' : '❌ Failed') . "\n";
    if ($result['success']) {
        echo "   Protocol Used: " . strtoupper($result['protocol']) . "\n";
    }
}

// Get recent attendance logs
echo "\n=== Recent Attendance Logs (Last 10) ===\n\n";

$recentLogs = AttendanceLog::with(['device', 'employee'])
    ->orderBy('log_datetime', 'desc')
    ->limit(10)
    ->get();

if ($recentLogs->isNotEmpty()) {
    foreach ($recentLogs as $log) {
        $device = $log->device;
        $protocolManager = new DeviceProtocolManager($device);
        $protocol = $protocolManager->detectProtocol();
        
        $status = $log->status === 'In' ? '✓ In' : '✕ Out';
        $protocolIcon = $protocol === 'adms' ? '📡' : '📠';
        
        echo "{$protocolIcon} [{$protocol}] {$log->badge_number} - {$log->device->name} - {$log->log_datetime->format('Y-m-d H:i:s')} - {$status}\n";
    }
} else {
    echo "No attendance logs found\n";
}

// Simulate live feed response structure
echo "\n=== Simulated Live Feed Response ===\n\n";

$formattedResponse = [
    'success' => true,
    'logs' => $recentLogs->take(3)->map(function ($log) {
        $device = $log->device;
        $protocolManager = new DeviceProtocolManager($device);
        
        return [
            'id' => $log->id,
            'badge_number' => $log->badge_number,
            'device_id' => $log->device_id,
            'device_name' => $device->name,
            'device_protocol' => $protocolManager->detectProtocol(),
            'log_datetime' => $log->log_datetime->toIso8601String(),
            'status' => $log->status,
            'punch_type' => $log->punch_type,
            'employee_name' => $log->employee?->getFullName() ?? 'Not Linked',
        ];
    })->toArray(),
    'total' => $recentLogs->count(),
    'timestamp' => now()->toIso8601String(),
];

echo "Response Structure:\n";
echo json_encode($formattedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

// Protocol distribution analysis
echo "\n=== Protocol Distribution Analysis ===\n\n";

$todayLogs = AttendanceLog::whereDate('log_date', today())->get();
echo "Total Logs Today: " . $todayLogs->count() . "\n\n";

if ($todayLogs->isNotEmpty()) {
    $protocolCounts = [];
    
    foreach ($todayLogs as $log) {
        $device = $log->device;
        $protocolManager = new DeviceProtocolManager($device);
        $protocol = $protocolManager->detectProtocol();
        
        if (!isset($protocolCounts[$protocol])) {
            $protocolCounts[$protocol] = 0;
        }
        $protocolCounts[$protocol]++;
    }
    
    $total = array_sum($protocolCounts);
    
    foreach ($protocolCounts as $protocol => $count) {
        $percentage = ($count / $total) * 100;
        $protocolIcon = $protocol === 'adms' ? '📡 ADMS' : '📠 ZKEM';
        $percentStr = number_format($percentage, 1);
        echo "{$protocolIcon}: {$count} logs ({$percentStr}%)\n";
    }
}

echo "\n✅ Live Monitor Protocol Support Test Complete\n";
