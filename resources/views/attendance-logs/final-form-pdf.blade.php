@php
    // Group logs by date
    $logsByDate = $logs->groupBy(function ($log) {
        return $log->log_date->format('Y-m-d');
    });
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Final Daily Time Record</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 8.5in;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header .info {
            font-size: 10px;
            margin: 3px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        thead {
            background-color: #e6e6e6;
            font-weight: bold;
            border: 1px solid #999;
        }
        
        thead th {
            padding: 8px 4px;
            text-align: left;
            border: 1px solid #999;
            font-size: 10px;
        }
        
        tbody td {
            padding: 0.005px;
            border: 1px solid #999;
            font-size: 10px;
        }
        tbody tr {
            transform: scaleY(0.99);
            transform-origin: top;
        }
        
        tbody tr {
            page-break-inside: avoid;
        }
        
        .weekend {
            background-color: #fffacd;
        }
        
        .time-in {
            color: #228B22;
            font-weight: bold;
        }
        
        .time-out {
            color: #DC143C;
            font-weight: bold;
        }
        
        .badge-holiday {
            background-color: #ffcccc;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }
        
        .badge-activity {
            background-color: #ccdbff;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }
        
        .badge-weekend {
            background-color: #fffacd;
            padding: 2px 4px;
            border-radius: 3px;
            display: inline-block;
        }
        
        .legend {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #999;
            font-size: 10px;
        }
        
        .legend-title {
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .legend-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        
        .legend-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid #999;
            margin-right: 4px;
            vertical-align: middle;
        }
        
        .center {
            text-align: center;
        }
        
        .empty {
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>FINAL DAILY TIME RECORD</h1>
            <div class="info">Employee: {{ $employee->getFullName() }} (Badge: {{ $employee->badge_number }})</div>
            <div class="info">Department: {{ $employee->department->name ?? 'N/A' }}</div>
            <div class="info">Period: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 8%;">Day</th>
                    <th style="width: 10%;">In Time</th>
                    <th style="width: 10%;">Out Time</th>
                    <th style="width: 15%;">Holiday</th>
                    <th style="width: 15%;">Activity</th>
                    <th style="width: 30%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dates as $date)
                    @php
                        $dayName = $date->format('l');
                        $dateStr = $date->format('Y-m-d');
                        $dateLogs = $logsByDate->get($dateStr, collect());
                        $holiday = $holidays->get($dateStr);
                        $activity = $activities->get($dateStr);
                        
                        $inTime = $dateLogs->firstWhere('status', 'In');
                        $outTime = $dateLogs->where('status', 'Out')->last();
                        
                        $isWeekend = in_array($dayName, ['Saturday', 'Sunday']);
                    @endphp
                    <tr class="{{ $isWeekend ? 'weekend' : '' }}">
                        <td>{{ $date->format('m/d/Y') }}</td>
                        <td class="center">{{ substr($dayName, 0, 3) }}</td>
                        <td class="center">
                            @if ($inTime)
                                <span class="time-in">{{ $inTime->log_time }}</span>
                            @else
                                <span class="empty">--:--</span>
                            @endif
                        </td>
                        <td class="center">
                            @if ($outTime)
                                <span class="time-out">{{ $outTime->log_time }}</span>
                            @else
                                <span class="empty">--:--</span>
                            @endif
                        </td>
                        <td>
                            @if ($holiday)
                                <span class="badge-holiday">{{ $holiday->name }}</span>
                            @elseif ($isWeekend)
                                <span class="badge-weekend">{{ $dayName }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($activity)
                                <span class="badge-activity">{{ $activity->activity_type }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($activity && $activity->description)
                                {{ $activity->description }}
                            @elseif ($holiday && $holiday->description)
                                {{ $holiday->description }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-title">Legend:</div>
            <div class="legend-item">
                <span class="legend-box" style="background-color: #fffacd; border: 1px solid #ccc;"></span>
                <span>Saturday/Sunday</span>
            </div>
            <div class="legend-item">
                <span class="legend-box" style="background-color: #ffcccc; border: 1px solid #ccc;"></span>
                <span>Holiday</span>
            </div>
            <div class="legend-item">
                <span class="legend-box" style="background-color: #ccdbff; border: 1px solid #ccc;"></span>
                <span>Activity</span>
            </div>
            <div class="legend-item">
                <span style="color: #228B22; font-weight: bold;">IN</span> / <span style="color: #DC143C; font-weight: bold;">OUT</span>
                <span>= Time Entries</span>
            </div>
        </div>
    </div>
</body>
</html>
