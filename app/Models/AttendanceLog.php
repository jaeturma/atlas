<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'device_id',
        'badge_number',
        'employee_id',
        'log_datetime',
        'status',
        'punch_type',
    ];

    protected $casts = [
        'log_datetime' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            unset($model->attributes['log_date']);
            unset($model->attributes['log_time']);
        });
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'badge_number', 'badge_number');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('log_date', [$startDate, $endDate]);
    }

    public function scopeForDevice($query, $deviceId)
    {
        return $query->where('device_id', $deviceId);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'In' => 'badge-success',
            'Out' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getPunchTypeBadge()
    {
        return match($this->punch_type) {
            'Fingerprint' => 'badge-primary',
            'Card' => 'badge-info',
            'Password' => 'badge-warning',
            default => 'badge-secondary',
        };
    }
}
