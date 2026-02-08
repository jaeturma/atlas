<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== New Structure - Complete View ===\n\n";

$period = '2026-02-01';
$summaries = App\Models\EmployeeDeduction::where('period_month', $period)
    ->with('employee')
    ->orderBy('employee_id')
    ->get();

echo "Period: " . Carbon\Carbon::parse($period)->format('F Y') . "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

foreach ($summaries as $summary) {
    $emp = $summary->employee;
    
    echo "┌─ {$emp->first_name} {$emp->last_name} (ID: {$emp->id})\n";
    echo "│  Summary: ₱" . number_format($summary->total_amount, 2) . " [{$summary->status}]\n";
    echo "│\n";
    echo "│  Details from employee_deduction_entries:\n";
    
    // Get entries for this employee
    $entries = App\Models\EmployeeDeductionEntry::where('employee_id', $emp->id)
        ->where('period_month', $period)
        ->with(['deduction', 'cashAdvance'])
        ->get();
    
    foreach ($entries as $entry) {
        $name = $entry->kind === 'deduction' 
            ? $entry->deduction->name 
            : 'Cash Advance #' . $entry->cash_advance_id;
        echo "│    • {$name}: ₱" . number_format($entry->amount, 2) . "\n";
    }
    
    $entryTotal = $entries->sum('amount');
    echo "│  ─────────────────────────────\n";
    echo "│  Subtotal: ₱" . number_format($entryTotal, 2);
    
    if ($entryTotal == $summary->total_amount) {
        echo " ✓\n";
    } else {
        echo " ⚠ (mismatch!)\n";
    }
    
    echo "└─────────────────────────────────────────────────────────────\n\n";
}

echo "=== Summary Statistics ===\n";
echo "employee_deductions table: " . $summaries->count() . " rows\n";
echo "employee_deduction_entries table: " . App\Models\EmployeeDeductionEntry::where('period_month', $period)->count() . " rows\n";
echo "Total Amount: ₱" . number_format($summaries->sum('total_amount'), 2) . "\n";
