<!DOCTYPE html>
<html lang="en" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Microsoft Word">
    <title>Individual Daily Log and Accomplishment Report</title>
    <style>
        @page { size: 21cm 29.7cm; margin: 0.5in; }
        body { font-family: 'Times New Roman', Times, serif; color: #000; margin: 0; font-size: 11px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .small { font-size: 10px; }
        .header { line-height: 1.2; }
        .section { margin-top: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; vertical-align: top; }
        th { background: #e6e6e6; font-weight: bold; text-align: center; }
        .no-border { border: none; }
        .label { width: 20%; }
    </style>
</head>
<body>
    @php
        $middleInitial = $employee->middle_name ? strtoupper(substr($employee->middle_name, 0, 1)) . '.' : '';
        $suffix = $employee->suffix ? ' ' . $employee->suffix : '';
        $fullName = trim("{$employee->first_name} {$middleInitial} {$employee->last_name}{$suffix}");
        $positionName = $employee->position?->name ?? '';
        $departmentName = $employee->department?->name ?? '';
    @endphp

    <div class="center header">
        <div class="bold">Republic of the Philippines</div>
        <div class="bold">Department of Education</div>
        <div class="bold">DepEd Order No. 11, s. 2020</div>
        <div class="bold">Enclosure No. 3</div>
        <div class="section bold uppercase">Individual Daily Log and Accomplishment Report</div>
    </div>

    <table class="section" style="border: none;">
        <tr>
            <td class="no-border label bold">Name</td>
            <td class="no-border">: <span class="uppercase">{{ $fullName }}</span></td>
        </tr>
        <tr>
            <td class="no-border label bold">Position</td>
            <td class="no-border">: {{ $positionName }}</td>
        </tr>
        <tr>
            <td class="no-border label bold">Office/Unit</td>
            <td class="no-border">: {{ $departmentName }}</td>
        </tr>
        <tr>
            <td class="no-border label bold">Period Covered</td>
            <td class="no-border">: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</td>
        </tr>
    </table>

    <table class="section">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th>Daily Log / Accomplishments</th>
                <th style="width: 18%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dates as $detail)
                @php
                    $dateStr = $detail->format('Y-m-d');
                    $activitiesForDate = $activitiesByDate[$dateStr] ?? [];

                    $filteredActivities = array_values(array_filter($activitiesForDate, function ($activity) {
                        $type = strtolower(trim($activity->activity_type ?? ''));
                        if ($type === '') {
                            return false;
                        }

                        // Exclude Force Leave, On-Leave variants, CTO leave, and all leave types
                        if (str_contains($type, 'leave')) {
                            return false;
                        }

                        return true;
                    }));
                @endphp
                @if (!empty($filteredActivities))
                    <tr>
                        <td class="center">{{ $detail->format('M d, Y') }}</td>
                        <td>
                            <ul style="margin: 0; padding-left: 16px;">
                                @foreach($filteredActivities as $activity)
                                    <li>
                                        <span class="bold">{{ $activity->activity_type }}</span>
                                        @if (!empty($activity->description))
                                            - {{ $activity->description }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="center">Completed</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="section">
        <div class="small">I certify that the above entries are true and correct.</div>
        <table style="width: 100%; border: none; margin-top: 12px;">
            <tr>
                <td class="no-border center" style="width: 50%;">
                    <div style="border-bottom: 1px solid #000; width: 70%; margin: 0 auto;">&nbsp;</div>
                    <div class="bold">Signature of Employee</div>
                </td>
                <td class="no-border center" style="width: 50%;">
                    <div style="border-bottom: 1px solid #000; width: 70%; margin: 0 auto;">&nbsp;</div>
                    <div class="bold">Immediate Supervisor</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
