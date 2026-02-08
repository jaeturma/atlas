<?php

namespace App\Jobs;

use App\Models\Device;
use App\Services\ZKTecoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncDeviceAttendanceLogs implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 30, 60];
    public $timeout = 120;

    private $device;

    /**
     * Create a new job instance.
     */
    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Skip if device is inactive
        if (!$this->device->is_active) {
            Log::info("Skipping sync for inactive device: {$this->device->name}");
            return;
        }

        $service = new ZKTecoService($this->device);

        // Test connection
        $connectionTest = $service->testConnection();
        if (!$connectionTest['success']) {
            Log::warning("Connection failed for device {$this->device->name}: " . $connectionTest['message']);
            throw new \Exception("Device connection failed");
        }

        // Get the latest log timestamp from database for this device
        $lastLog = $this->device->logs()
            ->orderBy('log_datetime', 'desc')
            ->first();

        // Set start date: either from last log or from 7 days ago
        if ($lastLog) {
            $startDate = $lastLog->log_datetime->format('Y-m-d');
        } else {
            $startDate = Carbon::now()->subDays(7)->format('Y-m-d');
        }

        $endDate = Carbon::now()->format('Y-m-d');

        // Download and sync logs
        $result = $service->downloadAttendanceRealtime($startDate, $endDate, $this->device->id);

        if ($result['success']) {
            Log::info("Device {$this->device->name} synced: {$result['logs_count']} new logs");
        } else {
            Log::error("Device {$this->device->name} sync failed: {$result['message']}");
            throw new \Exception($result['message']);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Failed to sync device {$this->device->name}: " . $exception->getMessage());
    }
}
