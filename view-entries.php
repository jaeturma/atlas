<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Generated Entries Details ===\n\n";

$entries = App\Models\EmployeeDeductionEntry::where('period_month', '2026-01-01')
    ->with(['employee', 'deduction', 'cashAdvance'])
    ->orderBy('employee_id')
    ->orderBy('kind')
    ->get();

$currentEmpId = null;
$empTotal = 0;
$grandTotal = 0;

foreach ($entries as $entry) {
    $emp = $entry->employee;
    
    if ($currentEmpId !== $emp->id) {
        if ($currentEmpId !== null) {
            echo "    Subtotal: ₱" . number_format($empTotal, 2) . "\n\n";
        }
        $currentEmpId = $emp->id;
        $empTotal = 0;
        echo "{$emp->first_name} {$emp->last_name} (ID: {$emp->id})\n";
    }
    
    $name = $entry->kind === 'deduction' 
        ? $entry->deduction->name 
        : 'Cash Advance';
    
    echo "  - {$name}: ₱" . number_format($entry->amount, 2) . " [{$entry->status}]\n";
    
    $empTotal += $entry->amount;
    $grandTotal += $entry->amount;
}

if ($currentEmpId !== null) {
    echo "    Subtotal: ₱" . number_format($empTotal, 2) . "\n\n";
}

echo "=== Summary ===\n";
echo "Total Entries: " . $entries->count() . "\n";
echo "Total Amount: ₱" . number_format($grandTotal, 2) . "\n";
echo "\nBreakdown:\n";
$deductions = $entries->where('kind', 'deduction');
$advances = $entries->where('kind', 'advance');
echo "  Deductions: " . $deductions->count() . " entries (₱" . number_format($deductions->sum('amount'), 2) . ")\n";
echo "  Advances: " . $advances->count() . " entries (₱" . number_format($advances->sum('amount'), 2) . ")\n";
