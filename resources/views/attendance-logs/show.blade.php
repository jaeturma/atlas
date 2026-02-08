<x-admin-layout>
    <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-end items-center mb-6">
        <a href="{{ route('attendance-logs.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">← Back to Logs</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Record Details</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-gray-600 text-sm">Date</div>
                        <div class="font-semibold">{{ $log->log_date->format('F d, Y') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Time</div>
                        <div class="font-semibold font-mono">{{ $log->log_time }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Status</div>
                        <div class="mt-1">
                            <span class="badge {{ $log->getStatusBadgeClass() }}">
                                {{ $log->status ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Punch Type</div>
                        <div class="mt-1">
                            <span class="badge {{ $log->getPunchTypeBadge() }}">
                                {{ $log->punch_type ?? 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <h3 class="font-bold mb-3">Employee Information</h3>
                @if ($log->employee)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-gray-600 text-sm">Name</div>
                            <div class="font-semibold">{{ $log->employee->getFullName() }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 text-sm">Badge Number</div>
                            <div class="font-semibold font-mono">{{ $log->badge_number }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 text-sm">Position</div>
                            <div class="font-semibold">{{ $log->employee->position?->name ?? 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-600 text-sm">Department</div>
                            <div class="font-semibold">{{ $log->employee->department?->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-span-2">
                            <a href="{{ route('attendance-logs.by-employee', $log->employee) }}" class="text-blue-500 hover:underline">
                                View all attendance records for this employee →
                            </a>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
                        <p class="text-sm">
                            <strong>Badge Number:</strong> <span class="font-mono">{{ $log->badge_number }}</span>
                        </p>
                        <p class="text-sm text-gray-600 mt-2">This record is not yet linked to an employee</p>
                    </div>
                @endif

                <hr class="my-6">

                <h3 class="font-bold mb-3">Device Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-gray-600 text-sm">Device Name</div>
                        <div class="font-semibold">{{ $log->device->name }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">IP Address</div>
                        <div class="font-semibold font-mono">{{ $log->device->ip_address }}:{{ $log->device->port }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Model</div>
                        <div class="font-semibold">{{ $log->device->model ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-sm">Location</div>
                        <div class="font-semibold">{{ $log->device->location ?? 'N/A' }}</div>
                    </div>
                    <div class="col-span-2">
                        <a href="{{ route('attendance-logs.by-device', $log->device) }}" class="text-blue-500 hover:underline">
                            View all records from this device →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Record Info -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-bold mb-4">Record Info</h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-gray-600 text-xs">Record ID</div>
                        <div class="font-mono text-sm">#{{ $log->id }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-xs">Created</div>
                        <div class="text-sm">{{ $log->created_at->format('M d, Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-600 text-xs">Last Updated</div>
                        <div class="text-sm">{{ $log->updated_at->format('M d, Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-bold mb-4">Actions</h3>
                <div class="space-y-2">
                    <a href="{{ route('attendance-logs.index') }}" class="block text-center bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        View All Logs
                    </a>
                    @if ($log->employee)
                        <a href="{{ route('attendance-logs.by-employee', $log->employee) }}" class="block text-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Employee Records
                        </a>
                    @endif
                    <a href="{{ route('attendance-logs.by-device', $log->device) }}" class="block text-center bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Device Records
                    </a>
                </div>
            </div>
        </div>
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

