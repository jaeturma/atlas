<?php
/**
 * Quick debug script to test the Livewire component methods directly
 */

require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Livewire\AttendanceLogsImport;
use App\Models\AttendanceLog;
use Carbon\Carbon;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "Testing Attendance Logs Import Component\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// Create a test component instance
$component = new AttendanceLogsImport();

// Test data
$testRows = [
    [
        'badge' => '001',
        'logged_at' => '2025-01-15 08:00:00',
        'deviceId' => 1
    ],
    [
        'badge' => '002',
        'logged_at' => '2025-01-15 08:15:00',
        'deviceId' => 1
    ],
];

echo "[1] Component instance created\n";
echo "    - Default deviceId: " . $component->deviceId . "\n";
echo "    - pendingRows: " . count($component->pendingRows) . "\n\n";

// Simulate what the button would do
echo "[2] Setting pendingRows (like prepareSaveAll would do)\n";
$component->pendingRows = $testRows;
echo "    - pendingRows set to " . count($component->pendingRows) . " rows\n\n";

// Call the Livewire method
echo "[3] Calling processPendingRows() method\n";
try {
    $component->processPendingRows();
    echo "    ✓ Method called successfully\n";
} catch (\Exception $e) {
    echo "    ✗ Error: " . $e->getMessage() . "\n";
}

echo "\nTest completed!\n";
echo "\nIf you see errors above, check:\n";
echo "1. Is the doSaveAll() method properly defined?\n";
echo "2. Are the Livewire event handlers working?\n";
echo "3. Check browser console for JavaScript errors\n";
?>
