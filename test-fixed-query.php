<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\AttendanceLog;

function getPeriodDates($year, $month, $periodType)
{
    $monthStart = Carbon::createFromDate($year, $month, 1);
    $monthEnd = $monthStart->clone()->endOfMonth();

    switch ($periodType) {
        case '1-15':
            return [
                'start' => $monthStart->copy()->startOfDay(),
                'end' => $monthStart->copy()->setDay(15)->endOfDay(),
                'label' => $monthStart->format('M 1-15, Y'),
            ];
        case '16-30':
            return [
                'start' => $monthStart->copy()->setDay(16)->startOfDay(),
                'end' => $monthEnd->copy()->endOfDay(),
                'label' => $monthStart->format('M 16-') . $monthEnd->format('d, Y'),
            ];
        case 'full-month':
        default:
            return [
                'start' => $monthStart->copy()->startOfDay(),
                'end' => $monthEnd->copy()->endOfDay(),
                'label' => $monthStart->format('M Y'),
            ];
    }
}

$year = 2026;
$month = 1;
$periodType = 'full-month';

$periodDates = getPeriodDates($year, $month, $periodType);

echo "Period Type: $periodType\n";
echo "Start: " . $periodDates['start']->toDateTimeString() . "\n";
echo "End: " . $periodDates['end']->toDateTimeString() . "\n";

// Now test the query
$logs = AttendanceLog::whereBetween('log_datetime', [$periodDates['start'], $periodDates['end']])
    ->where('badge_number', '8406069')
    ->get();

echo "\n=== Testing query with badge_number 8406069 ===\n";
echo "Found " . $logs->count() . " logs\n";

if ($logs->count() > 0) {
    foreach ($logs->take(5) as $log) {
        echo "  - " . $log->log_datetime . "\n";
    }
}

// Test with all badges
echo "\n=== Testing with all badges ===\n";
$badgeNumbers = ['5101395', '7403330', '7416880', '7423010', '8406069', '9412660'];
foreach ($badgeNumbers as $badge) {
    $count = AttendanceLog::whereBetween('log_datetime', [$periodDates['start'], $periodDates['end']])
        ->where('badge_number', $badge)
        ->count();
    echo "Badge $badge: $count logs\n";
}
