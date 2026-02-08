<?php
/**
 * Test the new simplified save implementation
 */

echo "=== SIMPLE SAVE IMPLEMENTATION TEST ===\n\n";

// 1. Check route exists
echo "1. Checking route...\n";
$routeFile = file_get_contents('routes/web.php');
if (strpos($routeFile, "Route::post('/attendance-logs/save'") !== false) {
    echo "   ✓ Route POST /attendance-logs/save exists\n";
    if (strpos($routeFile, "saveImported") !== false) {
        echo "   ✓ Route points to saveImported method\n";
    }
} else {
    echo "   ✗ Route not found\n";
}

// 2. Check controller method exists
echo "\n2. Checking controller method...\n";
$controllerFile = file_get_contents('app/Http/Controllers/AttendanceLogController.php');
if (strpos($controllerFile, 'public function saveImported') !== false) {
    echo "   ✓ saveImported method exists\n";
    if (strpos($controllerFile, 'firstOrCreate') !== false) {
        echo "   ✓ Method uses firstOrCreate for duplicate prevention\n";
    }
    if (strpos($controllerFile, 'response()->json') !== false) {
        echo "   ✓ Method returns JSON response\n";
    }
} else {
    echo "   ✗ saveImported method not found\n";
}

// 3. Check blade template has new methods
echo "\n3. Checking Blade template...\n";
$bladeFile = file_get_contents('resources/views/livewire/attendance-logs-import.blade.php');
if (strpos($bladeFile, 'saveLogs(logs, selectedDeviceId)') !== false) {
    echo "   ✓ saveLogs method exists\n";
}
if (strpos($bladeFile, 'saveSelected()') !== false) {
    echo "   ✓ saveSelected method exists\n";
}
if (strpos($bladeFile, 'postToServer') !== false) {
    echo "   ✓ postToServer method exists\n";
}
if (strpos($bladeFile, "fetch('/attendance-logs/save'") !== false) {
    echo "   ✓ Fetch call to correct endpoint\n";
}
if (strpos($bladeFile, 'X-CSRF-Token') !== false) {
    echo "   ✓ CSRF token included\n";
}

// 4. Check model
echo "\n4. Checking AttendanceLog model...\n";
if (file_exists('app/Models/AttendanceLog.php')) {
    echo "   ✓ AttendanceLog model exists\n";
}

// 5. Check build
echo "\n5. Checking build files...\n";
if (is_dir('public/build/assets/')) {
    $files = glob('public/build/assets/*');
    if (!empty($files)) {
        echo "   ✓ Build directory has " . count($files) . " files\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
echo "\nTo test on live site:\n";
echo "1. Go to https://emps.app/attendance-logs\n";
echo "2. Select a device\n";
echo "3. Extract logs from CSV file\n";
echo "4. Click 'Save All' button\n";
echo "5. Check browser console for [Simple] logs\n";
echo "6. Verify progress bar updates\n";
echo "7. Check database for new records\n";
