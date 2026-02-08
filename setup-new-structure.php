<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Setting Up Deduction Assignments ===\n\n";

// Clear old data
DB::table('employee_deduction_entries')->delete();
DB::table('employee_deductions')->delete();
DB::table('employee_deduction_assignments')->delete();

echo "Cleared existing data\n\n";

// Get or ensure deductions exist
$sss = App\Models\Deduction::firstOrCreate(
    ['code' => 'SSS'],
    [
        'name' => 'SSS',
        'description' => 'Social Security System',
        'is_recurring' => true,
        'default_amount' => 500.00,
        'frequency' => 'monthly',
        'is_active' => true,
    ]
);

$philhealth = App\Models\Deduction::firstOrCreate(
    ['code' => 'PHILHEALTH'],
    [
        'name' => 'PhilHealth',
        'description' => 'Philippine Health Insurance',
        'is_recurring' => true,
        'default_amount' => 200.00,
        'frequency' => 'monthly',
        'is_active' => true,
    ]
);

echo "Deductions ready:\n";
echo "  - {$sss->name}: ₱" . number_format($sss->default_amount, 2) . "\n";
echo "  - {$philhealth->name}: ₱" . number_format($philhealth->default_amount, 2) . "\n\n";

// Assign to all active employees
$employees = App\Models\Employee::where('is_active', true)->get();
echo "Assigning deductions to {$employees->count()} employees:\n\n";

$assignedCount = 0;

foreach ($employees as $emp) {
    echo "{$emp->first_name} {$emp->last_name}:\n";
    
    // Assign SSS
    $emp->deductions()->syncWithoutDetaching([
        $sss->id => [
            'amount' => 500.00,
            'frequency' => 'monthly',
            'is_active' => true,
            'start_date' => now(),
        ]
    ]);
    echo "  ✓ SSS: ₱500.00\n";
    $assignedCount++;
    
    // Assign PhilHealth
    $emp->deductions()->syncWithoutDetaching([
        $philhealth->id => [
            'amount' => 200.00,
            'frequency' => 'monthly',
            'is_active' => true,
            'start_date' => now(),
        ]
    ]);
    echo "  ✓ PhilHealth: ₱200.00\n";
    $assignedCount++;
    
    echo "  Total per period: ₱700.00\n\n";
}

echo "=== Summary ===\n";
echo "Assignments created: {$assignedCount}\n";
echo "Assignments in DB: " . DB::table('employee_deduction_assignments')->where('is_active', true)->count() . "\n";
echo "\nReady to test generation!\n";
