<x-admin-layout>
    <div class="max-w-7xl mx-auto print:max-w-none">
        <!-- Card Container for Print Area -->
        <div class="bg-white rounded-lg shadow-lg p-6 print:shadow-none print:p-0">
            <!-- Header + Actions -->
            <div class="mb-6 flex items-start justify-between gap-4 print:hidden">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Form 48 - Daily Time Record</h1>
                    <p class="text-gray-500 dark:text-gray-400">Civil Service Commission</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="history.back()" class="px-4 py-2 bg-gray-600 text-white hover:bg-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i> Back to DTR
                    </button>
                    <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    <form method="POST" action="{{ route('attendance-logs.form-48-download-pdf', $employee->id) }}" class="inline">
                        @csrf
                        <input type="hidden" name="start_date" value="{{ $startDate->toDateString() }}">
                        <input type="hidden" name="end_date" value="{{ $endDate->toDateString() }}">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white hover:bg-red-700">
                            <i class="fas fa-file-pdf mr-2"></i> Download PDF
                        </button>
                    </form>
                </div>
            </div>

            <script>
                function downloadPDF() {
                    // This function is no longer used; the form submission handles PDF download
                }
            </script>

            <!-- Form 48 Document - Two Column Layout -->
            <div class="grid grid-cols-2 gap-8 print:gap-8">
            @for($copy = 1; $copy <= 2; $copy++)
            <!-- Form Copy {{ $copy }} -->
            <div class="bg-white border border-gray-900 print:border-black p-2 text-[9px]" style="font-family: 'Times New Roman', Times, serif; width: 99.8%; box-sizing: border-box;">
                <!-- Form Header Row -->
                <div class="flex justify-between items-baseline mb-2" style="margin-left: -2px; margin-right: -2px;">
                    <p class="text-[12px] font-bold italic text-gray-700">CSC Form No. 48</p>
                    <h2 class="text-[12px] font-bold text-gray-900 print:text-black">DAILY TIME RECORD</h2>
                </div>

                <!-- Employee Information -->
                <div class="space-y-0 mb-2 text-[11px]">
                    <div class="text-center -mb-3 mt-5" style="font-family: 'Times New Roman', Times, serif;">
                        <span class="inline-block mx-auto border-b border-gray-900 print:border-black font-bold uppercase text-center text-[14px] leading-tight" style="max-width: 80%; word-wrap: break-word; padding: 0; line-height: 1;">{{ $employee->getFullName() }}</span>
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
                    <div class="flex items-center gap-2">
                        <span class="font-semibold w-28">Official Arrival:</span>
                        <span class="border-b border-gray-900 print:border-black px-1 w-20 text-center">8:00AM</span>
                        <span class="font-semibold w-28 text-right">Regular Days:</span>
                        <span class="border-b border-gray-900 print:border-black px-1 w-14 text-center">{{ $regularDays }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold w-28 pl-6">Departure:</span>
                        <span class="border-b border-gray-900 print:border-black px-1 w-20 text-center">5:00PM</span>
                        <span class="font-semibold w-28 text-right">Saturdays:</span>
                        <span class="border-b border-gray-900 print:border-black px-1 w-14 text-center">{{ $saturdays }}</span>
                    </div>
                </div>

                <!-- Daily Time Record Table -->
                <div class="mb-3" style="margin-left: -8px; margin-right: -8px; margin-bottom: -9px;">
                    <table class="w-full border-collapse" style="width: 99.8%; font-size: 10px;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 7.5%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px;">Day</th>
                                <th colspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 22%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px;">A.M.</th>
                                <th colspan="2" class="border-r border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 22%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px;">P.M.</th>
                                <th colspan="2" class="border-t border-b border-gray-900 print:border-black font-bold text-center" style="width: 16%; padding: 0.25px 0; font-family: 'Times New Roman', Times, serif; font-size: 11px;">Undertime</th>
                            </tr>
                            <tr>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Arrival</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Departure</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Arrival</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Departure</th>
                                <th class="border-r border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Hours</th>
                                <th class="border-b border-gray-900 print:border-black py-0 text-center" style="font-family: 'Times New Roman', Times, serif; font-size: 9px; padding: 0.25px 0;">Mins</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dailyDetails as $detail)
                                @php
                                    $dayShort = ['S','M','T','W','T','F','S'][$detail['day_of_week']] ?? '';
                                    $dayColor = '';
                                    if ($detail['day_of_week'] == 6) { // Saturday
                                        $dayColor = 'color: #1e40af;'; // Dark Blue
                                    } elseif ($detail['day_of_week'] == 0) { // Sunday
                                        $dayColor = 'color: #991b1b;'; // Dark Red
                                    }
                                @endphp
                                <tr>
                                    <td class="border-r border-b border-gray-900 print:border-black text-left" style="width: 7.5%; font-size: 11px; padding: 0 2px; {{ $dayColor }}">&nbsp;{{ $detail['day'] }} - {{ $dayShort }}</td>
                                    <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 13px; padding: 0 0;">{{ $detail['am_arrival'] ?? '' }}</td>
                                    <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 13px; padding: 0 0;">{{ $detail['am_departure'] ?? '' }}</td>
                                    <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 13px; padding: 0 0;">{{ $detail['pm_arrival'] ?? '' }}</td>
                                    <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 10%; font-size: 13px; padding: 0 0;">{{ $detail['pm_departure'] ?? '' }}</td>
                                    <td class="border-r border-b border-gray-900 print:border-black text-center" style="width: 7%; padding: 0 0;"></td>
                                    <td class="border-b border-gray-900 print:border-black text-center" style="width: 7%; padding: 0 0;"></td>
                                </tr>
                            @endforeach
                            <!-- Total Row -->
                            <tr class="font-semibold">
                                <td colspan="5" class="border-r border-b border-gray-900 print:border-black px-1 py-1 text-right" style="width: 60%; font-size: 12px;">TOTAL</td>
                                <td class="border-r border-b border-gray-900 print:border-black py-1 text-center" style="width: 8%;"></td>
                                <td class="border-b border-gray-900 print:border-black py-1 text-center" style="width: 8%;"></td>
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

    <!-- Notice Section (outside card, next row) -->
    <div class="mt-4" style="font-family: 'Times New Roman', Times, serif;">
        <p class="text-[10px] text-gray-800 leading-tight italic">
            This CSC Form No. 48 (Daily Time Record) was automatically generated by the Attendance Management System. All entries contained herein— including time-in, time-out, undertime, overtime, and other attendance-related data — are directly sourced from the biometric device and have not been manually modified, altered, or edited in any way.
        </p>
    </div>
    </div>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.3in;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</x-admin-layout>
