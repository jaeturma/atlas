<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'leave_period',
        'number_of_days',
        'reason',
        'status',
        'attachment',
        'approved_attachment',
        'approved_by_user_id',
        'approved_at',
        'approved_pnpki_full_name',
        'approved_pnpki_serial_number',
        'approved_pnpki_certificate_path',
        'validated_by_user_id',
        'validated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'number_of_days' => 'decimal:2',
        'approved_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }
}
