<!DOCTYPE html>
<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Microsoft Word">
    <title>Final Daily Time Record</title>
    <style>
        @page { size: 21cm 29.7cm; margin: 0.25in; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; text-align: center; }
        .title { font-size: 12px; font-weight: bold; }
        .subtitle { font-size: 12px; font-weight: bold; }
        .name { font-size: 14px; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; display: inline-block; max-width: 80%; }
        .small { font-size: 9px; }
        .header-cell { font-size: 11px; font-weight: bold; padding: 0.25px 0; background: #e6e6e6; }
        .sub-header { font-size: 9px; padding: 0.25px 0; background: #e6e6e6; }
        .day-cell { font-size: 11px; font-weight: bold; padding: 0.25px 0; }
        .time-cell { font-size: 13px; padding: 0.25px 0; }
        .note { font-size: 10px; font-style: italic; }
        .row-pad { padding: 0.25px 0; }
        .two-col { width: 100%; }
        .copy { width: 49%; display: inline-block; vertical-align: top; }
        .copy + .copy { margin-left: 2%; }
    </style>
</head>
<body>
    <table class="two-col" cellspacing="0" cellpadding="0" style="width: 100%; table-layout: fixed;">
        <tr>
        @for($copy = 1; $copy <= 2; $copy++)
            <td style="width: 50%; vertical-align: top; padding-right: {{ $copy === 1 ? '6px' : '0' }}; padding-left: {{ $copy === 2 ? '6px' : '0' }};">
                <div style="display:flex; justify-content:space-between; align-items:baseline;">
                    <div class="title">CSC Form No. 48</div>
                    <div class="subtitle">DAILY TIME RECORD</div>
                </div>

                <div style="margin-top: 6px; text-align:center;">
                    @php
                        $middleInitial = $employee->middle_name ? strtoupper(substr($employee->middle_name, 0, 1)) . '.' : '';
                        $suffix = $employee->suffix ? ' ' . $employee->suffix : '';
                        $fullName = trim("{$employee->first_name} {$middleInitial} {$employee->last_name}{$suffix}");
                    @endphp
                    <span class="name">{{ $fullName }}</span>
                </div>

                <div style="margin-top: 6px; font-size: 11px;">
                    <div>
                        <span><strong>For the month of</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $startDate->format('F Y') }}</span>
                        <span style="margin-left: 10px;"><strong>ID No.</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $employee->badge_number }}</span>
                    </div>
                    <div style="margin-top: 4px;">
                        <span><strong>Official Arrival:</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $officialArrival }}</span>
                        <span style="margin-left: 10px;"><strong>Regular Days:</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $regularDays }}</span>
                    </div>
                    <div style="margin-top: 4px;">
                        <span style="padding-left: 18px;"><strong>Departure:</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $officialDeparture }}</span>
                        <span style="margin-left: 10px;"><strong>Saturdays:</strong></span>
                        <span style="border-bottom:1px solid #000; padding: 0 4px;">{{ $saturdays }}</span>
                    </div>
                </div>

                <table style="margin-top: 8px;">
                    <thead>
                        <tr>
                            <th rowspan="2" class="header-cell" style="width:8%;">Day</th>
                            <th colspan="2" class="header-cell" style="width:22%;">A.M.</th>
                            <th colspan="2" class="header-cell" style="width:22%;">P.M.</th>
                            <th colspan="2" class="header-cell" style="width:20%;">Late/Undertime</th>
                        </tr>
                        <tr>
                            <th class="sub-header">Arrival</th>
                            <th class="sub-header">Departure</th>
                            <th class="sub-header">Arrival</th>
                            <th class="sub-header">Departure</th>
                            <th class="sub-header">Hour(s)</th>
                            <th class="sub-header">Min(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $skipDates = [];
                            foreach ($activitiesByDate as $dateStr => $activitiesOnDate) {
                                foreach ($activitiesOnDate as $act) {
                                    if ($act->date->format('Y-m-d') === $dateStr && $act->end_date && $act->end_date->format('Y-m-d') !== $dateStr) {
                                        $tempDate = $act->date->copy();
                                        while ($tempDate->lt($act->end_date)) {
                                            $tempDate->addDay();
                                            $tempDateStr = $tempDate->format('Y-m-d');
                                            $tempDayNum = (int) $tempDate->format('N');
                                            $tempHoliday = $holidays->get($tempDateStr);
                                            $tempHasLeave = !empty($leavesByDate[$tempDateStr] ?? []);
                                            $isRegularDay = $tempDayNum <= 5 && !$tempHoliday;
                                            if ($isRegularDay && !$tempHasLeave) {
                                                $skipDates[] = $tempDateStr;
                                            }
                                        }
                                    }
                                }
                            }
                        @endphp
                        @foreach($dates as $detail)
                            @php
                                $dateStr = $detail->format('Y-m-d');
                                $isSkippedByRowspan = in_array($dateStr, $skipDates);
                                $dayDetails = $dailyDetails[$dateStr] ?? null;
                                $holiday = $holidays->get($dateStr);
                                $activitiesForDate = $activitiesByDate[$dateStr] ?? [];
                                $leavesForDate = $leavesByDate[$dateStr] ?? [];
                                $activity = !empty($activitiesForDate) ? $activitiesForDate[0] : null;
                                $leave = !empty($leavesForDate) ? $leavesForDate[0] : null;
                                $leavePeriod = $leave?->leave_period ?? 'full';
                                $hasLeave = !empty($leave);
                                $leaveLabel = $leave
                                    ? (($leave->leaveType?->name ?? 'Leave')
                                        . ($leavePeriod !== 'full' ? ' (' . strtoupper($leavePeriod === 'morning' ? 'AM' : 'PM') . ')' : '')
                                        . (($leave->status ?? '') !== 'Approved' ? ' (P)' : ''))
                                    : null;
                                $hasLogs = $dayDetails['has_logs'] ?? false;
                                $dayName = $detail->format('l');
                                $dayNum = (int)$detail->format('N');
                                $dayDisplay = $detail->format('j');
                                $dayColor = $dayNum == 6 ? '#1e40af' : ($dayNum == 7 ? '#991b1b' : '#000');
                                $isWeekend = $dayNum == 6 || $dayNum == 7;
                                $isRegularDay = !$isWeekend && !$holiday;

                                if ($hasLeave) {
                                    $isSkippedByRowspan = false;
                                    $activity = null;
                                }

                                $isActivityStart = false;
                                $activitySpan = 1;
                                if ($activity && $isRegularDay) {
                                    $activityEnd = $activity->end_date ?? $activity->date;
                                    $prevDate = $detail->copy()->subDay();
                                    $prevInRange = $prevDate->gte($activity->date) && $prevDate->lte($activityEnd);
                                    $prevDateStr = $prevDate->format('Y-m-d');
                                    $prevDayNum = (int) $prevDate->format('N');
                                    $prevHoliday = $holidays->get($prevDateStr);
                                    $prevIsRegular = $prevInRange && ($prevDayNum <= 5) && !$prevHoliday;

                                    $isActivityStart = !$prevIsRegular;

                                    if ($isActivityStart) {
                                        $activitySpan = 0;
                                        $tempDate = $detail->copy();
                                        while ($tempDate->lte($activityEnd)) {
                                            $tempDateStr = $tempDate->format('Y-m-d');
                                            $tempDayNum = (int) $tempDate->format('N');
                                            $tempHoliday = $holidays->get($tempDateStr);
                                            $tempHasLeave = !empty($leavesByDate[$tempDateStr] ?? []);
                                            $tempIsRegular = $tempDayNum <= 5 && !$tempHoliday;
                                            if (!$tempIsRegular || $tempHasLeave) {
                                                break;
                                            }
                                            $activitySpan++;
                                            $tempDate->addDay();
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="day-cell" style="color: {{ $dayColor }};">{{ $dayDisplay }}</td>
                                @if ($isSkippedByRowspan)
                                    <td class="small row-pad">&nbsp;</td>
                                    <td class="small row-pad">&nbsp;</td>
                                @elseif ($isWeekend)
                                    @if ($hasLogs)
                                        <td class="time-cell">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                        <td class="time-cell">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                        <td class="time-cell">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                        <td class="time-cell">{{ $dayDetails['pm_departure'] ?? '' }}</td>
                                    @else
                                        <td colspan="4" class="row-pad" style="font-size: 13px; font-weight: bold; color: {{ $dayColor }};">{{ strtoupper($dayName) }}</td>
                                    @endif
                                @elseif ($holiday)
                                    <td colspan="4" class="row-pad" style="font-size: 13px; font-weight: bold;">{{ $holiday->name }}</td>
                                @elseif ($hasLeave && $leavePeriod === 'full')
                                    <td colspan="4" class="row-pad" style="font-size: 13px; font-weight: bold;">{{ $leaveLabel }}</td>
                                @elseif ($hasLeave && $leavePeriod === 'morning')
                                    <td colspan="2" class="row-pad" style="font-size: 12px; font-weight: bold;">{{ $leaveLabel }}</td>
                                    <td class="time-cell">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                    <td class="time-cell">{{ $dayDetails['pm_departure'] ?? '' }}</td>
                                @elseif ($hasLeave && $leavePeriod === 'afternoon')
                                    <td class="time-cell">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                    <td class="time-cell">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                    <td colspan="2" class="row-pad" style="font-size: 12px; font-weight: bold;">{{ $leaveLabel }}</td>
                                @elseif ($isActivityStart && $activity && $activitySpan > 1)
                                    <td colspan="4" rowspan="{{ $activitySpan }}" class="row-pad" style="font-size: 13px; vertical-align: middle;">
                                        {{ $activity->activity_type }}
                                    </td>
                                @elseif ($isActivityStart && $activity)
                                    <td colspan="4" class="row-pad" style="font-size: 13px;">{{ $activity->activity_type }}</td>
                                @else
                                    <td class="time-cell">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                    <td class="time-cell">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                    <td class="time-cell">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                    <td class="time-cell">{{ $dayDetails['pm_departure'] ?? '' }}</td>
                                @endif
                                @if (!$isSkippedByRowspan)
                                    @php
                                        $hasLogs = $dayDetails['has_logs'] ?? false;
                                        $ignoreLateUndertime = $dayDetails['ignore_late_undertime'] ?? false;
                                        $lateMinutes = ($hasLogs && !$ignoreLateUndertime) ? (int)($dayDetails['late_total_minutes'] ?? 0) : null;
                                        $workedMinutes = ($hasLogs && !$ignoreLateUndertime) ? max(0, 480 - $lateMinutes) : null;
                                        $hoursDisplay = ($hasLogs && !$ignoreLateUndertime && $workedMinutes > 0)
                                            ? sprintf('%02d:%02d', intdiv($workedMinutes, 60), $workedMinutes % 60)
                                            : '';
                                        $minsDisplay = ($hasLogs && !$ignoreLateUndertime && $workedMinutes > 0) ? $workedMinutes : '';
                                    @endphp
                                    <td class="small row-pad">{{ $hoursDisplay }}</td>
                                    <td class="small row-pad">{{ $minsDisplay }}</td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="row-pad" style="font-weight: bold;">
                            <td colspan="5" style="text-align:right;">TOTAL</td>
                            @php
                                $totalWorkedMinutes = 0;
                                $totalLateMinutes = 0;
                                foreach ($dailyDetails as $detail) {
                                    if (!empty($detail['has_logs']) && empty($detail['ignore_late_undertime'])) {
                                        $late = (int)($detail['late_total_minutes'] ?? 0);
                                        $totalLateMinutes += $late;
                                        $totalWorkedMinutes += max(0, 480 - $late);
                                    }
                                }
                                $totalHoursDisplay = ($totalWorkedMinutes > 0 || $totalLateMinutes > 0)
                                    ? sprintf('%02d:%02d', intdiv($totalWorkedMinutes, 60), $totalWorkedMinutes % 60)
                                    : '';
                                $totalLateMinutesDisplay = $totalWorkedMinutes > 0 ? $totalWorkedMinutes : '';
                            @endphp
                            <td class="small row-pad">{{ $totalHoursDisplay }}</td>
                            <td class="small row-pad">{{ $totalLateMinutesDisplay }}</td>
                        </tr>
                    </tbody>
                </table>

                <div style="margin-top: 10px;">
                    <p class="note">I CERTIFY on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</p>
                    <div style="margin-top: 8px; text-align:center;">
                        <div style="border-bottom:1px solid #000; width: 48%; margin: 0 auto;"></div>
                        <div class="small" style="font-weight: bold;">Signature</div>
                    </div>
                </div>

                @php
                    $signatoryDepartment = $employee->dtrSignatoryDepartment ?? $employee->department;
                    $signatoryName = $signatoryDepartment?->head_name ?? 'Head of Office';
                    $signatoryTitle = $signatoryDepartment?->head_position_title ?? '';
                @endphp
                <div style="margin-top: 8px;">
                    <p class="note">VERIFIED as to the prescribed office hours:</p>
                    <div style="margin-top: 6px; text-align:center;">
                        <div style="height: 12px; margin: 8px 20%;"></div>
                        <div style="font-size: 12px; font-weight: bold; text-transform: uppercase;">{{ $signatoryName }}</div>
                        <div class="small">{{ $signatoryTitle }}</div>
                    </div>
                </div>
            </td>
        @endfor
        </tr>
    </table>
</body>
</html>
```
</attachment>

</attachments>
<context>
The current date is January 30, 2026.
Terminals:
Terminal: powershell

</context>
<editorContext>
The user's current file is d:\lara\www\atlas\resources\views\attendance-logs\final-form.blade.php. The current selection is from line 1 to line 353.
</editorContext>
<reminderInstructions>
You are an agent - you must keep going until the user's query is completely resolved, before ending your turn and yielding back to the user. ONLY terminate your turn when you are sure that the problem is solved, or you absolutely cannot continue.
You take action when possible- the user is expecting YOU to take action and go to work for them. Don't ask unnecessary questions about the details if you can simply DO something useful instead.

</reminderInstructions>
<userRequest>
In final Daily Time Record, replace download PDF with download as Word document, fit to A4.
</userRequest>