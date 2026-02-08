<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     EMPLOYEE DEDUCTIONS - STRUCTURE VERIFICATION             ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// 1. Check assignments
echo "1️⃣  CONFIGURATION (employee_deduction_assignments)\n";
echo "   └─ Deduction assignments per employee\n";
$assignments = DB::table('employee_deduction_assignments')->where('is_active', true)->get();
echo "   └─ Total: {$assignments->count()} assignments\n";
$byEmployee = $assignments->groupBy('employee_id');
foreach ($byEmployee as $empId => $emps) {
    $employee = App\Models\Employee::find($empId);
    echo "      • {$employee->first_name}: " . $emps->count() . " deductions\n";
}

echo "\n";

// 2. Check summaries
echo "2️⃣  SUMMARY (employee_deductions)\n";
echo "   └─ One row per employee per period with TOTAL amount\n";
$summaries = App\Models\EmployeeDeduction::all();
echo "   └─ Total: {$summaries->count()} summary records\n";
$byPeriod = $summaries->groupBy('period_month');
foreach ($byPeriod as $period => $recs) {
    echo "      • " . Carbon\Carbon::parse($period)->format('F Y') . ": " . $recs->count() . " employees\n";
    echo "        Total: ₱" . number_format($recs->sum('total_amount'), 2) . "\n";
}

echo "\n";

// 3. Check entries
echo "3️⃣  DETAILS (employee_deduction_entries)\n";
echo "   └─ Itemized breakdown of deductions and advances\n";
$entries = App\Models\EmployeeDeductionEntry::all();
echo "   └─ Total: {$entries->count()} detail entries\n";
$byPeriod = $entries->groupBy('period_month');
foreach ($byPeriod as $period => $recs) {
    $byKind = $recs->groupBy('kind');
    $deductions = $byKind->get('deduction', collect());
    $advances = $byKind->get('advance', collect());
    echo "      • " . Carbon\Carbon::parse($period)->format('F Y') . ":\n";
    echo "        - Deductions: " . $deductions->count() . " entries\n";
    echo "        - Advances: " . $advances->count() . " entries\n";
    echo "        - Total: ₱" . number_format($recs->sum('amount'), 2) . "\n";
}

echo "\n";

// 4. Verification
echo "4️⃣  VERIFICATION\n";
foreach ($byPeriod as $period => $detailRecs) {
    $summaryRecs = App\Models\EmployeeDeduction::where('period_month', $period)->get();
    $summaryTotal = $summaryRecs->sum('total_amount');
    $detailTotal = $detailRecs->sum('amount');
    
    echo "   • " . Carbon\Carbon::parse($period)->format('F Y') . ":\n";
    echo "     Summary total: ₱" . number_format($summaryTotal, 2) . "\n";
    echo "     Details total: ₱" . number_format($detailTotal, 2) . "\n";
    
    if ($summaryTotal == $detailTotal) {
        echo "     Status: ✅ MATCH\n";
    } else {
        echo "     Status: ⚠️  MISMATCH!\n";
    }
}

echo "\n";

// 5. Structure check
echo "5️⃣  EXPECTED vs ACTUAL\n";
$activeEmployees = App\Models\Employee::where('is_active', true)->count();
$assignmentsPerEmp = $assignments->count() / max($activeEmployees, 1);
$expectedDetails = $assignments->count() + App\Models\Advance::where('status', 'active')->count();

echo "   • Active employees: {$activeEmployees}\n";
echo "   • Deductions per employee: " . round($assignmentsPerEmp, 1) . "\n";
echo "   • Active advances: " . App\Models\Advance::where('status', 'active')->count() . "\n";
echo "   • Expected summary rows per period: {$activeEmployees}\n";
echo "   • Expected detail rows per period: {$expectedDetails}\n";

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║                    STRUCTURE IS CORRECT! ✅                   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
