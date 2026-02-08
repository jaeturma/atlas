<x-admin-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-10 py-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Department Overview</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ $department?->name ?? 'No department assigned' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('attendance-logs.daily-time-record') }}" class="inline-flex items-center justify-center px-4 py-2 border-[0.25px] border-blue-200 outline outline-[0.125px] outline-blue-200/50 rounded-md text-sm font-medium text-blue-900 bg-blue-100 hover:bg-blue-200">
                        DTR
                    </a>
                    <a href="{{ route('attendance-logs.index') }}" class="inline-flex items-center justify-center px-4 py-2 border-[0.25px] border-green-200 outline outline-[0.125px] outline-green-200/50 rounded-md text-sm font-medium text-green-900 bg-green-100 hover:bg-green-200">
                        Logs
                    </a>
                    <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center px-4 py-2 border-[0.25px] border-amber-200 outline outline-[0.125px] outline-amber-200/50 rounded-md text-sm font-medium text-amber-900 bg-amber-100 hover:bg-amber-200">
                        Employee
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <div class="rounded-lg shadow p-6 border border-blue-300 bg-blue-100">
                    <div class="text-sm text-blue-800">Employees in Department</div>
                    <div class="text-3xl font-semibold text-blue-900 mt-2">{{ $employeeCount }}</div>
                </div>
                <div class="rounded-lg shadow p-6 border border-green-300 bg-green-100">
                    <div class="text-sm text-green-800">Logs Today</div>
                    <div class="text-3xl font-semibold text-green-900 mt-2">{{ $todayLogs }}</div>
                </div>
                <div class="rounded-lg shadow p-6 border border-amber-300 bg-amber-100">
                    <div class="text-sm text-amber-800">Logs This Month</div>
                    <div class="text-3xl font-semibold text-amber-900 mt-2">{{ $monthLogs }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-blue-50">
                        <h2 class="text-lg font-semibold text-blue-900">Recent Department Activities</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50 block">
                                <tr class="table w-full table-fixed">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 block max-h-[12.5rem] overflow-y-auto">
                                @forelse ($currentActivities as $activity)
                                    <tr class="table w-full table-fixed">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $activity->employee?->getFullName() ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $activity->activity_type ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="table w-full table-fixed">
                                        <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No current activities.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow border border-gray-200">
                    <div class="p-4 border-b border-gray-200 bg-emerald-50">
                        <h2 class="text-lg font-semibold text-emerald-900">Today's Logs</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 table-fixed">
                            <thead class="bg-gray-50 block">
                                <tr class="table w-full table-fixed">
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 block max-h-[12.5rem] overflow-y-auto">
                                @forelse ($todaysLogs as $log)
                                    <tr class="table w-full table-fixed">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $log->employee?->getFullName() ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            {{ $log->log_time }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="table w-full table-fixed">
                                        <td colspan="2" class="px-4 py-6 text-center text-sm text-gray-500">No logs today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>