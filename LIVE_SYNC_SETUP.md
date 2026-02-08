# Live Attendance Log Sync - Setup Guide

## Overview
This feature enables real-time synchronization of attendance logs from ZKTeco devices directly into your database. Logs are automatically stored as they are recorded on the device.

## Components Created

### 1. Console Commands

#### `php artisan attendance:sync` (Direct Polling)
Directly polls devices in a continuous loop.

**Usage:**
```bash
# Sync all active devices every 30 seconds
php artisan attendance:sync

# Sync specific device every 60 seconds
php artisan attendance:sync --device-id=1 --interval=60

# Run in background (recommended for Linux/macOS)
nohup php artisan attendance:sync &
```

**Options:**
- `--device-id=ID` - Sync only specific device
- `--interval=SECONDS` - Polling interval (default: 30 seconds)

#### `php artisan attendance:live-sync` (Queue-based)
Uses Laravel queue jobs for better performance and control.

**Usage:**
```bash
# Start queuing sync jobs every 60 seconds
php artisan attendance:live-sync

# Custom interval
php artisan attendance:live-sync --interval=120
```

**Requirements:**
- Queue worker must be running: `php artisan queue:work`

### 2. Queue Job
**File:** `app/Jobs/SyncDeviceAttendanceLogs.php`

- Handles device synchronization asynchronously
- Automatic retry (3 times) with exponential backoff
- Comprehensive logging
- Non-blocking

## Setup Instructions

### Option 1: Direct Polling (Simple, for testing)

```bash
# Terminal 1: Start the sync command
php artisan attendance:sync --interval=30

# Output:
# Starting attendance log sync for 2 device(s)
# Polling interval: 30 seconds
# Press Ctrl+C to stop
# ✓ Device Main Entrance - 2 new log(s) synced
# ✓ Device Back Door - 1 new log(s) synced
```

### Option 2: Queue-based (Production recommended)

```bash
# Terminal 1: Start queue worker
php artisan queue:work

# Terminal 2: Start sync dispatcher
php artisan attendance:live-sync --interval=60

# Output:
# Starting live attendance sync...
# Sync interval: 60 seconds
# Found 2 active device(s)
# • Queued sync for: Main Entrance (192.168.1.100:4370)
# • Queued sync for: Back Door (192.168.1.101:4370)
# Waiting 60 seconds until next sync...
```

### Option 3: Scheduled Background Task (Windows Service)

Create a Windows Service to run the command automatically. Use tools like:
- **NSSM** (Non-Sucking Service Manager)
- **AlwaysUp**
- **Windows Task Scheduler** with a batch file

**Batch file example:** `start_sync.bat`
```batch
@echo off
cd /d "D:\lara\www\emps"
php artisan attendance:sync --interval=30
```

## How It Works

1. **Connection Test** - Verifies device is online
2. **Fetch Last Log** - Gets timestamp of most recent log in database
3. **Date Range** - Queries device for logs since last stored log (or last 7 days)
4. **Parse Data** - Converts device format to database format
5. **Duplicate Check** - Skips logs that already exist
6. **Store** - Saves new logs to database
7. **Loop** - Repeats at specified interval

## Features

✅ **Real-time Sync** - Automatic polling at configurable intervals
✅ **Duplicate Prevention** - Never stores same log twice
✅ **Connection Handling** - Graceful handling of connection failures
✅ **Retry Logic** - Automatic retries on failure (queue-based)
✅ **Logging** - All sync events logged to Laravel logs
✅ **Multi-Device** - Supports unlimited devices simultaneously
✅ **Device Filter** - Sync specific device or all active devices
✅ **Active Check** - Only syncs devices with `is_active = true`

## Monitoring

### View Sync Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Database
```bash
php artisan tinker
>>> Device::first()->logs()->count()
>>> AttendanceLog::where('created_at', '>=', now()->subHour())->count()
```

### Queue Status (if using queue-based)
```bash
php artisan queue:failed   # View failed jobs
php artisan queue:retry all  # Retry failed jobs
```

## Configuration

### Device Settings
- Device must have `is_active = true` to be synced
- Edit device from admin panel to enable/disable sync

### Polling Interval
- **Testing:** 10-30 seconds
- **Production:** 30-60 seconds  
- **High Volume:** 60-120 seconds

### Log Retention
- Logs are kept indefinitely once stored
- Old logs can be exported to CSV/PDF for archival
- No automatic cleanup (manual management recommended)

## Troubleshooting

### Logs Not Syncing
1. Check device is active: `Device::find(1)->is_active`
2. Test connection manually in device view
3. Verify device is connected to network
4. Check Laravel logs: `storage/logs/laravel.log`

### Queue Jobs Not Running
1. Ensure queue worker is running: `php artisan queue:work`
2. Check database for queue table: `migrations` should show queue job entry
3. Run failed jobs: `php artisan queue:retry all`

### High CPU Usage
- Increase polling interval: `--interval=120`
- Reduce number of concurrent devices
- Use queue-based approach with multiple workers

### Connection Timeouts
- Increase timeout in `ZKTecoService`: change `TIMEOUT = 5` to higher value
- Check network connectivity to device
- Verify device IP address and port

## Performance Tips

1. **Queue Workers** - Run multiple workers for better throughput
   ```bash
   php artisan queue:work --workers=4
   ```

2. **Batch Size** - Modify polling interval based on log volume
   - Low volume: 60+ seconds
   - Medium volume: 30 seconds
   - High volume: 10-15 seconds

3. **Database Index** - Ensure attendance_logs table has proper indexes
   ```bash
   php artisan migrate
   ```

## API Integration

You can also trigger sync manually:

```php
use App\Services\ZKTecoService;
use App\Models\Device;

$device = Device::find(1);
$service = new ZKTecoService($device);

// Manual sync
$result = $service->downloadAttendanceRealtime(
    '2025-12-08', 
    '2025-12-08', 
    $device->id
);

if ($result['success']) {
    echo "{$result['logs_count']} new logs synced";
}
```

## Next Steps

1. Start a sync command in terminal
2. Record attendance on device (or simulate with test data)
3. Wait for polling interval to pass
4. Check "Recent Attendance Logs" in device view
5. View detailed logs in "Attendance → Daily Summary"

---

**For support:** Check `storage/logs/laravel.log` for detailed error messages.
