<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\ZKTecoService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncAttendanceLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync {--device-id= : Sync specific device} {--interval=30 : Polling interval in seconds}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync attendance logs from ZKTeco devices in real-time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = (int)$this->option('interval') ?: 30;
        $deviceId = $this->option('device-id');

        // Get devices to sync
        $query = Device::where('is_active', true);
        
        if ($deviceId) {
            $query->where('id', $deviceId);
            $devices = $query->get();
            if ($devices->isEmpty()) {
                $this->error("Device with ID {$deviceId} not found or is inactive");
                return;
            }
        } else {
            $devices = $query->get();
            if ($devices->isEmpty()) {
                $this->error("No active devices found to sync");
                return;
            }
        }

        $this->info("Starting attendance log sync for " . $devices->count() . " device(s)");
        $this->info("Polling interval: {$interval} seconds");
        $this->info("Press Ctrl+C to stop\n");

        // Continuous polling loop
        while (true) {
            foreach ($devices as $device) {
                try {
                    $this->syncDevice($device);
                } catch (\Exception $e) {
                    $this->error("Error syncing device {$device->name}: " . $e->getMessage());
                }
            }

            // Sleep for the specified interval
            sleep($interval);
        }
    }

    /**
     * Sync a single device
     */
    private function syncDevice(Device $device)
    {
        $service = new ZKTecoService($device);

        // Test connection first
        $connectionTest = $service->testConnection();
        if (!$connectionTest['success']) {
            $this->warn("Device {$device->name} ({$device->ip_address}:{$device->port}) - Connection failed: " . $connectionTest['message']);
            return;
        }

        $this->info("Device {$device->name} - Connected successfully");

        // Get the latest log timestamp from database for this device
        $lastLog = $device->logs()
            ->orderBy('log_datetime', 'desc')
            ->first();

        // Set start date: either from last log or from today (for real-time)
        if ($lastLog) {
            $startDate = $lastLog->log_datetime->format('Y-m-d');
        } else {
            $startDate = Carbon::now()->format('Y-m-d');
        }

        $endDate = Carbon::now()->format('Y-m-d');

        // Download and sync logs
        $result = $service->downloadAttendanceRealtime($startDate, $endDate, $device->id);

        if ($result['success']) {
            if ($result['logs_count'] > 0) {
                $this->info("✓ Device {$device->name} - {$result['logs_count']} new log(s) synced at " . date('H:i:s'));
            } else {
                $this->line("  Device {$device->name} - No new logs (last check: " . date('H:i:s') . ")");
            }
        } else {
            $this->warn("Device {$device->name} - {$result['message']}");
        }
    }
}
