<x-admin-layout>
    <x-slot name="header">
        Add Manual Attendance Log
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <form method="POST" action="{{ route('attendance-logs.store') }}" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                        <select name="employee_id" id="employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                    {{ $employee->getFullName() }} ({{ $employee->badge_number ?? 'No Badge' }})
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="badge_number" class="block text-sm font-medium text-gray-700">Badge Number (if no employee)</label>
                        <input type="text" name="badge_number" id="badge_number" value="{{ old('badge_number') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('badge_number') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="log_date" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="log_date" id="log_date" value="{{ old('log_date', now()->toDateString()) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('log_date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="log_time" class="block text-sm font-medium text-gray-700">Time</label>
                        <input type="time" name="log_time" id="log_time" value="{{ old('log_time') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                        @error('log_time') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="In" @selected(old('status') === 'In')>In</option>
                            <option value="Out" @selected(old('status') === 'Out')>Out</option>
                        </select>
                        @error('status') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="punch_type" class="block text-sm font-medium text-gray-700">Punch Type</label>
                        <input type="text" name="punch_type" id="punch_type" value="{{ old('punch_type', 'Manual') }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        @error('punch_type') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="device_id" class="block text-sm font-medium text-gray-700">Device</label>
                    <select name="device_id" id="device_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- None --</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>
                                {{ $device->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('device_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('attendance-logs.index') }}" class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Save Log
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>