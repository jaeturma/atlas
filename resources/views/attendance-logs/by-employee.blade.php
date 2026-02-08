<x-admin-layout>
    <div class="container mx-auto px-2 sm:px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Raw Logs</h1>
            <p class="text-sm text-gray-600">{{ $employee->getFullName() }} • Badge: {{ $employee->badge_number ?? 'N/A' }}</p>
        </div>
        <button type="button" onclick="history.back()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded w-full sm:w-auto">← Back to Logs</button>
    </div>

    <!-- Employee Info Card (Hidden on print) -->
    <div class="bg-white rounded-lg shadow p-6 mb-6 screen-only">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <div class="text-gray-600 text-sm">Position</div>
                <div class="font-semibold">{{ $employee->position?->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Department</div>
                <div class="font-semibold">{{ $employee->department?->name ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Email</div>
                <div class="font-semibold text-sm">{{ $employee->email ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Phone</div>
                <div class="font-semibold">{{ $employee->phone ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-6 screen-only">
        <form method="GET" action="{{ route('attendance-logs.by-employee', $employee) }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded w-full">Filter</button>
                <a href="{{ route('attendance-logs.by-employee', $employee) }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded w-full text-center">Reset</a>
            </div>
            
            <div class="flex items-end">
                <button type="button" onclick="window.print()" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded w-full">
                    <i class="fas fa-print mr-2"></i>Print Raw
                </button>
            </div>
        </form>
    </div>

    <!-- Print Title and Content (hidden on screen) -->
    @php
        $startDate = request('start_date') ? \Carbon\Carbon::parse(request('start_date')) : \Carbon\Carbon::now();
        $endDate = request('end_date') ? \Carbon\Carbon::parse(request('end_date')) : \Carbon\Carbon::now();
        $printTitle = '';
        
        if ($startDate->isSameMonth($endDate)) {
            $printTitle = strtoupper($startDate->format('F Y'));
        } else {
            $printTitle = strtoupper($startDate->format('F Y')) . ' - ' . strtoupper($endDate->format('F Y'));
        }
        
        $printLogs = $printLogs ?? $logs;

        // Group logs by date for print view
        $logsByDate = $printLogs->groupBy(function($log) {
            return $log->log_datetime->format('Y-m-d');
        });
        
        // Generate all days of the month based on start date
        $daysInMonth = $startDate->daysInMonth;
        $allDays = collect();
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $startDate->copy()->setDay($day)->format('Y-m-d');
            $allDays->put($date, $logsByDate->get($date) ?? collect());
        }
    @endphp
    
    @php
        $employeeName = $employee?->getFullName();
        if (!$employeeName) {
            $employeeName = trim(($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''));
        }
        $employeeName = $employeeName ?: 'N/A';
    @endphp

    <div class="print-only p-8">
        <div class="text-center mb-6">
            <h1 class="text-lg font-bold mb-2">RAW LOG RECORDS</h1>
            <p class="text-sm mb-1">of <strong>{{ $employeeName }}</strong> - <strong>Badge No.</strong> <strong>{{ $employee->badge_number ?? 'N/A' }}</strong></p>
            <p class="text-sm">for {{ $printTitle }}</p>
        </div>

        <!-- Print View Table -->
        <table class="w-full border-collapse" style="border: 1px solid #000;">
            <thead>
                <tr>
                    <th style="border: 1px solid #000; padding: 0.25rem; text-align: left; font-weight: bold;">Date</th>
                    <th style="border: 1px solid #000; padding: 0.25rem; text-align: left; font-weight: bold;">Day</th>
                    <th style="border: 1px solid #000; padding: 0.25rem; text-align: left; font-weight: bold;">Logs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allDays as $date => $dateLogs)
                    @php
                        $dateObj = \Carbon\Carbon::parse($date);
                        $times = $dateLogs->count() > 0 
                            ? $dateLogs->sortBy('log_datetime')->map(function($log) {
                                return $log->log_datetime->format('g:i');
                            })->implode(', ')
                            : '';
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; padding: 0.25rem;">{{ $dateObj->format('j') }}</td>
                        <td style="border: 1px solid #000; padding: 0.25rem;">{{ $dateObj->format('l') }}</td>
                        <td style="border: 1px solid #000; padding: 0.25rem;">{{ $times }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Note Section -->
        <div class="note-section" style="margin-top: 16px; font-size: 10px; line-height: 1.4;">
            <p style="font-weight: bold; margin-bottom: 8px; text-align: left;">NOTE:</p>
            <p style="text-align: left; margin-bottom: 8px;">This RLR Form (Raw Log Record) is automatically generated by the Attendance Management System. All data reflected in this document are the actual raw logs recorded by the biometric device, including multiple punches, doubled entries, or successive time-ins/time-outs captured during the day.</p>
            <p style="text-align: left; margin: 0;">No entries have been manually modified, edited, filtered, or corrected. The information presented is exactly as transmitted by the device and is produced strictly for official reference and compliance with Civil Service Commission (CSC) requirements.</p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section" style="margin-top: 36px; font-size: 10px;">
            <div style="display: flex; justify-content: space-between; gap: 24px;">
                <div style="flex: 1; text-align: center;">
                    <div style="border-bottom: 1px solid #000; height: 18px;"></div>
                    <div style="margin-top: 4px; font-weight: bold;">{{ $employeeName }}</div>
                    <div style="font-size: 9px;">{{ $employee->position?->name ?? 'Position' }}</div>
                    <div style="font-size: 9px; margin-top: 2px;">Employee Signature</div>
                </div>
                <div style="flex: 1; text-align: center;">
                    <div style="border-bottom: 1px solid #000; height: 18px;"></div>
                    <div style="margin-top: 4px; font-weight: bold;">DTR Incharge</div>
                    <div style="font-size: 9px;">Signature</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs (Screen View) -->
    <div class="bg-white rounded-lg shadow overflow-hidden screen-only">
        <!-- Mobile cards -->
        <div class="md:hidden divide-y divide-gray-200">
            @forelse ($logs as $log)
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $log->log_datetime->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->log_datetime->format('H:i:s') }}</div>
                        </div>
                        <span class="badge {{ $log->getStatusBadgeClass() }}">{{ $log->status ?? 'N/A' }}</span>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">Device: {{ $log->device->name }}</div>
                    <div class="mt-2">
                        <span class="badge {{ $log->getPunchTypeBadge() }}">{{ $log->punch_type ?? 'N/A' }}</span>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">No attendance records found</div>
            @endforelse
        </div>

        <!-- Desktop table -->
        <div class="hidden md:block">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Date & Time</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Device</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium">{{ $log->log_datetime->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->log_datetime->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $log->device->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="badge {{ $log->getStatusBadgeClass() }}">
                                {{ $log->status ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="badge {{ $log->getPunchTypeBadge() }}">
                                {{ $log->punch_type ?? 'N/A' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No attendance records found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6 screen-only">
        {{ $logs->links() }}
    </div>
</div>

<style>
    .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: white;
    }

    .badge-success {
        background-color: #10b981;
    }

    .badge-danger {
        background-color: #ef4444;
    }

    .badge-primary {
        background-color: #3b82f6;
    }

    .badge-info {
        background-color: #06b6d4;
    }

    .badge-warning {
        background-color: #f59e0b;
    }

    .badge-secondary {
        background-color: #6b7280;
    }

    .screen-only {
        display: block !important;
    }
    
    .print-only {
        display: none !important;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0.5in;
        }
        
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
        }
        
        body, html {
            width: 100% !important;
            height: 100% !important;
        }
        
        body {
            print-color-adjust: exact !important;
            -webkit-print-color-adjust: exact !important;
            font-family: Arial, sans-serif !important;
            font-size: 11px !important;
        }
        
        /* Show print content, hide everything else */
        .print-only {
            display: block !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .screen-only {
            display: none !important;
        }
        
        /* Hide layout wrappers but keep content */
        x-admin-layout {
            display: block !important;
            all: revert !important;
        }
        
        .container {
            all: revert !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Hide specific screen elements */
        nav, 
        header, 
        footer,
        .flex,
        .pagination,
        .bg-gray-500,
        .bg-white,
        .shadow,
        .rounded-lg,
        .grid {
            display: none !important;
        }
        
        /* Print title styles */
        .print-only h1 {
            font-size: 14px !important;
            font-weight: bold !important;
            margin: 0 0 8px 0 !important;
            text-align: center !important;
        }
        
        .print-only p {
            font-size: 11px !important;
            text-align: center !important;
            margin: 0 0 8px 0 !important;
        }
        
        .print-only p strong {
            display: inline !important;
            font-weight: bold !important;
        }
        
        /* Ensure table is visible */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        th, td {
            border: 1px solid #000 !important;
            padding: 3px !important;
            text-align: left !important;
            font-weight: normal !important;
            line-height: 1.2 !important;
        }
        
        th {
            font-weight: bold !important;
            background: white !important;
        }
        
        /* Note section */
        .note-section {
            margin-top: 16px !important;
            font-size: 10px !important;
            line-height: 1.4 !important;
        }
        
        .note-section p {
            text-align: left !important;
            margin: 0 0 8px 0 !important;
        }
    }
</style>
</div>
</x-admin-layout>

