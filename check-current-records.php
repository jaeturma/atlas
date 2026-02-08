<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Gross;
use App\Models\GrossEntry;
use Carbon\Carbon;

echo "=== CURRENT GROSS RECORDS ===\n";
$records = Gross::with('employee', 'entries')->orderBy('created_at', 'desc')->get();

echo "Total records: " . $records->count() . "\n\n";

foreach ($records as $record) {
    echo "ID: {$record->id}\n";
    echo "  Employee: " . ($record->employee ? $record->employee->getFullName() : 'NULL') . "\n";
    echo "  Period: " . $record->period_start->format('Y-m-d') . " to " . $record->period_end->format('Y-m-d') . "\n";
    echo "  Type: {$record->period_type}\n";
    echo "  Days Worked: {$record->days_worked}\n";
    echo "  Entries: " . $record->entries->count() . "\n";
    if ($record->entries->count() > 0) {
        foreach ($record->entries->take(3) as $entry) {
            echo "    - {$entry->date}: {$entry->total_hours} hrs (AM: {$entry->am_in}-{$entry->am_out}, PM: {$entry->pm_in}-{$entry->pm_out}, OT: {$entry->ot_in}-{$entry->ot_out})\n";
        }
        if ($record->entries->count() > 3) {
            echo "    ... and " . ($record->entries->count() - 3) . " more\n";
        }
    }
    echo "\n";
}

echo "=== CHECKING JANUARY 2026 RANGE ===\n";
$jan_start = Carbon::createFromDate(2026, 1, 1)->startOfDay();
$jan_end = Carbon::createFromDate(2026, 1, 31)->endOfDay();
$jan_records = Gross::whereBetween('period_start', [$jan_start, $jan_end])
    ->where('period_type', 'full-month')
    ->with('employee')
    ->get();

echo "Records in January 2026 (full-month): " . $jan_records->count() . "\n";
foreach ($jan_records as $record) {
    echo "  - {$record->employee->getFullName()}\n";
}
