<x-admin-layout>
    <x-slot name="header">
        Daily Attendance Summary
    </x-slot>
    <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Daily Attendance Summary</h1>
        <a href="{{ route('attendance-logs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">← Back to Logs</a>
    </div>

    <!-- Date Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('attendance-logs.daily-summary') }}" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-2">Select Date</label>
                <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="border rounded px-3 py-2">
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Show</button>
        </form>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">Total Records</div>
            <div class="text-3xl font-bold mt-2">{{ $totalLogs }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">Check Ins</div>
            <div class="text-3xl font-bold text-green-600 mt-2">{{ $checkIns }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">Check Outs</div>
            <div class="text-3xl font-bold text-red-600 mt-2">{{ $checkOuts }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm font-medium">Date</div>
            <div class="text-lg font-bold mt-2">{{ $date->format('M d, Y') }}</div>
        </div>
    </div>

    <!-- Filter by Device -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('attendance-logs.daily-summary') }}" class="flex gap-4">
            <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Filter by Device</label>
                <select name="device_id" class="w-full border rounded px-3 py-2" onchange="this.form.submit()">
                    <option value="">-- All Devices --</option>
                    @foreach ($devices as $device)
                        <option value="{{ $device->id }}" @selected(request('device_id') == $device->id)>
                            {{ $device->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Time</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Employee</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Badge #</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Device</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-mono font-semibold">{{ $log->log_time }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if ($log->employee)
                                <a href="{{ route('attendance-logs.by-employee', $log->employee) }}" class="text-blue-500 hover:underline">
                                    {{ $log->employee->getFullName() }}
                                </a>
                            @else
                                <span class="text-gray-400">Not Linked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-mono">{{ $log->badge_number }}</td>
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
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No attendance records for this date</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
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
</style>
</div>
</x-admin-layout>

