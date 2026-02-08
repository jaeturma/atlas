<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'end_date',
        'name',
        'description',
        'type',
        'memorandum_attachment',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the activities for this holiday
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
