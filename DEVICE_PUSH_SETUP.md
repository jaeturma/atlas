# Push Protocol Device Setup Guide

## For ADMS/Cloud Devices (WL10, WL20, etc.)

### Overview
ADMS/Cloud devices like the ZKTeco WL10 series use a **Push Protocol** instead of the traditional ZKEM pull-based protocol. This means:
- The device **pushes** attendance logs to your server via HTTP POST requests
- The server **cannot pull** historical logs from the device
- You must configure the device with your server's publicly accessible URL

### Prerequisites
1. Your server must have a **publicly accessible URL** (not localhost)
2. The push endpoint must be accessible on **HTTP or HTTPS**
3. The device must have internet connectivity
4. Device serial number must be recorded in the system

---

## Step 1: Get Your Push Endpoint URL

1. Log in to your Employee Management System
2. Navigate to **Settings** (User Menu → System Settings)
3. Under **Devices Settings**, find the **Push Server URL**
4. Your push endpoint will be:
   ```
   https://your-domain.com/api/attendance/push
   ```

### Test Endpoint Health
Before configuring devices, verify the endpoint is accessible:
```bash
curl https://your-domain.com/api/attendance/push/health
```

Expected response:
```json
{
  "status": "online",
  "message": "Attendance push endpoint is operational",
  "timestamp": "2025-12-13 06:15:30"
}
```

---

## Step 2: Configure WL10 Device

### Access Device Settings
1. Power on the WL10 device
2. Access the device admin menu (usually Menu → Admin → Password)
3. Navigate to **Communication** or **Network Settings**

### Configure Push Server
1. Find **Push Server** or **ADMS Server** settings
2. Enter your push endpoint URL:
   ```
   https://your-domain.com/api/attendance/push
   ```
3. Set push interval (recommended: 1-5 minutes for real-time sync)
4. Enable push mode
5. Save settings and restart device

### Alternative: Cloud Configuration
Some WL10 devices support cloud configuration:
1. Log in to ZKTeco cloud portal
2. Add your device using serial number
3. Configure push URL in device settings
4. Apply configuration remotely

---

## Step 3: Add Device in System

1. Navigate to **Devices** → **Create Device**
2. Fill in device information:
   - **Name**: e.g., "Main Entrance WL10"
   - **Model**: Select "ZKTeco WL10"
   - **Serial Number**: **REQUIRED** - Enter device serial number (found in device settings)
   - **IP Address**: Device IP (for reference only)
   - **Port**: 4370 (default for WL10)
   - **Location**: Physical location

3. **Important**: The serial number is crucial for matching incoming push logs to the correct device

---

## Step 4: Verify Push Data Reception

### Check Logs
Monitor your Laravel logs for incoming push data:
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
```
[2025-12-13 06:20:15] local.INFO: Attendance push received from IP: 192.168.1.100
[2025-12-13 06:20:15] local.INFO: Payload: {...}
[2025-12-13 06:20:15] local.INFO: Matched device: Main Entrance WL10 (ID: 5)
[2025-12-13 06:20:15] local.INFO: Successfully stored 3 attendance logs
```

### Check Attendance Logs
1. Navigate to **Attendance** → **Attendance Logs**
2. Filter by device: Select your WL10 device
3. Verify logs are appearing automatically

---

## Supported Payload Formats

The push endpoint supports multiple payload formats:

### Format 1: ZKTeco ADMS Format
```json
{
  "device": {
    "sn": "BQWD123456789",
    "stamp": 1702445678
  },
  "records": [
    {
      "pin": "1001",
      "time": 1702445678,
      "verify": 15,
      "status": 0
    }
  ]
}
```

### Format 2: Cloud API Format
```json
{
  "serialNumber": "BQWD123456789",
  "deviceIP": "192.168.1.100",
  "data": [
    {
      "userId": "1001",
      "timestamp": "2023-12-13T08:30:00+08:00",
      "verifyType": 1,
      "status": 0
    }
  ]
}
```

### Format 3: Generic Format
```json
{
  "serial_number": "BQWD123456789",
  "logs": [
    {
      "badge_number": "1001",
      "datetime": "2023-12-13 08:30:00",
      "status": "IN"
    }
  ]
}
```

---

## Time-Based Status Rules

The system automatically assigns status based on log time:

| Time Range | Status | Notes |
|------------|--------|-------|
| 04:00 - 09:30 AM | **IN** | Morning arrival |
| 12:00 - 12:59 PM | **OUT** (first) | Lunch break start |
| 12:10 - 01:00 PM | **IN** (second) | Return from lunch (after OUT) |
| 03:00 - 09:00 PM | **OUT** | End of day departure |

**Midday Sequencing**: The system tracks each employee's logs per day. The first punch between 12:00-12:59 PM is marked as OUT, and the second punch between 12:10-1:00 PM is marked as IN.

---

## Troubleshooting

### Device Not Pushing Logs
1. **Check device connectivity**: Ensure device has internet access
2. **Verify push URL**: Must be publicly accessible (use online URL checker)
3. **Check device settings**: Confirm push mode is enabled
4. **Review device logs**: Check device internal logs for push errors
5. **Firewall**: Ensure no firewall blocking outbound connections from device

### Logs Not Appearing in System
1. **Serial number mismatch**: Device serial must match system record exactly
2. **Check Laravel logs**: `tail -f storage/logs/laravel.log`
3. **Verify endpoint**: Test health endpoint: `/api/attendance/push/health`
4. **Employee badge mismatch**: Ensure badge numbers in device match employee records

### Testing Push Manually
Use curl to simulate a device push:
```bash
curl -X POST https://your-domain.com/api/attendance/push \
  -H "Content-Type: application/json" \
  -d '{
    "device": {
      "sn": "BQWD123456789",
      "stamp": 1702445678
    },
    "records": [
      {
        "pin": "1001",
        "time": 1702445678,
        "verify": 15,
        "status": 0
      }
    ]
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Logs processed successfully",
  "logs_stored": 1,
  "logs_skipped": 0
}
```

---

## Security Considerations

1. **HTTPS Recommended**: Use HTTPS for production to encrypt push data
2. **IP Whitelisting**: Consider restricting /api/attendance/push to known device IPs
3. **Rate Limiting**: Monitor push frequency to prevent abuse
4. **Authentication**: Future versions may support token-based authentication

---

## Differences: ZKEM vs ADMS Protocol

| Feature | ZKEM (P160, K40, etc.) | ADMS (WL10, WL20, etc.) |
|---------|----------------------|----------------------|
| **Communication** | Server pulls from device | Device pushes to server |
| **Historical logs** | ✅ Can extract all | ❌ Cannot extract (push-only) |
| **Real-time sync** | Manual/scheduled | Automatic push |
| **Public URL required** | ❌ No | ✅ Yes |
| **Port** | 4370 (TCP) | 4370 (ADMS) + HTTP |
| **Test Connection** | ✅ Supported | ⚠️ Limited (health check only) |

---

## Support

For additional help:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review ZKTeco device manual for push configuration
3. Contact system administrator
4. GitHub Issues: [Your Repository URL]

---

**Last Updated**: December 13, 2025
**Version**: 1.0
