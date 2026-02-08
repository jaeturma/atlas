<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeGroup extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get employees in this group
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get report settings for this group
     */
    public function reportSettings()
    {
        return $this->hasMany(ReportSetting::class);
    }
}
