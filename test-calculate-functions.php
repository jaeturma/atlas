<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Carbon\Carbon;
use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Models\Gross;
use App\Models\GrossEntry;

function getPeriodDates($year, $month, $periodType)
{
    $monthStart = Carbon::createFromDate($year, $month, 1);
    $monthEnd = $monthStart->clone()->endOfMonth();

    switch ($periodType) {
        case 'full-month':
        default:
            return [
                'start' => $monthStart->copy()->startOfDay(),
                'end' => $monthEnd->copy()->endOfDay(),
                'label' => $monthStart->format('M Y'),
            ];
    }
}

function calculateDailyEntries($logs, $startDate, $endDate)
{
    $entries = [];

    // Group logs by date
    $logsByDate = $logs->groupBy(function ($log) {
        return $log->log_datetime->format('Y-m-d');
    });

    // Initialize all dates in period
    $currentDate = $startDate->copy();
    while ($currentDate <= $endDate) {
        $dateKey = $currentDate->format('Y-m-d');
        $dateLogs = $logsByDate[$dateKey] ?? collect();

        $entry = [
            'date' => $currentDate->copy(),
            'am_in' => null,
            'am_out' => null,
            'pm_in' => null,
            'pm_out' => null,
            'ot_in' => null,
            'ot_out' => null,
            'total_hours' => 0,
        ];

        // Parse logs for this date
        $timeEntries = [];
        $timeIn = null;

        foreach ($dateLogs as $log) {
            $time = $log->log_datetime->format('H:i:s');

            if (strtoupper($log->status) === 'IN') {
                $timeIn = $time;
            } elseif (strtoupper($log->status) === 'OUT' && $timeIn) {
                $timeEntries[] = ['in' => $timeIn, 'out' => $time];
                $timeIn = null;
            }
        }

        // Assign to AM, PM, OT based on time
        if (count($timeEntries) > 0) {
            $entry['am_in'] = $timeEntries[0]['in'] ?? null;
            $entry['am_out'] = $timeEntries[0]['out'] ?? null;
        }

        if (count($timeEntries) > 1) {
            $entry['pm_in'] = $timeEntries[1]['in'] ?? null;
            $entry['pm_out'] = $timeEntries[1]['out'] ?? null;
        }

        if (count($timeEntries) > 2) {
            $entry['ot_in'] = $timeEntries[2]['in'] ?? null;
            $entry['ot_out'] = $timeEntries[2]['out'] ?? null;
        }

        // Calculate total hours
        $totalHours = 0;
        foreach ($timeEntries as $te) {
            $inTime = Carbon::createFromFormat('H:i:s', $te['in']);
            $outTime = Carbon::createFromFormat('H:i:s', $te['out']);
            $totalHours += $inTime->diffInMinutes($outTime) / 60;
        }
        $entry['total_hours'] = round($totalHours, 2);

        // Only add entry if there are time logs
        if ($totalHours > 0) {
            $entries[] = $entry;
        }

        $currentDate->addDay();
    }

    return $entries;
}

function calculateDaysWorked($entries)
{
    $daysWorked = 0;

    foreach ($entries as $entry) {
        $hours = $entry['total_hours'];

        if ($hours >= 8) {
            $daysWorked += 1.0; // Full day
        } elseif ($hours >= 4) {
            $daysWorked += 0.5; // Half day
        } else {
            $daysWorked += round($hours / 8, 2); // Proportional
        }
    }

    return round($daysWorked, 2);
}

$year = 2026;
$month = 1;
$periodType = 'full-month';

$periodDates = getPeriodDates($year, $month, $periodType);
$employees = Employee::orderBy('first_name')->get();

echo "Testing calculate functions with actual logs...\n\n";

foreach ($employees->take(1) as $employee) {
    echo "Employee: " . $employee->id . " (Badge: " . $employee->badge_number . ")\n";
    
    // Get attendance logs for the period
    $logs = AttendanceLog::where('badge_number', $employee->badge_number)
        ->whereBetween('log_datetime', [$periodDates['start'], $periodDates['end']])
        ->orderBy('log_datetime')
        ->get();

    echo "Logs found: " . $logs->count() . "\n";
    
    if ($logs->count() > 0) {
        // Test calculateDailyEntries
        $entries = calculateDailyEntries($logs, $periodDates['start'], $periodDates['end']);
        echo "Entries calculated: " . count($entries) . "\n";
        
        if (count($entries) > 0) {
            echo "\nFirst 3 entries:\n";
            foreach (array_slice($entries, 0, 3) as $entry) {
                echo "  " . $entry['date']->format('Y-m-d') . ": " . $entry['total_hours'] . " hours\n";
            }
            
            // Test calculateDaysWorked
            $daysWorked = calculateDaysWorked($entries);
            echo "\nDays worked: " . $daysWorked . "\n";
        } else {
            echo "No entries calculated!\n";
        }
    }
}
