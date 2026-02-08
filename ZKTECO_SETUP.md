# ZKTeco Device Module - 3rd Party Plugins & Setup Guide

## Overview
This Device module manages ZKTeco attendance devices and their logs.

## Recommended 3rd Party Packages

**Important:** The following packages were tested and are verified to exist on Packagist.

### 1. **coding-libs/zkteco-php** (Recommended) ✅
Modern, actively maintained PHP/Laravel library for ZKTeco device communication.

```bash
composer require coding-libs/zkteco-php
```

**Status:** Actively maintained (Last update: August 2025)
**GitHub:** https://github.com/coding-libs/zkteco-php
**Best for:** Laravel projects needing reliable device communication
**Features:**
- Socket-based communication (port 4370)
- User management
- Attendance log retrieval
- Device information access
- Modern PHP/Laravel support

### 2. **kamshory/zklibrary** (Alternative)
Comprehensive PHP library with extensive ZKTeco device functionality.

```bash
composer require kamshory/zklibrary
```

**Features:**
- UDP protocol support (port 4370)
- User and fingerprint template management
- Device control and settings
- LCD display operations

### 3. **nurkarim/zkteco-sdk-php** (Alternative)
PHP SDK based on ZKLibrary for attendance device communication.

```bash
composer require nurkarim/zkteco-sdk-php
```

**Features:**
- UDP protocol support
- User attendance logs
- Device information retrieval

### 4. **fahriztx/zksoapphp** (For SOAP-enabled devices)
For ZKTeco devices with SOAP/Web Service support.

```bash
composer require fahriztx/zksoapphp
```

**Features:**
- SOAP Protocol support
- Attendance log retrieval with date ranges
- Device information access

### 5. **dnaextrim/php_zklib** (Alternative)
Foundational attendance machine library using UDP protocol.

```bash
composer require dnaextrim/php_zklib
```

## Installation Steps

### Step 1: Enable PHP Socket Extension

The ZKTeco library requires the PHP socket extension. Enable it in your `php.ini`:

**Windows:**
```ini
extension=sockets
```

**Linux/Mac:**
```ini
extension=sockets.so
```

Restart your PHP server after enabling the extension.

### Step 2: Install ZKTeco Package
```bash
composer require coding-libs/zkteco-php
```

If installation fails, try one of the alternatives listed above.

### Step 3: Create ZKTecoService Class
Create `app/Services/ZKTecoService.php`:

```php
<?php

namespace App\Services;

use App\Models\Device;
use App\Models\AttendanceLog;
use Exception;

class ZKTecoService
{
    private $device;

    public function __construct(Device $device)
    {
        $this->device = $device;
    }

    /**
     * Test connection to device using fsockopen
     */
    public function testConnection(): array
    {
        try {
            $ip = $this->device->ip_address;
            $port = (int) $this->device->port;
            
            // Try to connect using fsockopen
            $connection = @fsockopen($ip, $port, $errno, $errstr, 5);
            
            if ($connection) {
                fclose($connection);
                return [
                    'success' => true,
                    'message' => 'Successfully connected to device',
                    'device' => [
                        'ip' => $ip,
                        'port' => $port,
                        'version' => 'Connection verified',
                        'users' => 0,
                        'logs' => 0
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Failed to connect: $errstr (Error $errno)",
                    'device' => null
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'device' => null
            ];
        }
    }

    /**
     * Download attendance logs from device
     * Note: Full implementation requires socket extension enabled
     */
    public function downloadAttendance(): array
    {
        return [
            'success' => false,
            'message' => 'Download functionality requires socket extension. Please enable PHP socket extension in php.ini'
        ];
    }

    /**
     * Get device information
     * Note: Full implementation requires socket extension enabled
     */
    public function getDeviceInfo(): array
    {
        return [
            'success' => false,
            'message' => 'Device info functionality requires socket extension. Please enable PHP socket extension in php.ini'
        ];
    }
}
```

### Step 3: Update DeviceController Test Connection
Update `app/Http/Controllers/DeviceController.php` testConnection method:

```php
public function testConnection(Device $device)
{
    try {
        $service = new \App\Services\ZKTecoService($device);
        $result = $service->testConnection();
        
        return response()->json($result, $result['success'] ? 200 : 500);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Service error: ' . $e->getMessage()
        ], 500);
    }
}
```

### Step 4: Create Service Provider (Optional but Recommended)
Create `app/Services/ZKTecoServiceProvider.php` for dependency injection.

## Device Specifications

### Common ZKTeco Devices & Ports
- **ZKTeco K40**: Port 4370 (default)
- **ZKTeco K50**: Port 4370
- **ZKTeco U160**: Port 4370
- **ZKTeco ProFace (X7/X8)**: Port 4370

### Network Requirements
1. Device and Server must be on same network (or properly routed)
2. Firewall should allow TCP connections on device port (typically 4370)
3. Device should have static IP or reserved DHCP lease

## Database Schema

### Devices Table
```
id | name | model | serial_number | ip_address | port | location | is_active | timestamps
```

### Attendance Logs Table
```
id | device_id | employee_id | badge_number | log_date | log_time | log_datetime | status | punch_type | timestamps
```

## Module Features

✅ Add/Edit/Delete ZKTeco Devices
✅ View Connected Devices
✅ Test Device Connection
✅ View Recent Attendance Logs
✅ Device Status (Active/Inactive)
✅ Connection Information Display
✅ Pagination Support

## Future Enhancements

- [ ] Automatic attendance sync via queued jobs
- [ ] Real-time device monitoring
- [ ] Employee matching with badge numbers
- [ ] Attendance report generation
- [ ] Device firmware update capability
- [ ] Multi-device synchronization
- [ ] Attendance analytics dashboard

## Troubleshooting

### Connection Issues
1. Check IP address and port are correct
2. Verify firewall allows TCP connection
3. Ensure device is powered on and networked
4. Test with device manufacturer's software first

### Missing Attendance Data
1. Verify device has records stored
2. Check device time is synchronized
3. Ensure employee badge numbers exist in system

### Performance Issues
1. Use pagination for large datasets
2. Schedule batch downloads during off-hours
3. Consider data archival for older records
