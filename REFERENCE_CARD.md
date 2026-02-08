# Multi-Protocol Implementation - Reference Card

## Quick Reference

### Protocol Selection
```
WL10/20/30/40/50 → ADMS (auto-detected)
K40/50/60 → ZKEM (auto-detected)
Unknown → Try ADMS, fallback to ZKEM
```

### One-Line Migration
```bash
php artisan migrate
```

### Force Protocol
```bash
php artisan tinker
>>> Device::find(1)->update(['protocol' => 'adms']);
>>> Device::find(2)->update(['protocol' => 'zkem']);
```

---

## Protocol Comparison

| Feature | ADMS | ZKEM |
|---------|------|------|
| **Devices** | WL10, WL20, WL30, WL40, WL50 | K40, K50, K60, U100, U200, iClock |
| **Type** | TCP | UDP |
| **Header** | hPUSH | ZKEM Protocol |
| **Features** | Real-time push, modern | Basic attendance |
| **Port** | 4370 | 4370 |
| **Status** | New implementation | Existing SDK |

---

## API Changes Summary

### getStatus()
```json
{
  "connection": {
    "protocol_type": "adms"
  }
}
```

### getDeviceTime()
```json
{
  "protocol": "adms",
  "device_time": "2025-12-08 15:30:45"
}
```

### syncTime()
```json
{
  "protocol": "adms",
  "message": "Device time synchronized using adms protocol"
}
```

---

## File Map

```
app/Services/
├── ADMSProtocol.php (NEW)
├── DeviceProtocolManager.php (NEW)
└── ZKTecoWrapper.php (existing)

app/Models/
└── Device.php (updated: +protocol)

app/Http/Controllers/
└── DeviceController.php (updated: 3 methods)

database/migrations/
└── 2025_12_08_add_protocol_to_devices.php (NEW)
```

---

## Detection Flow

```
Request → Check explicit protocol
   ↓
If 'auto' → Check device model pattern
   ↓
Match found? → Use detected protocol
   ↓
No match? → Try ADMS first
   ↓
Connection success? → Done
   ↓
ADMS failed? → Fallback to ZKEM
   ↓
Both failed? → Return error
```

---

## Common Commands

```bash
# Run migration
php artisan migrate

# Check logs for fallback events
tail -f storage/logs/laravel.log | grep "Protocol"

# Open Tinker for device config
php artisan tinker

# View device protocols
>>> Device::pluck('protocol', 'name');
```

---

## Troubleshooting Quick Tips

| Issue | Try |
|-------|-----|
| Device unreachable | Check IP/port, ping device |
| Protocol failed | Check device model, verify firmware |
| Fallback message | Normal behavior, both protocols working |
| ADMS not working | Force ZKEM with `protocol='zkem'` |
| Time mismatch | Check device timezone, verify sync |

---

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Add WL10 test device
- [ ] Test connection to WL10
- [ ] Verify protocol='adms' in response
- [ ] Get device time
- [ ] Check protocol field in response
- [ ] Sync time
- [ ] Review logs for successful operation
- [ ] Test with legacy K40 if available
- [ ] Verify fallback message in logs

---

## Support Matrix

| Model | Protocol | Fallback | Status |
|-------|----------|----------|--------|
| WL10 | ADMS | ZKEM | ✅ Supported |
| WL20 | ADMS | ZKEM | ✅ Supported |
| WL30 | ADMS | ZKEM | ✅ Supported |
| WL40 | ADMS | ZKEM | ✅ Supported |
| WL50 | ADMS | ZKEM | ✅ Supported |
| K40 | ZKEM | N/A | ✅ Supported |
| K50 | ZKEM | N/A | ✅ Supported |
| K60 | ZKEM | N/A | ✅ Supported |
| U100 | ZKEM | N/A | ✅ Supported |
| U200 | ZKEM | N/A | ✅ Supported |
| iClock | ZKEM | N/A | ✅ Supported |
| Other | ADMS→ZKEM | Auto-fallback | ✅ Supported |

---

## Database Schema

**New Column: `protocol`**
- Type: string
- Default: 'auto'
- Options: 'auto', 'adms', 'zkem'
- Indexed: Yes

```sql
ALTER TABLE devices ADD COLUMN protocol VARCHAR(20) DEFAULT 'auto' AFTER port;
CREATE INDEX idx_protocol ON devices(protocol);
```

---

## Key Files Size

- ADMSProtocol.php: ~11 KB
- DeviceProtocolManager.php: ~8.6 KB
- Migration: ~1 KB
- Total new code: ~20 KB

---

## Backward Compatibility

✅ All existing routes unchanged
✅ All existing endpoints work as-is
✅ Old devices continue to work
✅ New protocol added, old retained
✅ No database breaking changes
✅ Migration is reversible
✅ Can be rolled back if needed

---

## Next Steps

1. **Run migration**: `php artisan migrate`
2. **Test device**: Click "Test Connection"
3. **Verify protocol**: Check response for `protocol` field
4. **Monitor logs**: Look for protocol detection messages
5. **Deploy**: Ready for production

---

*Last Updated: December 8, 2025*
*Multi-Protocol Support v1.0*
