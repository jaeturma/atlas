<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceTemplate extends Model
{
    protected $fillable = [
        'employee_id',
        'image_path',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
