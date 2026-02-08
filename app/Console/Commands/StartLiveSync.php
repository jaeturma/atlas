<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Jobs\SyncDeviceAttendanceLogs;
use Illuminate\Console\Command;

class StartLiveSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:live-sync {--interval=60 : Interval between syncs in seconds}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Start live sync of attendance logs using queue jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $interval = (int)$this->option('interval') ?: 60;

        $this->info("Starting live attendance sync...");
        $this->info("Sync interval: {$interval} seconds");
        $this->info("Press Ctrl+C to stop\n");

        // Get all active devices
        $devices = Device::where('is_active', true)->get();

        if ($devices->isEmpty()) {
            $this->error("No active devices found");
            return;
        }

        $this->info("Found " . $devices->count() . " active device(s)\n");

        // Continuous loop
        while (true) {
            foreach ($devices as $device) {
                // Dispatch job to queue
                SyncDeviceAttendanceLogs::dispatch($device);
                $this->line("• Queued sync for: {$device->name} ({$device->ip_address}:{$device->port})");
            }

            $this->line("Waiting {$interval} seconds until next sync...\n");
            sleep($interval);
        }
    }
}
