<?php

namespace App\Http\Controllers;

use App\Models\ReportSetting;
use App\Models\EmployeeGroup;
use Illuminate\Http\Request;

class ReportSettingController extends Controller
{
    /**
     * Display report settings page
     */
    public function index(Request $request)
    {
        $groups = EmployeeGroup::where('is_active', true)->orderBy('name')->get();
        $selectedGroupId = $request->input('group_id', $groups->first()?->id);

        if ($selectedGroupId) {
            $rangeSettings = [
                'am_arrival_start' => ['label' => 'AM Arrival Start Time', 'description' => 'Start of AM arrival window', 'default' => '08:00'],
                'am_arrival_end' => ['label' => 'AM Arrival End Time', 'description' => 'End of AM arrival window', 'default' => '08:00'],
                'am_departure_start' => ['label' => 'AM Departure Start Time', 'description' => 'Start of AM departure window', 'default' => '12:00'],
                'am_departure_end' => ['label' => 'AM Departure End Time', 'description' => 'End of AM departure window', 'default' => '12:00'],
                'pm_arrival_start' => ['label' => 'PM Arrival Start Time', 'description' => 'Start of PM arrival window', 'default' => '13:00'],
                'pm_arrival_end' => ['label' => 'PM Arrival End Time', 'description' => 'End of PM arrival window', 'default' => '14:00'],
                'pm_departure_start' => ['label' => 'PM Departure Start Time', 'description' => 'Start of PM departure window', 'default' => '17:00'],
                'pm_departure_end' => ['label' => 'PM Departure End Time', 'description' => 'End of PM departure window', 'default' => '17:00'],
            ];

            foreach ($rangeSettings as $key => $meta) {
                ReportSetting::updateOrCreate(
                    ['employee_group_id' => $selectedGroupId, 'key' => $key],
                    [
                        'label' => $meta['label'],
                        'description' => $meta['description'],
                        'value' => ReportSetting::get($key, $selectedGroupId, $meta['default']),
                    ]
                );
            }

            ReportSetting::updateOrCreate(
                ['employee_group_id' => $selectedGroupId, 'key' => 'official_arrival'],
                [
                    'label' => 'Official Arrival',
                    'description' => 'Official arrival time shown on reports',
                    'value' => ReportSetting::get('official_arrival', $selectedGroupId, '08:00'),
                ]
            );

            ReportSetting::updateOrCreate(
                ['employee_group_id' => $selectedGroupId, 'key' => 'official_departure'],
                [
                    'label' => 'Official Departure',
                    'description' => 'Official departure time shown on reports',
                    'value' => ReportSetting::get('official_departure', $selectedGroupId, '17:00'),
                ]
            );
        }
        
        $settings = ReportSetting::where('employee_group_id', $selectedGroupId)
            ->orderBy('id')
            ->get()
            ->groupBy(function($item) {
                if (str_contains($item->key, 'am_')) {
                    return 'AM';
                } elseif (str_contains($item->key, 'pm_')) {
                    return 'PM';
                } else {
                    return 'General';
                }
            });

        return view('report-settings.index', compact('settings', 'groups', 'selectedGroupId'));
    }

    /**
     * Update report settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:employee_groups,id',
            'settings' => 'required|array',
            'settings.*' => 'required|string',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            ReportSetting::set($key, $value, $validated['group_id']);
        }

        return redirect()->route('report-settings.index', ['group_id' => $validated['group_id']])
            ->with('success', 'Report settings updated successfully.');
    }
}
