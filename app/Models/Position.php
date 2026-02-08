<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        'description',
        'daily_rate',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_rate' => 'decimal:2',
    ];

    /**
     * Get the employees for this position
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope to get only active positions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
