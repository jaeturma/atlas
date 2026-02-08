<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CS Form No. 6 - PDF</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; color: #000; margin: 0; }
        @page { size: A4 portrait; margin: 0.4in; }
        .page { width: 100%; min-height: 10.5in; }
        .title { text-align: center; font-weight: bold; font-size: 16px; margin-top: 2px; }
        .sub { text-align: center; font-size: 12px; }
        .cs { text-align: right; font-size: 11px; }
        .annex { font-size: 11px; margin-top: 6px; }
        table.form { width: 100%; border-collapse: collapse; font-size: 12px; }
        table.form td, table.form th { border: 1px solid #000; padding: 6px 6px; vertical-align: top; line-height: 1.2; }
        .label { font-weight: bold; }
        .line { border-bottom: 1px solid #000; display: inline-block; min-width: 120px; }
        .small { font-size: 11px; }
        .section { font-weight: bold; margin: 6px 0 4px; }
        .checkbox { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; text-align: center; line-height: 10px; font-size: 10px; margin-right: 4px; }
        .signature { height: 0.35in; border-bottom: 1px solid #000; }
        .center { text-align: center; }
        .tall-cell { min-height: 2.2in; }
        .row-tall { height: 2.3in; }
        .row-mid { height: 1.15in; }
        .row-short { height: 0.6in; }
        .cert-table { width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 6px; }
        .cert-table td { border: 1px solid #000; padding: 4px 6px; height: 0.3in; }
        .vmid { vertical-align: middle; }
    </style>
</head>
<body>
@php
    $leaveTypeName = $leave->leaveType?->name ?? 'Leave';
    $leavePeriod = $leave->leave_period ?? 'full';
    $periodLabel = $leavePeriod === 'full' ? 'Full Day' : 'Half Day - ' . strtoupper($leavePeriod === 'morning' ? 'AM' : 'PM');
    $dateRange = $leave->start_date?->format('F d, Y') ?? '—';
    if ($leave->end_date && $leave->start_date && $leave->end_date->format('Y-m-d') !== $leave->start_date->format('Y-m-d')) {
        $dateRange .= ' - ' . $leave->end_date->format('F d, Y');
    }
    $filingDate = $leave->created_at?->format('F d, Y') ?? now()->format('F d, Y');
    $selected = strtolower($leaveTypeName);
    $check = function (array $needles) use ($selected) {
        foreach ($needles as $n) {
            if (str_contains($selected, strtolower($n))) {
                return 'X';
            }
        }
        return '';
    };
    $approverName = $leave->approved_pnpki_full_name ?: ($leave->approver?->name ?? '');
    $approverSerial = $leave->approved_pnpki_serial_number ?: '';
@endphp

<div class="page">
    <div class="cs">Civil Service Form No. 6<br>Revised 2020</div>
    <div class="sub">Republic of the Philippines</div>
    <div class="sub">Department of Education</div>
    <div class="title">APPLICATION FOR LEAVE</div>
    <div class="annex">ANNEX A</div>

    <table class="form" role="presentation">
        <tr class="row-short">
            <td colspan="2" class="vmid">
                <div class="label">1. OFFICE/DEPARTMENT</div>
                <div style="margin-top:4px;"><strong>{{ $department?->name ?? '—' }}</strong></div>
            </td>
            <td colspan="3" class="vmid">
                <div class="label">2. NAME</div>
                <div style="margin-top:4px;"><strong>{{ $employee?->last_name ?? '—' }}, {{ $employee?->first_name ?? '—' }} {{ $employee?->middle_name ?? '' }}</strong></div>
            </td>
        </tr>
        <tr class="row-short">
            <td colspan="2" class="vmid">
                <div class="label">3. DATE OF FILING</div>
                <div style="margin-top:4px;"><strong>{{ $filingDate }}</strong></div>
            </td>
            <td colspan="2" class="vmid">
                <div class="label">4. POSITION</div>
                <div style="margin-top:4px;"><strong>{{ $position?->name ?? '—' }}</strong></div>
            </td>
            <td class="vmid">
                <div class="label">5. SALARY</div>
                <div style="margin-top:4px;"><strong>—</strong></div>
            </td>
        </tr>
        <tr class="row-tall">
            <td colspan="3">
                <div class="label">6.A TYPE OF LEAVE TO BE AVAILED OF</div>
                <div class="small">
                    <div><span class="checkbox">{{ $check(['Vacation']) }}</span>Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['Mandatory', 'Forced']) }}</span>Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['Sick']) }}</span>Sick Leave (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['Maternity']) }}</span>Maternity Leave (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)</div>
                    <div><span class="checkbox">{{ $check(['Paternity']) }}</span>Paternity Leave (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)</div>
                    <div><span class="checkbox">{{ $check(['Special Privilege']) }}</span>Special Privilege Leave (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['Solo Parent']) }}</span>Solo Parent Leave (RA No. 8972 / CSC MC No. 8, s. 2004)</div>
                    <div><span class="checkbox">{{ $check(['Study']) }}</span>Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['VAWC', '10-Day']) }}</span>10-Day VAWC Leave (RA No. 9262 / CSC MC No. 15, s. 2005)</div>
                    <div><span class="checkbox">{{ $check(['Rehabilitation']) }}</span>Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</div>
                    <div><span class="checkbox">{{ $check(['Special Emergency', 'Calamity']) }}</span>Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)</div>
                    <div><span class="checkbox">{{ $check(['Adoption']) }}</span>Adoption Leave (R.A. No. 8552)</div>
                    <div><span class="checkbox">{{ $check(['Special Leave Benefits', 'Women']) }}</span>Special Leave Benefits for Women (RA No. 9710 / CSC MC No. 25, s. 2010)</div>
                    <div><span class="checkbox">{{ $check(['Monetization']) }}</span>Monetization of Leave Credits</div>
                    <div><span class="checkbox">{{ $check(['Terminal']) }}</span>Terminal Leave</div>
                </div>
            </td>
            <td colspan="2">
                <div class="label">6.B DETAILS OF LEAVE</div>
                <div class="small">
                    <div>In case of Vacation/Special Privilege Leave:</div>
                    <div><span class="checkbox"></span>Within the Philippines __________________________</div>
                    <div><span class="checkbox"></span>Abroad (Specify) ________________________________</div>
                    <div style="margin-top:4px;">In case of Sick Leave:</div>
                    <div><span class="checkbox"></span>In Hospital (Specify Illness) _____________________</div>
                    <div><span class="checkbox"></span>Out Patient (Specify Illness) ____________________</div>
                    <div style="margin-top:4px;">In case of Special Leave Benefits for Women:</div>
                    <div><span class="checkbox"></span>(Specify Illness) ________________________________</div>
                    <div style="margin-top:4px;">In case of Study Leave:</div>
                    <div><span class="checkbox"></span>Completion of Master's Degree</div>
                    <div><span class="checkbox"></span>BAR/Board Examination Review</div>
                    <div style="margin-top:4px;">Other purpose/Reason:</div>
                    <div>{{ $leave->reason ?: '______________________________' }}</div>
                </div>
            </td>
        </tr>
        <tr class="row-mid">
            <td colspan="3">
                <div class="label">6.C NUMBER OF WORKING DAYS APPLIED FOR</div>
                <div class="center" style="margin-top:6px;">{{ number_format((float) $leave->number_of_days, 2) }} day(s)</div>
                <div class="small" style="margin-top:8px;">INCLUSIVE DATES:</div>
                <div class="center" style="margin-top:6px; text-decoration: underline;"><strong>{{ $dateRange }}</strong></div>
            </td>
            <td colspan="2">
                <div class="label">6.D COMMUTATION</div>
                <div class="small"><span class="checkbox"></span>Not Requested</div>
                <div class="small"><span class="checkbox"></span>Requested</div>
                <div class="small" style="margin-top:6px;">Signature of Applicant</div>
                <div class="signature"></div>
            </td>
        </tr>
        <tr class="row-mid">
            <td colspan="3">
                <div class="label">7.A CERTIFICATION OF LEAVE CREDITS</div>
                <div class="small">As of _______________________</div>
                <table class="cert-table" role="presentation">
                    <tr>
                        <td></td>
                        <td>Vacation Leave</td>
                        <td>Sick Leave</td>
                    </tr>
                    <tr>
                        <td>Total Earned</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Less this application</td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Balance</td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
            </td>
            <td colspan="2">
                <div class="label">7.B RECOMMENDATION</div>
                <div class="small"><span class="checkbox"></span>For approval</div>
                <div class="small"><span class="checkbox"></span>For disapproval due to _________________________</div>
                <div class="small" style="margin-top:6px;">Reason for disapproval: _______________________</div>
            </td>
        </tr>
        <tr class="row-short">
            <td colspan="3">
                <div class="label">7.C APPROVED FOR:</div>
                <div class="small">{{ $leave->status === 'Approved' ? number_format((float) $leave->number_of_days, 2) . ' day(s) with pay' : '_______ days with pay' }}</div>
                <div class="small">_______ days without pay</div>
                <div class="small">_______ others (Specify) _____________________</div>
            </td>
            <td colspan="2">
                <div class="label">7.D DISAPPROVED DUE TO:</div>
                <div class="small">{{ $leave->status === 'Approved' ? '—' : 'Pending approval' }}</div>
            </td>
        </tr>
        <tr class="row-short">
            <td colspan="2" class="center">
                <div class="signature"></div>
                <div class="small">(Signature of Applicant)</div>
            </td>
            <td colspan="3" class="center">
                <div class="signature"></div>
                <div class="small">{{ $approverName ?: '(Authorized Official)' }}</div>
                @if (!empty($approverSerial))
                    <div class="small">PNPKI: {{ $approverSerial }}</div>
                @endif
            </td>
        </tr>
    </table>
</div>
</body>
</html>
