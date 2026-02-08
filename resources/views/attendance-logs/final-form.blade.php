@php
    // No need to group - already grouped in controller
@endphp

<x-admin-layout>
    <div class="max-w-7xl mx-auto print:max-w-none">
        <!-- Card Container for Print Area -->
        <div class="bg-white rounded-lg shadow-lg p-6 print:shadow-none print:p-0">
            <!-- Header + Actions -->
            <div class="mb-6 flex items-start justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Final DTR</h1>
                    <p class="text-gray-500 dark:text-gray-400">With Holidays & Activities</p>
                </div>
                    <div class="flex items-center gap-3">
                    <!-- Toggle for Late/Undertime Columns -->
                    <div class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg">
                        <label for="toggleLateUndertime" class="text-sm font-medium text-gray-700">Show Late/Undertime:</label>
                        <input type="checkbox" id="toggleLateUndertime" class="w-4 h-4 text-blue-600 rounded" checked onchange="toggleLateUndertimeColumns()">
                    </div>
                    <button onclick="history.back()" class="px-4 py-2 bg-gray-600 text-white rounded-[2px] hover:bg-gray-700 inline-flex items-center gap-2 text-sm font-semibold">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back</span>
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-[2px] hover:bg-blue-700 inline-flex items-center gap-2 text-sm font-semibold">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                    <button onclick="printWithAttachments()" class="px-4 py-2 bg-green-700 text-green-50 rounded-[2px] hover:bg-green-800 inline-flex items-center gap-2 text-sm font-semibold" style="background-color: #15803d; color: #f0fdf4;">
                        <i class="fas fa-print"></i>
                        <span>Print /Att</span>
                    </button>
                    <form method="POST" action="{{ route('attendance-logs.final-form-download-acc-report-word', $employee->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-[2px] hover:bg-amber-700 inline-flex items-center gap-2 text-sm font-semibold" title="Acc Report" aria-label="Acc Report">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Acc Report</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('attendance-logs.final-form-download-word', $employee->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                        <button type="submit" class="px-4 py-2 bg-blue-900 text-white rounded-[2px] hover:bg-blue-950 inline-flex items-center gap-2 text-sm font-semibold" title="Download Word" aria-label="Download Word">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-white text-blue-900 text-xs font-bold">W</span>
                            <span>Word</span>
                        </button>
                    </form>
                </div>
            </div>

            <script>
                function downloadPDF() {
                    // This function is no longer used; the form submission handles PDF download
                }
                
                function toggleLateUndertimeColumns() {
                    const checkbox = document.getElementById('toggleLateUndertime');
                    const values = document.querySelectorAll('.late-undertime-value');
                    values.forEach(val => {
                        val.style.display = checkbox.checked ? '' : 'none';
                    });
                }

                function printWithAttachments() {
                    const attachmentLinks = Array.from(document.querySelectorAll('.attachment-link'))
                        .map(link => link.href)
                        .filter(Boolean);

                    const openedWindows = attachmentLinks.map(url => window.open(url, '_blank'));

                    openedWindows.forEach((win) => {
                        if (!win) {
                            return;
                        }

                        const printAfterLoad = () => {
                            setTimeout(() => {
                                try {
                                    win.focus();
                                    win.print();
                                } catch (e) {
                                    // Ignore print errors (popup blockers or cross-origin issues)
                                }
                            }, 700);
                        };

                        if (win.document && win.document.readyState === 'complete') {
                            printAfterLoad();
                        } else {
                            win.addEventListener('load', printAfterLoad, { once: true });
                        }
                    });

                    setTimeout(() => {
                        window.focus();
                        window.print();
                    }, 900);
                }
            </script>

            <!-- Final DTR Document - Two Column Layout -->
            <div class="grid grid-cols-2 gap-8 print:gap-8">
            @for($copy = 1; $copy <= 2; $copy++)
            <!-- Final DTR Copy {{ $copy }} -->
            <div class="bg-white border border-gray-900 print:border-black p-2 text-[9px]" style="font-family: 'Times New Roman', Times, serif; width: 99.8%; box-sizing: border-box;">
                <!-- Form Header Row -->
                <div class="flex justify-between items-baseline mb-2" style="margin-left: -2px; margin-right: -2px;">
                    <p class="text-[12px] font-bold italic text-gray-700">CSC Form No. 48</p>
                    <h2 class="text-[12px] font-bold text-gray-900 print:text-black">DAILY TIME RECORD</h2>
                </div>

                <!-- Employee Information -->
                <div class="space-y-0 mb-2 text-[11px]">
                    <div class="text-center -mb-3 mt-5" style="font-family: 'Times New Roman', Times, serif;">
                        @php
                            $middleInitial = $employee->middle_name ? strtoupper(substr($employee->middle_name, 0, 1)) . '.' : '';
                            $suffix = $employee->suffix ? ' ' . $employee->suffix : '';
                            $fullName = trim("{$employee->first_name} {$middleInitial} {$employee->last_name}{$suffix}");
                        @endphp
                        <span class="inline-block mx-auto border-b border-gray-900 print:border-black font-bold uppercase text-center text-[14px] leading-tight" style="max-width: 80%; word-wrap: break-word; padding: 0; line-height: 1;">{{ $fullName }}</span>
                    </div>
                    <div class="py-0.5">
                        &nbsp;
                    </div>
                    <div class="flex items-center gap-2 py-3 print:gap-1">
                        <span class="font-semibold w-28 print:w-auto print:whitespace-nowrap">For the month of</span>
                        <span class="flex-initial w-28 border-b border-gray-900 print:border-black px-1 text-left uppercase print:text-[10px]">{{ $startDate->format('F Y') }}</span>
                        <span class="font-semibold ml-4 print:ml-2 print:whitespace-nowrap">ID No.</span>
                        <span class="border-b border-gray-900 print:border-black px-1 w-20 text-center print:text-[10px]">{{ $employee->badge_number }}</span>
                    </div>

                    <!-- Official Arrival/Departure & Days Info -->
                    <div class="flex items-center gap-2">
                        <span class="font-semibold w-28">Official Arrival:</span>
                            <span class="border-b border-gray-900 print:border-black px-1 w-20 text-center">{{ $officialArrival }}</span>
                        <span class="font-semibold w-28 text-right">Regular Days:</span>
                            <span class="border-b border-gray-900 print:border-black px-1 w-14 text-center">{{ $regularDays }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold w-28 pl-6">Departure:</span>
                            <span class="border-b border-gray-900 print:border-black px-1 w-20 text-center">{{ $officialDeparture }}</span>
                        <span class="font-semibold w-28 text-right">Saturdays:</span>
                            <span class="border-b border-gray-900 print:border-black px-1 w-14 text-center">{{ $saturdays }}</span>
                    </div>
                </div>

                <!-- Daily Time Record Table -->
                <div class="mb-3" style="margin-left: -8px; margin-right: -8px; margin-bottom: -9px;">
                    <table class="w-full border-collapse dtr-table" style="width: 99.8%; font-size: 10px;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 8%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px; background-color: #e6e6e6;">Day</th>
                                <th colspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 22%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px; background-color: #e6e6e6;">A.M.</th>
                                <th colspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 22%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px; background-color: #e6e6e6;">P.M.</th>
                                <th colspan="2" class="border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 20%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px; background-color: #e6e6e6;">Late/Undertime</th>
                            </tr>
                            <tr>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Arrival</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Departure</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Arrival</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Departure</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Hour(s)</th>
                                <th class="border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0; background-color: #e6e6e6;">Min(s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Pre-calculate skip dates for multi-day activities (regular weekdays only)
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
                                    
                                    // Check if this row is covered by a multi-day activity rowspan
                                    $isSkippedByRowspan = in_array($dateStr, $skipDates);
                                    
                                    $dayDetails = $dailyDetails[$dateStr] ?? null;
                                    $holiday = $holidays->get($dateStr);
                                    $activitiesForDate = $activitiesByDate[$dateStr] ?? [];
                                    $leavesForDate = $leavesByDate[$dateStr] ?? [];

                                    // Get first activity for display
                                    $activity = !empty($activitiesForDate) ? $activitiesForDate[0] : null;
                                    $leave = !empty($leavesForDate) ? $leavesForDate[0] : null;
                                    $leavePeriod = $leave?->leave_period ?? 'full';
                                    $hasLeave = !empty($leave);
                                    $leaveLabel = $leave
                                        ? (($leave->leaveType?->name ?? 'Leave')
                                            . ($leavePeriod !== 'full' ? ' (' . strtoupper($leavePeriod === 'morning' ? 'AM' : 'PM') . ')' : '')
                                            . (($leave->status ?? '') !== 'Approved' ? ' (P)' : ''))
                                        : null;

                                    if ($hasLeave) {
                                        $isSkippedByRowspan = false;
                                        $activity = null;
                                    }

                                    $hasLogs = $dayDetails['has_logs'] ?? false;
                                    
                                    $dayName = $detail->format('l');
                                    $dayNum = (int)$detail->format('N'); // 1=Mon, 6=Sat, 7=Sun
                                    $dayColor = '';
                                    if ($dayNum == 6) { // Saturday
                                        $dayColor = 'color: #1e40af;'; // Dark Blue
                                    } elseif ($dayNum == 7) { // Sunday
                                        $dayColor = 'color: #991b1b;'; // Dark Red
                                    }
                                    
                                    $dayDisplay = $detail->format('j'); // Date number only

                                    $isWeekend = $dayNum == 6 || $dayNum == 7;
                                    $isRegularDay = !$isWeekend && !$holiday;

                                    // Determine if this date should start an activity span (regular weekdays only)
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
                                    <td class="border-r border-b border-gray-900 print:border-black text-center font-semibold dtr-day-cell" style="width: 8%; font-size: 11px; padding: 0.25px 0; {{ $dayColor }}">{{ $dayDisplay }}</td>
                                    @if ($isSkippedByRowspan)
                                        <!-- Row covered by rowspan from previous activity - render day cell and time cells only -->
                                        <!-- Columns 2-5 are covered by rowspan, so don't render them -->
                                        <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 8px; padding: 0.25px 2px;">&nbsp;</td>
                                        <td class="border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 8px; padding: 0.25px 2px;">&nbsp;</td>
                                    @elseif ($isWeekend)
                                        @if ($hasLogs)
                                            <!-- Weekend with logs: show times -->
                                            <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                            <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                            <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                            <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['pm_departure'] ?? '' }}</td>
                                        @else
                                            <!-- Saturday/Sunday row: span columns 2-5 -->
                                            <td colspan="4" class="border-r border-b border-gray-900 print:border-black text-center font-bold" style="font-size: 13px; padding: 0.25px 0; color: {{ $dayNum == 6 ? '#1e40af' : '#991b1b' }};">{{ strtoupper($dayName) }}</td>
                                        @endif
                                    @elseif ($holiday)
                                        <!-- Holiday row: span columns 2-5 -->
                                        <td colspan="4" class="border-r border-b border-gray-900 print:border-black text-center font-bold" style="font-size: 13px; padding: 0.25px 0; color: #000;">
                                            @if (!empty($holiday->memorandum_attachment))
                                                <a href="{{ $holiday->memorandum_attachment }}" target="_blank" class="text-gray-900 hover:text-blue-600 attachment-link">
                                                    {{ $holiday->name }}
                                                </a>
                                            @else
                                                {{ $holiday->name }}
                                            @endif
                                        </td>
                                    @elseif ($hasLeave && $leavePeriod === 'full')
                                        <!-- Full-day Leave row: span columns 2-5 -->
                                        <td colspan="4" class="border-r border-b border-gray-900 print:border-black text-center font-bold" style="font-size: 13px; padding: 0.25px 0; color: #000;">
                                            {{ $leaveLabel }}
                                        </td>
                                    @elseif ($hasLeave && $leavePeriod === 'morning')
                                        <!-- Half-day Leave (AM): show leave in AM, logs in PM -->
                                        <td colspan="2" class="border-r border-b border-gray-900 print:border-black text-center font-bold" style="font-size: 12px; padding: 0.25px 0; color: #000;">
                                            {{ $leaveLabel }}
                                        </td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['pm_departure'] ?? '' }}</td>
                                    @elseif ($hasLeave && $leavePeriod === 'afternoon')
                                        <!-- Half-day Leave (PM): show logs in AM, leave in PM -->
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0;">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                        <td colspan="2" class="border-r border-b border-gray-900 print:border-black text-center font-bold" style="font-size: 12px; padding: 0.25px 0; color: #000;">
                                            {{ $leaveLabel }}
                                        </td>
                                    @elseif ($isActivityStart && $activity && $activitySpan > 1)
                                        <!-- Multi-day Activity row: span columns 2-5 AND multiple rows -->
                                        <td colspan="4" rowspan="{{ $activitySpan }}" class="border-r border-b border-gray-900 print:border-black text-center" style="font-size: 13px; padding: 0.25px 0; color: #000; vertical-align: middle;">
                                            <a href="{{ route('activities.show', $activity) }}" class="text-gray-900 hover:text-blue-600">
                                                {{ $activity->activity_type }}
                                            </a>
                                        </td>
                                    @elseif ($isActivityStart && $activity)
                                        <!-- Single-day Activity row: span columns 2-5 -->
                                        <td colspan="4" class="border-r border-b border-gray-900 print:border-black text-center" style="font-size: 13px; padding: 0.25px 0; color: #000;">
                                            <a href="{{ route('activities.show', $activity) }}" class="text-gray-900 hover:text-blue-600">
                                                {{ $activity->activity_type }}
                                            </a>
                                        </td>
                                    @else
                                        @php
                                            $amArrivalMinutes = $dayDetails['am_arrival_minutes'] ?? null;
                                            $amDepartureMinutes = $dayDetails['am_departure_minutes'] ?? null;
                                            $pmArrivalMinutes = $dayDetails['pm_arrival_minutes'] ?? null;
                                            $pmDepartureMinutes = $dayDetails['pm_departure_minutes'] ?? null;

                                            $amArrivalOut = $amArrivalMinutes !== null && (
                                                $amArrivalMinutes < ($timeWindows['am_arrival_start'] ?? 0) ||
                                                $amArrivalMinutes > ($timeWindows['am_arrival_end'] ?? 0)
                                            );
                                            $amDepartureOut = $amDepartureMinutes !== null && (
                                                $amDepartureMinutes < ($timeWindows['am_departure_start'] ?? 0) ||
                                                $amDepartureMinutes > ($timeWindows['am_departure_end'] ?? 0)
                                            );
                                            $pmArrivalOut = $pmArrivalMinutes !== null && (
                                                $pmArrivalMinutes < ($timeWindows['pm_arrival_start'] ?? 0) ||
                                                $pmArrivalMinutes > ($timeWindows['pm_arrival_end'] ?? 0)
                                            );
                                            $pmDepartureOut = $pmDepartureMinutes !== null && (
                                                $pmDepartureMinutes < ($timeWindows['pm_departure_start'] ?? 0) ||
                                                $pmDepartureMinutes > ($timeWindows['pm_departure_end'] ?? 0)
                                            );
                                        @endphp
                                        <!-- Regular weekday: show times -->
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0; {{ $amArrivalOut ? 'color:#991b1b;' : '' }}">{{ $dayDetails['am_arrival'] ?? '' }}</td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0; {{ $amDepartureOut ? 'color:#991b1b;' : '' }}">{{ $dayDetails['am_departure'] ?? '' }}</td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0; {{ $pmArrivalOut ? 'color:#991b1b;' : '' }}">{{ $dayDetails['pm_arrival'] ?? '' }}</td>
                                        <td class="border-r border-b border-gray-900 print:border-black text-center dtr-time-cell" style="width: 11%; font-size: 13px; padding: 0.25px 0; {{ $pmDepartureOut ? 'color:#991b1b;' : '' }}">{{ $dayDetails['pm_departure'] ?? '' }}</td>
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
                                        <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 8px; padding: 0.25px 2px;"><span class="late-undertime-value">{{ $hoursDisplay }}</span></td>
                                        <td class="border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 8px; padding: 0.25px 2px;"><span class="late-undertime-value">{{ $minsDisplay }}</span></td>
                                    @endif
                                </tr>
                            @endforeach
                            <!-- Total Row -->
                            @php
                                $totalWorkedMinutes = 0;
                                $totalLateMinutes = 0;
                                $totalLogDays = 0;
                                foreach ($dailyDetails as $detail) {
                                    if (!empty($detail['has_logs']) && empty($detail['ignore_late_undertime'])) {
                                        $totalLogDays += 1;
                                        $late = (int)($detail['late_total_minutes'] ?? 0);
                                        $totalLateMinutes += $late;
                                        $totalWorkedMinutes += max(0, 480 - $late);
                                    }
                                }
                            @endphp
                            <tr class="font-semibold">
                                <td colspan="5" class="border-r border-b border-gray-900 print:border-black px-1 py-1 text-right" style="width: 65%; font-size: 12px;">TOTAL</td>
                                @php
                                    $totalHoursDisplay = ($totalWorkedMinutes > 0 || $totalLateMinutes > 0)
                                        ? sprintf('%02d:%02d', intdiv($totalWorkedMinutes, 60), $totalWorkedMinutes % 60)
                                        : '';
                                    $totalLateMinutesDisplay = $totalWorkedMinutes > 0 ? $totalWorkedMinutes : '';
                                @endphp
                                <td class="border-r border-b border-gray-900 print:border-black py-1 text-center" style="width: 10%;"><span class="late-undertime-value">{{ $totalHoursDisplay }}</span></td>
                                <td class="border-b border-gray-900 print:border-black py-1 text-center" style="width: 10%;"><span class="late-undertime-value">{{ $totalLateMinutesDisplay }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Certification Section -->
                <div class="space-y-2 text-xs mt-3">
                    <div>
                        <p class="mb-1.5 text-[11px] italic">I CERTIFY on my honor that the above is a true and correct report of the hours of work performed, record of which was made daily at the time of arrival and departure from office.</p>
                        <div class="mt-3 text-center">
                            <div class="border-b border-gray-900 print:border-black w-48 mx-auto mb-0">&nbsp;</div>
                            <p class="font-semibold text-[11px] leading-none">Signature</p>
                        </div>
                    </div>

                    @php
                        $signatoryDepartment = $employee->dtrSignatoryDepartment ?? $employee->department;
                        $signatoryName = $signatoryDepartment?->head_name ?? 'Head of Office';
                        $signatoryTitle = $signatoryDepartment?->head_position_title ?? '';
                    @endphp

                    <div>
                        <p class="mb-1.5 text-[11px] italic">VERIFIED as to the prescribed office hours:</p>
                        <div class="mt-1 text-center">
                            <div style="height: 12px; margin: 8px 20%;">&nbsp;</div>
                            <p class="text-[12px] font-semibold uppercase leading-none" style="border: none;">{{ $signatoryName }}</p>
                            <p class="text-[11px] leading-tight">{{ $signatoryTitle }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endfor
        </div>

    </div>
    </div>

    <style>
        .dtr-table tbody td {
            padding: 0.005px !important;
        }
        .dtr-day-cell {
            font-size: 10px !important;
        }
        .dtr-time-cell {
            font-size: 12px !important;
        }
        .dtr-table tbody tr {
            transform: scaleY(0.97);
            transform-origin: top;
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.25in;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            .dtr-table {
                font-size: 9px;
            }
            .dtr-day-cell {
                font-size: 9px !important;
            }
            .dtr-time-cell {
                font-size: 11px !important;
            }
        }
    </style>
</x-admin-layout>
