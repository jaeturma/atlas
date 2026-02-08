<?php

require 'vendor/autoload.php';

echo "=== Time Sync Diagnostic ===\n\n";

echo "Server Information:\n";
echo "  - Server Time: " . date('Y-m-d H:i:s') . "\n";
echo "  - Server Timezone: " . date_default_timezone_get() . "\n";
echo "  - PHP Timezone: " . ini_get('date.timezone') . "\n";

echo "\nLaravel Configuration:\n";
echo "  - Config Timezone: UTC (from config/app.php)\n";

echo "\nCurrent Issue:\n";
echo "  - Device shows: 6:12 PM (18:12)\n";
echo "  - Response shows: 10:11 AM\n";
echo "  - Difference: ~8 hours\n";

echo "\nPossible Causes:\n";
echo "  1. Device is in a different timezone than server\n";
echo "  2. Time parsing is treating device time as UTC when it's in another timezone\n";
echo "  3. Device time returned by ZKTeco library is already in a different format\n";

echo "\nSolution Needed:\n";
echo "  - Determine device timezone\n";
echo "  - Ensure time comparison accounts for timezone differences\n";
echo "  - Show both times with their respective timezones\n";

echo "\nCommon Timezone Offsets from UTC:\n";
$timezones = [
    'EST' => -5,  // Eastern Standard Time
    'CST' => -6,  // Central Standard Time
    'MST' => -7,  // Mountain Standard Time
    'PST' => -8,  // Pacific Standard Time
    'UTC' => 0,   // Universal Coordinated Time
];

foreach ($timezones as $tz => $offset) {
    echo "  - $tz (UTC$offset): ";
    $time_offset = date_create('now', timezone_open('UTC'))->modify(($offset > 0 ? '+' : '') . ($offset * 3600) . ' seconds');
    echo date_format($time_offset, 'H:i (same as ') . (date('H') - abs($offset)) . ":i UTC)\n";
}

echo "\n=== Recommendation ===\n";
echo "Add timezone detection/configuration for devices\n";
echo "Allow users to specify device timezone in device settings\n";
