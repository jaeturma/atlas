<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'end_date',
        'activity_type',
        'description',
        'holiday_id',
        'memorandum_link',
        'certificate_attachment',
        'att_attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the holiday
     */
    public function holiday()
    {
        return $this->belongsTo(Holiday::class);
    }
}
