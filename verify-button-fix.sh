#!/bin/bash
# Quick verification script to test the button fix

echo "=== ATTENDANCE LOGS BUTTON FIX VERIFICATION ==="
echo ""

# Check PHP component
echo "1. Checking Livewire Component..."
if grep -q "public function saveLogs" app/Livewire/AttendanceLogsImport.php; then
    echo "   ✓ saveLogs method is PUBLIC"
else
    echo "   ✗ saveLogs method is NOT public - FIX FAILED"
fi

# Check Blade template for hidden input
echo ""
echo "2. Checking Hidden Input..."
if grep -q 'x-ref="logsJson".*wire:model.live="pendingRows"' resources/views/livewire/attendance-logs-import.blade.php; then
    echo "   ✓ Hidden input with wire:model.live found"
else
    echo "   ✗ Hidden input is missing or incorrect"
fi

# Check Blade template for hidden button
echo ""
echo "3. Checking Hidden Button..."
if grep -q 'x-ref="triggerSave".*wire:click="saveLogs' resources/views/livewire/attendance-logs-import.blade.php; then
    echo "   ✓ Hidden button with wire:click found"
else
    echo "   ✗ Hidden button is missing or incorrect"
fi

# Check sendToLivewire method
echo ""
echo "4. Checking sendToLivewire Method..."
if grep -q "dispatchEvent(event)" resources/views/livewire/attendance-logs-import.blade.php; then
    echo "   ✓ sendToLivewire uses event dispatch"
else
    echo "   ✗ sendToLivewire method not properly updated"
fi

# Check build files
echo ""
echo "5. Checking Built Assets..."
BUILD_COUNT=$(ls -1 public/build/assets/ 2>/dev/null | wc -l)
if [ $BUILD_COUNT -gt 0 ]; then
    echo "   ✓ Build assets found ($BUILD_COUNT files)"
else
    echo "   ✗ No build assets found"
fi

# Check manifest
echo ""
echo "6. Checking Manifest..."
if [ -f public/build/manifest.json ]; then
    echo "   ✓ Manifest.json exists"
else
    echo "   ✗ Manifest.json missing"
fi

echo ""
echo "=== VERIFICATION COMPLETE ==="
echo ""
echo "Next steps:"
echo "1. Open https://emps.app/attendance-logs in browser"
echo "2. Extract logs from USB file"
echo "3. Click 'Save All' button"
echo "4. Check browser console (F12) for [Alpine] and [Livewire] logs"
echo "5. Verify progress bar updates and records save to database"
