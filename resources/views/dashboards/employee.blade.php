<x-admin-layout>
    <x-slot name="header"></x-slot>

    <div class="min-h-screen bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-10 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">My Attendance</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $employee?->getFullName() ?? 'Employee' }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="rounded-lg shadow p-6 border border-green-300 bg-green-100">
                    <div class="text-sm text-green-800">Logs Today</div>
                    <div class="text-3xl font-semibold text-green-900 mt-2">{{ $todayLogs }}</div>
                </div>
                <div class="rounded-lg shadow p-6 border border-amber-300 bg-amber-100">
                    <div class="text-sm text-amber-800">Logs This Month</div>
                    <div class="text-3xl font-semibold text-amber-900 mt-2">{{ $monthLogs }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <a href="{{ route('activities.index') }}" class="rounded-lg shadow p-6 border border-blue-300 bg-blue-100 hover:bg-blue-200 transition">
                    <div class="text-sm text-blue-800">Quick Link</div>
                    <div class="text-xl font-semibold text-blue-900 mt-2">Activities</div>
                </a>
                <a href="{{ route('attendance-logs.daily-time-record') }}" class="rounded-lg shadow p-6 border border-purple-300 bg-purple-100 hover:bg-purple-200 transition">
                    <div class="text-sm text-purple-800">Quick Link</div>
                    <div class="text-xl font-semibold text-purple-900 mt-2">Daily Time Record</div>
                </a>
            </div>

        </div>
    </div>
</x-admin-layout>