<?php
/**
 * Test file to verify button fix implementation
 */

echo "=== BUTTON FIX VERIFICATION ===\n\n";

// 1. Check if attendancelogsimport component exists
$componentPath = 'app/Livewire/AttendanceLogsImport.php';
if (file_exists($componentPath)) {
    echo "✓ Livewire component exists\n";
    $content = file_get_contents($componentPath);
    
    // Check for processPendingRows method
    if (strpos($content, 'public function processPendingRows') !== false) {
        echo "✓ processPendingRows method found\n";
    } else {
        echo "✗ processPendingRows method NOT found\n";
    }
    
    // Check for pendingRows property
    if (strpos($content, 'public array $pendingRows') !== false) {
        echo "✓ pendingRows property found\n";
    } else {
        echo "✗ pendingRows property NOT found\n";
    }
    
    // Check for saveLogs method
    if (strpos($content, 'public function saveLogs') !== false) {
        echo "✓ saveLogs method found\n";
    } else {
        echo "✗ saveLogs method NOT found\n";
    }
} else {
    echo "✗ Livewire component does NOT exist\n";
}

echo "\n";

// 2. Check blade template
$templatePath = 'resources/views/livewire/attendance-logs-import.blade.php';
if (file_exists($templatePath)) {
    echo "✓ Blade template exists\n";
    $content = file_get_contents($templatePath);
    
    // Check for hidden input with wire:model
    if (strpos($content, 'x-ref="logsJson"') !== false && strpos($content, 'wire:model="pendingRows"') !== false) {
        echo "✓ Hidden input with wire:model found\n";
    } else {
        echo "✗ Hidden input with wire:model NOT found\n";
    }
    
    // Check for hidden button with wire:click
    if (strpos($content, 'x-ref="triggerSave"') !== false && strpos($content, 'wire:click="processPendingRows"') !== false) {
        echo "✓ Hidden button with wire:click found\n";
    } else {
        echo "✗ Hidden button with wire:click NOT found\n";
    }
    
    // Check for sendToLivewire method
    if (strpos($content, 'sendToLivewire(method, rows)') !== false) {
        echo "✓ sendToLivewire method found\n";
    } else {
        echo "✗ sendToLivewire method NOT found\n";
    }
    
    // Check for direct component access pattern
    if (strpos($content, "__livewire.set('pendingRows'") !== false || strpos($content, "__livewire.call('processPendingRows'") !== false) {
        echo "✓ Direct Livewire component access found\n";
    } else {
        echo "✗ Direct Livewire component access NOT found\n";
    }
} else {
    echo "✗ Blade template does NOT exist\n";
}

echo "\n";

// 3. Check built assets
echo "Built Assets:\n";
$buildPath = 'public/build/assets/';
if (is_dir($buildPath)) {
    $files = glob($buildPath . '*');
    if (!empty($files)) {
        echo "✓ Build directory exists with " . count($files) . " files\n";
        foreach ($files as $file) {
            echo "  - " . basename($file) . "\n";
        }
    } else {
        echo "✗ Build directory is empty\n";
    }
} else {
    echo "✗ Build directory does NOT exist\n";
}

echo "\n";

// 4. Check manifest
$manifestPath = 'public/build/manifest.json';
if (file_exists($manifestPath)) {
    echo "✓ Manifest.json exists\n";
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (isset($manifest['resources/css/app.css']) && isset($manifest['resources/js/app.js'])) {
        echo "✓ Manifest has both CSS and JS entries\n";
    }
} else {
    echo "✗ Manifest.json does NOT exist\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
