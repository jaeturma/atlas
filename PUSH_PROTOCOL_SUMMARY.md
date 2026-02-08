# Push Protocol Implementation Summary

## Overview
Implemented dual-protocol architecture for attendance devices:
- **ZKEM Protocol**: Traditional pull-based (P160, K40, U580, iFace series) - **FULLY INTACT**
- **ADMS/Push Protocol**: HTTP push-based (WL10, WL20, Cloud devices) - **NEW**

## What Was Implemented

### 1. Database Infrastructure ✅
- **Migration**: `2025_12_13_055941_create_system_settings_table.php`
  - Created `system_settings` table with key-value storage
  - Default settings: `push_server_url`, `app_name`
- **Confirmed**: `devices.serial_number` column already exists

### 2. Models ✅
- **SystemSetting** (`app/Models/SystemSetting.php`)
  - `get($key, $default)`: Retrieve setting value
  - `set($key, $value, $type, $group, $description)`: Update or create setting
  - Mass assignable: key, value, type, group, description

### 3. Controllers ✅

#### AttendancePushController (`app/Http/Controllers/AttendancePushController.php`)
**Purpose**: Receive and process attendance logs pushed from ADMS/Cloud devices

**Methods**:
- `receivePush()`: Main endpoint, processes incoming logs
- `parsePayload()`: Handles 3 payload formats (ZKTeco ADMS, Cloud API, Generic)
- `findDevice()`: Matches device by serial_number or IP address
- `parseDateTime()`: Supports Unix timestamps and ISO 8601
- `determineStatus()`: **Same time-based rules as DeviceController**
- `healthCheck()`: Returns endpoint status and timestamp

**Time-Based Status Rules** (Consistent across ZKEM and Push):
- **04:00-09:30 AM** → Status = 'IN'
- **03:00-09:00 PM** → Status = 'OUT'
- **12:00-12:59 PM** → First punch = 'OUT'
- **12:10-01:00 PM** → Second punch (after OUT) = 'IN'
- Per-employee/day tracking with midday sequencing

**Features**:
- Comprehensive logging for debugging
- Duplicate detection (badge_number + datetime)
- Multiple payload format support
- Device matching via serial_number or IP

#### SystemSettingController (`app/Http/Controllers/SystemSettingController.php`)
**Purpose**: Manage system settings via web UI

**Methods**:
- `index()`: Display settings grouped by category
- `update()`: Save updated settings

### 4. Routes ✅

#### API Routes (`routes/api.php`)
```php
POST   /api/attendance/push          → receivePush (no auth)
GET    /api/attendance/push/health   → healthCheck (no auth)
```

#### Web Routes (`routes/web.php`)
```php
GET    /settings    → SystemSettingController@index
PUT    /settings    → SystemSettingController@update
```

### 5. Views ✅

#### Settings Page (`resources/views/settings/index.blade.php`)
**Features**:
- Grouped settings display (by category)
- Push protocol configuration instructions
- Highlighted WL10/ADMS setup guide with:
  - Push endpoint URL display
  - Public URL requirement notice
  - Serial number importance
  - Health check link
  - Troubleshooting tips
- Save settings form with validation
- Success message display

#### Device Form (`resources/views/devices/form.blade.php`)
**Updates**:
- Added helpful note for `serial_number` field:
  - **Required for ADMS/Push devices** (WL10, WL20, etc.)
  - Explains purpose: Match incoming push logs
  - Guidance: Find in device system information

#### Navigation (`resources/views/layouts/navigation.blade.php`)
**Updates**:
- Added "System Settings" link to user dropdown menu

### 6. Configuration ✅

#### Bootstrap (`bootstrap/app.php`)
**Update**: Enabled API routes in Laravel 11
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // NEW
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

### 7. Documentation ✅
- **DEVICE_PUSH_SETUP.md**: Comprehensive guide covering:
  - Push protocol overview
  - Prerequisites (public URL requirement)
  - Step-by-step WL10 configuration
  - Device setup in system
  - Payload format examples (3 formats supported)
  - Time-based status rules
  - Troubleshooting guide
  - ZKEM vs ADMS comparison table
  - Testing with curl examples

## How It Works

### ZKEM Devices (Existing - Unchanged)
1. User clicks "Extract Logs" on device page
2. System connects to device via TCP (port 4370)
3. Pulls attendance logs from device
4. Displays in preview table
5. User can "Store Selected" or "Store All"
6. Logs stored with time-based status rules

### ADMS/Push Devices (New)
1. WL10 device configured with push URL: `https://your-domain.com/api/attendance/push`
2. Device pushes logs automatically (every 1-5 minutes)
3. `AttendancePushController@receivePush` receives POST request
4. Parses payload (supports 3 formats)
5. Finds device by serial_number or IP
6. Finds employee by badge_number
7. Applies time-based status rules
8. Stores in `attendance_logs` table
9. Returns success response to device

### Time-Based Status Assignment (Both Protocols)
```php
// Morning IN: 04:00-09:30
if ($hour >= 4 && $hour < 9 || ($hour == 9 && $minute <= 30))
    $status = 'IN';

// Afternoon OUT: 15:00-21:00
if ($hour >= 15 && $hour <= 21)
    $status = 'OUT';

// Midday OUT/IN: 12:00-12:59 (OUT), 12:10-13:00 (IN after OUT)
// Tracked per employee/day with $middayTracker
```

## Testing

### 1. Verify Routes
```bash
php artisan route:list --path=api/attendance/push
```

### 2. Test Health Endpoint
```bash
curl http://localhost:8000/api/attendance/push/health
```
Expected:
```json
{
  "status": "online",
  "message": "Attendance push endpoint is operational",
  "timestamp": "2025-12-13 06:30:00"
}
```

