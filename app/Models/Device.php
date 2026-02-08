<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'name',
        'model',
        'serial_number',
        'ip_address',
        'port',
        'protocol',
        'live_sync_mode',
        'timezone',
        'location',
        'is_active',
    ];

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}

