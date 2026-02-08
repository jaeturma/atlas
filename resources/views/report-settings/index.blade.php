<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Report Settings</h1>
                <p class="mt-1 text-sm text-gray-600">Configure time ranges for attendance report generation</p>
            </div>

            <!-- Group Selector -->
            <div class="mb-6 bg-white shadow-md rounded-lg p-6">
                <form method="GET" action="{{ route('report-settings.index') }}">
                    <div class="flex items-center gap-4">
                        <label for="group_id" class="text-sm font-medium text-gray-700">Employee Group:</label>
                        <select name="group_id" id="group_id" onchange="this.form.submit()" class="flex-1 max-w-md px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}" @selected($selectedGroupId == $group->id)>
                                    {{ $group->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Settings Form -->
            <form method="POST" action="{{ route('report-settings.update') }}" class="bg-white shadow-md rounded-lg p-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="group_id" value="{{ $selectedGroupId }}">

                <!-- Official Office Hours -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Official Office Hours</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $officialArrivalSetting = ($settings->get('General', collect()))->firstWhere('key', 'official_arrival');
                            $officialDepartureSetting = ($settings->get('General', collect()))->firstWhere('key', 'official_departure');
                        @endphp
                        @if ($officialArrivalSetting)
                            <div>
                                <label for="{{ $officialArrivalSetting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    Official Arrival
                                </label>
                                <input
                                    type="time"
                                    name="settings[{{ $officialArrivalSetting->key }}]"
                                    id="{{ $officialArrivalSetting->key }}"
                                    value="{{ $officialArrivalSetting->value }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @if ($officialArrivalSetting->description)
                                    <p class="mt-1 text-xs text-gray-500">{{ $officialArrivalSetting->description }}</p>
                                @endif
                            </div>
                        @endif
                        @if ($officialDepartureSetting)
                            <div>
                                <label for="{{ $officialDepartureSetting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    Official Departure
                                </label>
                                <input
                                    type="time"
                                    name="settings[{{ $officialDepartureSetting->key }}]"
                                    id="{{ $officialDepartureSetting->key }}"
                                    value="{{ $officialDepartureSetting->value }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @if ($officialDepartureSetting->description)
                                    <p class="mt-1 text-xs text-gray-500">{{ $officialDepartureSetting->description }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- AM Settings -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Morning (AM) Schedule</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($settings->get('AM', []) as $setting)
                            <div>
                                <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $setting->label }}
                                </label>
                                <input 
                                    type="time" 
                                    name="settings[{{ $setting->key }}]" 
                                    id="{{ $setting->key }}" 
                                    value="{{ $setting->value }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                @if ($setting->description)
                                    <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- PM Settings -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Afternoon (PM) Schedule</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($settings->get('PM', []) as $setting)
                            @if ($setting->key !== 'pm_arrival_interval')
                                <div>
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $setting->label }}
                                    </label>
                                    <input 
                                        type="time" 
                                        name="settings[{{ $setting->key }}]" 
                                        id="{{ $setting->key }}" 
                                        value="{{ $setting->value }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    @if ($setting->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Interval Settings -->
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b pb-2">Break Time Settings</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ($settings->get('PM', []) as $setting)
                            @if ($setting->key === 'pm_arrival_interval')
                                <div>
                                    <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $setting->label }}
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <input 
                                            type="number" 
                                            name="settings[{{ $setting->key }}]" 
                                            id="{{ $setting->key }}" 
                                            value="{{ $setting->value }}"
                                            min="0"
                                            step="5"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <span class="text-sm text-gray-600">minutes</span>
                                    </div>
                                    @if ($setting->description)
                                        <p class="mt-1 text-xs text-gray-500">{{ $setting->description }}</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Time Format Information -->
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Time Format Information</h3>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>• Use 12-hour format (e.g., 05:00 for 5:00 AM, 01:00 for 1:00 PM)</li>
                        <li>• AM times: 12:00 AM (midnight) to 11:59 AM (before noon)</li>
                        <li>• PM times: 12:00 PM (noon) to 11:59 PM (before midnight)</li>
                        <li>• Example: 8:00 AM arrival, 12:00 PM lunch start, 1:00 PM back from lunch, 5:00 PM departure</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