### 3. Simulate Device Push
```bash
curl -X POST http://localhost:8000/api/attendance/push \
  -H "Content-Type: application/json" \
  -d '{
    "device": {"sn": "BQWD123456789", "stamp": 1702445678},
    "records": [{"pin": "1001", "time": 1702445678, "verify": 15, "status": 0}]
  }'
```

### 4. Access Settings Page
1. Log in to system
2. Click user menu → "System Settings"
3. Verify push_server_url is displayed
4. Check WL10 configuration instructions

### 5. Create ADMS Device
1. Navigate to Devices → Create Device
2. Select model: "ZKTeco WL10"
3. Enter serial number (e.g., BQWD123456789)
4. Fill other fields
5. Save device

## What Remains UNCHANGED

### DeviceController.php
- All ZKEM extraction logic intact
- `downloadDeviceLogs()` method unchanged
- Time-based status rules still applied during storage
- Store Selected/Store All functionality working

### Device Management UI
- Extract Logs button (ZKEM devices)
- Store Selected button
- Store All button (fetches up to 10,000 logs)
- Preview table
- Test Connection (ZKEM only)

### Attendance Logs
- All existing features working
- Live Monitor
- Daily Summary
- Form 48 generation
- Export functionality

## Key Differences

| Aspect | ZKEM (P160, K40, etc.) | ADMS (WL10, WL20, etc.) |
|--------|----------------------|------------------------|
| **Extraction** | Manual via UI | Automatic push |
| **Historical logs** | ✅ Can pull all | ❌ Cannot pull (push-only) |
| **Test Connection** | ✅ Works | ⚠️ Limited (not ZKEM) |
| **Store logs** | User-initiated | Automatic on push |
| **Public URL** | Not required | **Required** |
| **Serial number** | Optional | **Required** (for matching) |

## Important Notes

### For Production Deployment
1. **Public URL Required**: WL10 devices must push to publicly accessible URL (not localhost)
2. **SSL Recommended**: Use HTTPS to encrypt push data
3. **Serial Number Crucial**: Must match exactly between device and system
4. **Test Health Endpoint**: Verify accessibility before configuring devices
5. **Monitor Logs**: Check `storage/logs/laravel.log` for push activity

### For Development/Testing
1. **Local Testing**: Use ngrok or similar tunnel for localhost
   ```bash
   ngrok http 8000
   ```
   Use ngrok URL as push endpoint

2. **Manual Testing**: Use curl to simulate device push
3. **Check Logs**: Monitor Laravel logs for debugging

### Serial Number Matching
The system matches incoming push logs to devices using:
1. **Primary**: Serial number (if provided in payload and exists in DB)
2. **Fallback**: IP address (if serial number not found)

**Best Practice**: Always configure serial_number in device record for reliable matching.

## Migration Status

- ✅ `create_system_settings_table` - Migrated successfully
- ✅ `devices.serial_number` - Column already exists (no migration needed)

## Files Modified/Created

### Created Files
1. `app/Models/SystemSetting.php`
2. `app/Http/Controllers/AttendancePushController.php`
3. `app/Http/Controllers/SystemSettingController.php`
4. `database/migrations/2025_12_13_055941_create_system_settings_table.php`
5. `resources/views/settings/index.blade.php`
6. `DEVICE_PUSH_SETUP.md`
7. `PUSH_PROTOCOL_SUMMARY.md` (this file)

### Modified Files
1. `routes/api.php` - Added push endpoints
2. `routes/web.php` - Added settings routes
3. `bootstrap/app.php` - Enabled API routes
4. `resources/views/devices/form.blade.php` - Added serial_number note
5. `resources/views/layouts/navigation.blade.php` - Added settings link

### Unchanged Files (ZKEM Logic Intact)
1. `app/Http/Controllers/DeviceController.php` - All extraction logic preserved
2. `resources/views/devices/show.blade.php` - Extract/Store buttons working
3. All other attendance-related controllers and views

## Next Steps

### For User
1. **Configure WL10 Device**:
   - Follow `DEVICE_PUSH_SETUP.md` guide
   - Set push URL to: `https://your-domain.com/api/attendance/push`
   - Record device serial number

2. **Add Device in System**:
   - Navigate to Devices → Create Device
   - Select "ZKTeco WL10" model
   - Enter serial number (crucial!)
   - Save device

3. **Monitor Push Activity**:
   - Check Settings page for push endpoint URL
   - Test health endpoint: `/api/attendance/push/health`
   - Monitor Laravel logs: `tail -f storage/logs/laravel.log`

4. **Verify Logs**:
   - Navigate to Attendance → Attendance Logs
   - Filter by WL10 device
   - Confirm logs appearing automatically

### For Development
1. **Add Authentication** (optional): Token-based auth for push endpoint
2. **IP Whitelisting** (optional): Restrict push endpoint to device IPs
3. **Rate Limiting** (optional): Prevent abuse of push endpoint
4. **Push History** (optional): Log all push attempts for debugging
5. **Device Status** (optional): Track last push timestamp per device

## Troubleshooting

### Push Logs Not Appearing
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify device serial number matches system record
3. Test health endpoint accessibility
4. Confirm device has internet connectivity
5. Check device push configuration

### Test Connection Shows "0 logs" for WL10
**This is expected!** WL10 uses ADMS protocol and cannot be pulled via ZKEM. The device only pushes data. Configure push URL instead.

### Device Serial Number Unknown
1. Access WL10 device menu
2. Navigate to System → Device Info
3. Note the serial number (usually starts with BQWD)
4. Update device record in system

---

**Implementation Date**: December 13, 2025
**Status**: ✅ Complete and Ready for Testing
**Version**: 1.0
