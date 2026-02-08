<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form 48 - Daily Time Record</title>

    <style>
        /* === Base / Reset === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; background: #fff; }

        /* === Page === */
        @page {
            size: A4 portrait;
            margin: 0.25in 0.25in;
        }

        /* wrapper table holds two side-by-side cells */
        .layout-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .layout-cell {
            width: calc((100% - 0.15in) / 2);
            vertical-align: top;
            padding: 0 0.075in;
            box-sizing: border-box;
        }

        /* === Form container === */
        .form-container {
            width: 100%;
            min-height: 0;
            border: 1px solid #000;
            padding: 0.10in 0.08in;
            display: block;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        /* === Header === */
        .header { position: relative; text-align: center; margin-bottom: 4px; }
        .header-left {
            position: absolute; left: 0.06in; top: -0.02in;
            font-weight: bold; font-style: italic; font-size: 12px;
        }
        .header-center { font-weight: bold; font-size: 12px; letter-spacing: 0.5px; }

        /* === Employee name & meta === */
        .employee-name { text-align: center; margin-bottom: 4px; margin-top: 8px; font-size: 11px; }
        .employee-name-line {
            display: inline-block;
            width: 88%;
            border-bottom: 1px solid #000;
            padding: 2px 2px;
            font-weight: bold;
            font-size: 14px;
        }

        .month-row { margin-bottom: 4px; margin-top: 8px; font-size: 11px; }
        .month-value {
            display: inline-block;
            min-width: 1.1in;
            border-bottom: 1px solid #000;
            padding: 2px 4px;
            font-size: 11px;
            text-align: center;
        }

        /* info grid */
        .info-grid { width: 100%; margin-bottom: 6px; border-collapse: collapse; font-size: 11px; }
        .info-grid td { padding: 2px 3px; vertical-align: middle; }
        .info-grid .label { font-weight: bold; white-space: nowrap; }
        .info-grid .value { border-bottom: 1px solid #000; text-align: center; }

        /* === Attendance table === */
        table.attendance {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 10px;
            page-break-inside: auto;
        }

        table.attendance th,
        table.attendance td {
            border: 1px solid #000;
            padding: 0 1px;
            text-align: center;
            vertical-align: middle;
            word-break: break-word;
        }

        table.attendance th {
            background: #f5f5f5;
            font-size: 9px;
            font-weight: bold;
        }

        table.attendance tbody tr { page-break-inside: avoid; page-break-after: auto; }
        table.attendance tbody tr td { height: 11px; font-size: 10px; }
        table.attendance tbody tr td.day-cell { font-weight: bold; font-size: 11px; }
        table.attendance tbody tr td.time-cell { font-size: 10px; }

        .total-row td { font-weight: bold; font-size: 9px; height: 14px; }

        /* Certification block */
        .certification { margin-top: 4px; font-size: 11px; line-height: 1.2; }
        .cert-text { font-style: italic; margin-bottom: 3px; font-size: 11px; }

        /* signature lines */
        .signature-line { border-bottom: 1px solid #000; height: 12px; margin: 7px 20%; }
        .signature-label { text-align: center; font-weight: bold; font-size: 11px; margin-top: 2px; }

        .verified-title { text-align: center; font-weight: bold; font-size: 12px; margin-top: 3px; text-transform: uppercase; }
        .verified-position { text-align: center; font-size: 11px; margin-top: 1px; }
    </style>
</head>
<body>
    <table class="layout-table" role="presentation">
        <tr>
            @for($copy = 1; $copy <= 2; $copy++)
            <td class="layout-cell">
                <div class="form-container">

                    <!-- Header -->
                    <div class="header">
                        <div class="header-left">CSC Form No. 48</div>
                        <div class="header-center">DAILY TIME RECORD</div>
                    </div>

                    <!-- Employee info -->
                    <div class="employee-info">
                        <div class="employee-name">
                            @php
                                $middleInitial = $employee->middle_name ? strtoupper(substr($employee->middle_name, 0, 1)) . '.' : '';
                                $suffix = $employee->suffix ? ' ' . $employee->suffix : '';
                                $fullName = trim("{$employee->first_name} {$middleInitial} {$employee->last_name}{$suffix}");
                            @endphp
                            <div class="employee-name-line">{{ $fullName }}</div>
                        </div>

                        <div class="month-row">
                            For the month of <span class="month-value">{{ $startDate->format('F Y') }}</span>
                            <span style="margin-left: 12px; font-weight: bold;">ID No.</span>
                            <span style="border-bottom: 1px solid #000; padding: 2px 4px; display: inline-block; min-width: 0.6in; text-align: center;">{{ $employee->badge_number }}</span>
                        </div>

                        <table class="info-grid" role="presentation">
                            <tr>
                                <td class="label">Official Arrival:</td>
                                <td class="value" style="width:1.2in">8:00AM</td>
                                <td class="label" style="padding-left:6px;">Regular Days:</td>
                                <td class="value" style="width:0.7in">{{ $regularDays }}</td>
                            </tr>
                            <tr>
                                <td class="label">Departure:</td>
                                <td class="value">5:00PM</td>
                                <td class="label" style="padding-left:6px;">Saturdays:</td>
                                <td class="value">{{ $saturdays }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Attendance table -->
                    <table class="attendance" role="table" aria-label="Daily attendance">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width:8%;">Day</th>
                                <th colspan="2" style="width:24%;">A.M.</th>
                                <th colspan="2" style="width:24%;">P.M.</th>
                                <th colspan="2" style="width:18%;">Undertime</th>
                            </tr>
                            <tr>
                                <th style="width:11%;">Arrival</th>
                                <th style="width:11%;">Departure</th>
                                <th style="width:11%;">Arrival</th>
                                <th style="width:11%;">Departure</th>
                                <th style="width:8%;">Hours</th>
                                <th style="width:8%;">Mins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyDetails as $detail)
                            @php
                                $dayShort = ['S','M','T','W','T','F','S'][$detail['day_of_week']] ?? '';
                                $dayStyle = '';
                                if ($detail['day_of_week'] == 6) { // Saturday
                                    $dayStyle = 'color: #1e40af;'; // Dark Blue
                                } elseif ($detail['day_of_week'] == 0) { // Sunday
                                    $dayStyle = 'color: #991b1b;'; // Dark Red
                                }
                            @endphp
                            <tr>
                                <td class="day-cell" style="{{ $dayStyle }} text-align: left; font-weight: normal; padding-left: 2px;">&nbsp;{{ $detail['day'] }} - {{ $dayShort }}</td>
                                <td class="time-cell" style="padding: 0;">{{ $detail['am_arrival'] ?? '' }}</td>
                                <td class="time-cell" style="padding: 0;">{{ $detail['am_departure'] ?? '' }}</td>
                                <td class="time-cell" style="padding: 0;">{{ $detail['pm_arrival'] ?? '' }}</td>
                                <td class="time-cell" style="padding: 0;">{{ $detail['pm_departure'] ?? '' }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforeach

                            <tr class="total-row">
                                <td colspan="5" style="text-align: right; padding-right: 4px;">TOTAL</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Certification -->
                    <div class="certification">
                        <div class="cert-text">
                            I CERTIFY on my honor that the above is a true and correct report of the hours
                            of work performed, record of which was made daily at the time of arrival and
                            departure from office.
                        </div>

                        <div class="signature-line"></div>
                        <div class="signature-label">Signature</div>

                        @php
                            $signatoryDepartment = $employee->dtrSignatoryDepartment ?? $employee->department;
                            $signatoryName = $signatoryDepartment?->head_name ?? 'Head of Office';
                            $signatoryTitle = $signatoryDepartment?->head_position_title ?? '';
                        @endphp

                        <div class="cert-text" style="margin-top:4px;">
                            VERIFIED as to the prescribed office hours:
                        </div>

                        <div style="height: 12px; margin: 8px 20%;"></div>
                        <div class="verified-title" style="border: none;">{{ $signatoryName }}</div>
                        <div class="verified-position">{{ $signatoryTitle }}</div>
                    </div>

                </div>
            </td>
            @endfor
        </tr>
    </table>
</body>
</html>
