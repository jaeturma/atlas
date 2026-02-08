<x-admin-layout>
    <x-slot name="header">
        Attendance Logs
    </x-slot>
    <div class="w-full h-full">

    @role('Superadmin')
    <div id="manual-entry-card" class="hidden bg-white rounded-lg shadow mb-6 border border-gray-400 mx-4 lg:mx-8 mt-8">
        <div class="p-6 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Manual Entry</h3>
                <p class="text-sm text-gray-600">Add attendance logs manually.</p>
            </div>
            <a href="{{ route('attendance-logs.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Add Manual Log
            </a>
        </div>
    </div>
    @endrole

    @role('Admin|Superadmin')
    <!-- USB Import -->
    <div class="bg-white rounded-lg shadow mb-6 border border-gray-400 mx-4 lg:mx-8 mt-8">
        <div class="p-6">
            <livewire:attendance-logs-import />
        </div>
    </div>
    @endrole

    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-400 mx-4 lg:mx-8 mb-6">
        <!-- Filters -->
        <div class="bg-gray-50 border-b border-gray-300 p-4">
            <form method="GET" action="{{ route('attendance-logs.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="cloud_badge_number" value="{{ request('cloud_badge_number') }}">
                <input type="hidden" name="cloud_device_id" value="{{ request('cloud_device_id') }}">
                <input type="hidden" name="cloud_status" value="{{ request('cloud_status') }}">
                <input type="hidden" name="cloud_start_date" value="{{ request('cloud_start_date') }}">
                <input type="hidden" name="cloud_end_date" value="{{ request('cloud_end_date') }}">

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Employee</label>
                    <select name="employee_id" class="w-full border rounded px-3 py-2 text-sm" @role('Employee') disabled @endrole>
                        <option value="">-- All Employees --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(request('employee_id') == $employee->id)>
                                {{ $employee->getFullName() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Device</label>
                    <select name="device_id" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- All Devices --</option>
                        @foreach ($devices as $device)
                            <option value="{{ $device->id }}" @selected(request('device_id') == $device->id)>
                                {{ $device->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- All Status --</option>
                        <option value="In" @selected(request('status') == 'In')>Check In</option>
                        <option value="Out" @selected(request('status') == 'Out')>Check Out</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">Filter</button>
                    <a id="reset-filters" href="{{ route('attendance-logs.index') }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded text-sm font-medium">Reset</a>
                </div>

                <form method="GET" action="{{ route('attendance-logs.export') }}" class="flex-shrink-0">
                    <input type="hidden" name="device_id" value="{{ request('device_id') }}">
                    <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm font-medium">
                        📥 Export
                    </button>
                </form>
            </form>
        </div>
        
        <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Date & Time</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Employee</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Badge #</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Device</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
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
                            @if ($log->device)
                                <a href="{{ route('attendance-logs.by-device', $log->device) }}" class="text-blue-500 hover:underline">
                                    {{ $log->device->name }}
                                </a>
                            @else
                                <span class="text-gray-400">No Device</span>
                            @endif
                        </td>
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
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('attendance-logs.show', $log) }}" class="text-blue-500 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No attendance logs found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @role('Superadmin')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const resetButton = document.getElementById('reset-filters');
            const manualEntryCard = document.getElementById('manual-entry-card');
            if (!resetButton || !manualEntryCard) return;

            const key = 'manual-entry-reset-clicks';
            manualEntryCard.classList.add('hidden');
            sessionStorage.setItem(key, '0');

            resetButton.addEventListener('click', () => {
                const next = (parseInt(sessionStorage.getItem(key) || '0', 10) + 1);
                sessionStorage.setItem(key, next);
                if (next % 3 === 0) {
                    manualEntryCard.classList.remove('hidden');
                } else {
                    manualEntryCard.classList.add('hidden');
                }
            });
        });
    </script>
    @endrole

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cloudForm = document.querySelector('[data-cloud-load-form]');
            const cloudButton = document.querySelector('[data-cloud-load-button]');
            if (!cloudForm || !cloudButton) return;

            cloudForm.addEventListener('submit', () => {
                cloudButton.setAttribute('disabled', 'disabled');
                cloudButton.classList.add('opacity-60', 'cursor-not-allowed');
                const spinner = cloudButton.querySelector('svg');
                if (spinner) spinner.classList.remove('hidden');
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const syncForm = document.querySelector('[data-cloud-sync-form]');
            const syncButton = document.querySelector('[data-cloud-sync-button]');
            if (!syncForm || !syncButton) return;

            syncForm.addEventListener('submit', () => {
                syncButton.setAttribute('disabled', 'disabled');
                syncButton.classList.add('opacity-60', 'cursor-not-allowed');
                const spinner = syncButton.querySelector('svg');
                if (spinner) spinner.classList.remove('hidden');
            });
        });
    </script>

    <!-- Pagination -->
    <div class="mt-6 px-4 lg:px-8">
        {{ $logs->links() }}
    </div>

    @role('Admin|Superadmin')
    <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-400 mx-4 lg:mx-8 mb-6 mt-6">
        <div class="bg-gray-50 border-b border-gray-300 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Cloud Attendance Logs</h3>
            <p class="text-sm text-gray-600">Latest 50 logs from cloud server.</p>
        </div>

        <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('attendance-logs.index') }}" data-cloud-load-form>
                <input type="hidden" name="cloud_load" value="1">
                <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                <input type="hidden" name="device_id" value="{{ request('device_id') }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-medium inline-flex items-center gap-2" data-cloud-load-button>
                    <svg class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    <span>Load Cloud Logs</span>
                </button>
            </form>

            <form method="POST" action="{{ route('attendance-logs.sync-cloud') }}" data-cloud-sync-form>
                @csrf
                <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                <input type="hidden" name="device_id" value="{{ request('device_id') }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded text-sm font-medium inline-flex items-center gap-2" data-cloud-sync-button>
                    <svg class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    <span>Sync Local to Cloud</span>
                </button>
            </form>
        </div>

        @if (request()->boolean('cloud_load'))
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <form method="GET" action="{{ route('attendance-logs.index') }}" class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="cloud_load" value="1">
                <input type="hidden" name="employee_id" value="{{ request('employee_id') }}">
                <input type="hidden" name="device_id" value="{{ request('device_id') }}">
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Badge #</label>
                    <input type="text" name="cloud_badge_number" value="{{ request('cloud_badge_number') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Device ID</label>
                    <input type="text" name="cloud_device_id" value="{{ request('cloud_device_id') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Start Date</label>
                    <input type="date" name="cloud_start_date" value="{{ request('cloud_start_date') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">End Date</label>
                    <input type="date" name="cloud_end_date" value="{{ request('cloud_end_date') }}" class="w-full border rounded px-3 py-2 text-sm">
                </div>

                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="cloud_status" class="w-full border rounded px-3 py-2 text-sm">
                        <option value="">-- All Status --</option>
                        <option value="In" @selected(request('cloud_status') == 'In')>Check In</option>
                        <option value="Out" @selected(request('cloud_status') == 'Out')>Check Out</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium">Filter</button>
                    <a href="{{ route('attendance-logs.index', request()->except(['cloud_badge_number','cloud_device_id','cloud_status','cloud_start_date','cloud_end_date','cloud_page'])) }}" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded text-sm font-medium">Reset</a>
                </div>
            </form>
        </div>

        @if ($cloudError)
            <div class="px-6 py-4 text-sm text-red-600">{{ $cloudError }}</div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Date &amp; Time</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Badge #</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Device ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cloudLogs as $cloudLog)
                        @php
                            $cloudDateTime = $cloudLog->log_datetime
                                ? \Carbon\Carbon::parse($cloudLog->log_datetime)
                                : null;
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                @if ($cloudDateTime)
                                    <div class="text-sm font-medium">{{ $cloudDateTime->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $cloudDateTime->format('H:i:s') }}</div>
                                @else
                                    <span class="text-gray-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm font-mono">{{ $cloudLog->badge_number ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $cloudLog->device_id ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $cloudLog->status ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $cloudLog->punch_type ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No cloud attendance logs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4">
            {{ $cloudLogs->appends(request()->except('cloud_page'))->links() }}
        </div>
        @else
            <div class="px-6 py-6 text-sm text-gray-600">Click “Load Cloud Logs” to fetch records from the cloud server.</div>
        @endif
    </div>
    @endrole
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
</div>
</x-admin-layout>

