<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{
    protected $fillable = [
        'employee_group_id',
        'key',
        'value',
        'label',
        'description',
    ];

    protected $casts = [
        'employee_group_id' => 'integer',
    ];

    /**
     * Get the employee group
     */
    public function employeeGroup()
    {
        return $this->belongsTo(EmployeeGroup::class);
    }

    /**
     * Get a setting value by key for a specific group
     */
    public static function get($key, $groupId = null, $default = null)
    {
        $query = self::where('key', $key);
        if ($groupId) {
            $query->where('employee_group_id', $groupId);
        }
        $setting = $query->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value by key for a specific group
     */
    public static function set($key, $value, $groupId = null)
    {
        return self::updateOrCreate(
            ['key' => $key, 'employee_group_id' => $groupId],
            ['value' => $value]
        );
    }
}
