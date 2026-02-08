<?php

namespace App\Livewire;

use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AttendanceLogsImport extends Component
{
    public array $parsed = [];
    public int $savedCount = 0;
    public int $skippedCount = 0;
    public int $deviceId = 1;
    public array $pendingRows = [];
    public array $extracted = [];

    public function render()
    {
        return view('livewire.attendance-logs-import', [
            'devices' => Device::orderBy('name')->get(),
        ]);
    }

    public function processPendingRows(): void
    {
        \Log::info('[Livewire] processPendingRows called with:', ['count' => count($this->pendingRows)]);
        $this->saveLogs($this->pendingRows);
    }

    public function saveAll($rows = []): void
    {
        $rows = is_array($rows) ? $rows : (array)$rows;
        \Log::info('[Livewire] saveAll called', [
            'rowsCount' => count($rows),
            'rowsType' => gettype($rows),
            'deviceId' => $this->deviceId
        ]);
        $this->saveLogs($rows);
    }

    public function saveAllLogs($logs = []): void
    {
        $logs = is_array($logs) ? $logs : (array)$logs;
        \Log::info('[Livewire] saveAllLogs called', [
            'logsCount' => count($logs),
            'logsType' => gettype($logs),
            'deviceId' => $this->deviceId
        ]);
        $this->saveLogs($logs);
    }

    public function saveSelected($rows = []): void
    {
        $rows = is_array($rows) ? $rows : (array)$rows;
        \Log::info('[Livewire] saveSelected called', [
            'rowsCount' => count($rows),
            'rowsType' => gettype($rows),
            'deviceId' => $this->deviceId
        ]);
        $this->saveLogs($rows);
    }

    public function saveSelectedLogs($logs = []): void
    {
        $logs = is_array($logs) ? $logs : (array)$logs;
        \Log::info('[Livewire] saveSelectedLogs called', [
            'logsCount' => count($logs),
            'logsType' => gettype($logs),
            'deviceId' => $this->deviceId
        ]);
        $this->saveLogs($logs);
    }

    // Direct methods that don't require parameters (for testing)
    public function doSaveAll($rows = []): void
    {
        if (is_string($rows)) {
            $rows = json_decode($rows, true) ?? [];
        }
        $rows = is_array($rows) ? $rows : (array)$rows;
        \Log::info('[Livewire] doSaveAll called with rows:', ['count' => count($rows)]);
        $this->saveLogs($rows);
    }

    public function doSaveRows($rows = []): void
    {
        $rows = is_array($rows) ? $rows : (array)$rows;
        \Log::info('[Livewire] doSaveRows called with rows:', ['count' => count($rows)]);
        $this->saveLogs($rows);
    }

    public function doSaveSelected($rows = []): void
    {
        if (is_string($rows)) {
            $rows = json_decode($rows, true) ?? [];
        }
        $rows = is_array($rows) ? $rows : (array)$rows;
        \Log::info('[Livewire] doSaveSelected called with rows:', ['count' => count($rows)]);
        $this->saveLogs($rows);
    }

    public function storeRow($rowData): void
    {
        \Log::info('[Livewire] storeRow called with data:', is_array($rowData) ? array_keys($rowData) : get_class($rowData));
        $rowData = is_array($rowData) ? $rowData : (array)$rowData;
        $this->saveLogs([$rowData]);
    }

    private function getPendingRows(): array
    {
        if (!empty($this->pendingRows)) {
            return $this->pendingRows;
        }
        if (!empty($this->pendingRowsJson)) {
            $decoded = json_decode($this->pendingRowsJson, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function saveLogs(array $rows): void
    {
        $saved = 0;
        $skipped = 0;
        $totalRows = count($rows);
        $processed = 0;

        if (!$this->deviceId && !isset($rows[0]['deviceId'])) {
            session()->flash('error', 'Please select a device to map these logs.');
            return;
        }

        \Log::info('[Livewire] Starting saveLogs', [
            'totalRows' => $totalRows,
            'defaultDeviceId' => $this->deviceId,
            'firstRowDeviceId' => isset($rows[0]['deviceId']) ? $rows[0]['deviceId'] : 'not set',
            'sampleRow' => count($rows) > 0 ? array_keys($rows[0]) : []
        ]);
        
        // Dispatch event to frontend to start progress bar
        $this->js("window.dispatchEvent(new CustomEvent('import-saving-start', { detail: { total: {$totalRows} } }))");

        DB::beginTransaction();
        try {
            foreach ($rows as $idx => $row) {
                $processed++;
                // Ensure row is an array
                if (is_object($row)) {
                    $row = (array)$row;
                }
                
                $badge = (string)($row['badge'] ?? '');
                $loggedAt = isset($row['logged_at']) ? Carbon::parse($row['logged_at']) : null;
                // Prefer per-row device id if provided, fallback to component deviceId
                $deviceId = isset($row['deviceId']) && (int)$row['deviceId'] > 0 ? (int)$row['deviceId'] : $this->deviceId;

                \Log::info('[Livewire] Processing row', [
                    'index' => $idx,
                    'badge' => $badge,
                    'loggedAt' => $loggedAt ? $loggedAt->toDateTimeString() : 'null',
                    'deviceId' => $deviceId
                ]);

                // Validate required fields
                if (!$badge || !$loggedAt || !$deviceId) {
                    \Log::warning('[Livewire] Row skipped - missing required field', [
                        'badge' => $badge ? 'ok' : 'missing',
                        'loggedAt' => $loggedAt ? 'ok' : 'missing',
                        'deviceId' => $deviceId ? 'ok' : 'missing'
                    ]);
                    $skipped++;
                    // Send progress update every 10 rows
                    if ($processed % 10 === 0) {
                        $this->js("window.dispatchEvent(new CustomEvent('import-saving-progress', { detail: { processed: {$processed}, total: {$totalRows} } }))");
                    }
                    continue;
                }

                try {
                    // Look up employee by badge number
                    $employee = Employee::where('badge_id', $badge)->first();
                    $employeeId = $employee ? $employee->id : null;

                    // Use firstOrCreate to avoid duplicates (device_id + badge_number + log_datetime)
                    $created = AttendanceLog::firstOrCreate(
                        [
                            'device_id' => $deviceId,
                            'badge_number' => $badge,
                            'log_datetime' => $loggedAt,
                        ],
                        [
                            'employee_id' => $employeeId,
                            'status' => null,
                            'punch_type' => null,
                        ]
                    );

                    // Only count as saved if it was newly created (not duplicate)
                    if ($created->wasRecentlyCreated) {
                        \Log::info('[Livewire] Row created successfully', ['id' => $created->id]);
                        $saved++;
                    } else {
                        \Log::info('[Livewire] Row already exists (duplicate)', ['id' => $created->id]);
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    \Log::error('[Livewire] Error saving log', [
                        'message' => $e->getMessage(),
                        'badge' => $badge,
                        'loggedAt' => $loggedAt ? $loggedAt->toDateTimeString() : 'null'
                    ]);
                    $skipped++;
                }
                
                // Send progress update every 10 rows
                if ($processed % 10 === 0) {
                    $this->js("window.dispatchEvent(new CustomEvent('import-saving-progress', { detail: { processed: {$processed}, total: {$totalRows} } }))");
                }
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('[Livewire] Transaction failed: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            $this->js("window.dispatchEvent(new CustomEvent('import-saving-complete'))");
            session()->flash('error', 'Failed to save logs: '.$e->getMessage());
            return;
        }

        $this->savedCount += $saved;
        $this->skippedCount += $skipped;
        \Log::info('[Livewire] saveLogs completed', [
            'saved' => $saved,
            'skipped' => $skipped,
            'processed' => $processed,
            'totalSavedThisSession' => $this->savedCount,
            'totalSkippedThisSession' => $this->skippedCount
        ]);
        $this->js("window.dispatchEvent(new CustomEvent('import-saving-complete', { detail: { processed: {$totalRows}, total: {$totalRows} } }))");
        session()->flash('success', "Saved {$saved}, skipped {$skipped} (duplicates/invalid)");
    }
}
