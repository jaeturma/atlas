<x-admin-layout>
    <x-slot name="header">
        {{ $device->name }} Attendance Records
    </x-slot>
    <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">{{ $device->name }}</h1>
            <p class="text-gray-600 mt-1">Attendance Records</p>
        </div>
        <a href="{{ route('attendance-logs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">← Back to Logs</a>
    </div>

    <!-- Device Info Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div>
                <div class="text-gray-600 text-sm">IP Address</div>
                <div class="font-semibold font-mono">{{ $device->ip_address }}:{{ $device->port }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Model</div>
                <div class="font-semibold">{{ $device->model ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Serial</div>
                <div class="font-semibold text-sm">{{ $device->serial_number ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Location</div>
                <div class="font-semibold">{{ $device->location ?? 'N/A' }}</div>
            </div>
            <div>
                <div class="text-gray-600 text-sm">Status</div>
                <div class="font-semibold">
                    @if ($device->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" action="{{ route('attendance-logs.by-device', $device) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Employee</label>
                <select name="employee_id" class="w-full border rounded px-3 py-2">
                    <option value="">-- All Employees --</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>
                            {{ $employee->getFullName() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border rounded px-3 py-2">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded flex-1">Filter</button>
                <a href="{{ route('attendance-logs.by-device', $device) }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded flex-1">Reset</a>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Date & Time</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Employee</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Badge #</th>
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
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No attendance records found</td>
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

