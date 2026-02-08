<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Model
{
    protected $fillable = [
        'badge_number',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'birthdate',
        'gender',
        'civil_status',
        'email',
        'position_id',
        'department_id',
        'employee_group_id',
        'dtr_signatory_department_id',
        'is_active',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'position_id' => 'integer',
        'department_id' => 'integer',
        'employee_group_id' => 'integer',
        'dtr_signatory_department_id' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the position of the employee
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Stored face templates for this employee.
     */
    public function faceTemplates()
    {
        return $this->hasMany(FaceTemplate::class);
    }

    /**
     * Get the department of the employee
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Department selected as DTR signatory.
     */
    public function dtrSignatoryDepartment()
    {
        return $this->belongsTo(Department::class, 'dtr_signatory_department_id');
    }

    /**
     * Get the employee group
     */
    public function employeeGroup()
    {
        return $this->belongsTo(EmployeeGroup::class);
    }

    /**
     * Get the attendance logs for this employee
     */
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'badge_number', 'badge_number');
    }

    /**
     * Get the full name of the employee
     */
    public function getFullName()
    {
        $name = trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
        if ($this->suffix) {
            $name .= " {$this->suffix}";
        }
        return $name;
    }

    /**
     * Get the activities for this employee
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Leave balances for this employee
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Leave requests for this employee
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
