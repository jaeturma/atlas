<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf
                        @method('PUT')

                        @foreach ($settings as $group => $groupSettings)
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-gray-700 mb-4 pb-2 border-b">
                                    {{ ucfirst($group) }} Settings
                                </h3>

                                @if ($group === 'devices')
                                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded">
                                        <h4 class="font-semibold text-blue-800 mb-2">Push Protocol Configuration (WL10, ADMS, Cloud Devices)</h4>
                                        <p class="text-sm text-gray-700 mb-2">
                                            For devices that use ADMS/Cloud/Push protocol (like WL10), configure the device to push logs to this URL:
                                        </p>
                                        <div class="mt-2">
                                            <strong class="text-blue-800">Push Endpoint:</strong>
                                            <code class="ml-2 px-2 py-1 bg-gray-100 rounded text-sm">
                                                {{ url('/api/attendance/push') }}
                                            </code>
                                        </div>
                                        <div class="mt-3 text-sm text-gray-600">
                                            <strong>Important:</strong>
                                            <ul class="list-disc ml-5 mt-1 space-y-1">
                                                <li>This URL must be publicly accessible (not localhost)</li>
                                                <li>Configure your WL10 device to push data to this endpoint</li>
                                                <li>Ensure the device has the correct <strong>Serial Number</strong> configured (see Device Management)</li>
                                                <li>Test connectivity: <a href="{{ url('/api/attendance/push/health') }}" target="_blank" class="text-blue-600 hover:underline">{{ url('/api/attendance/push/health') }}</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                @endif

                                @foreach ($groupSettings as $setting)
                                    <div class="mb-4">
                                        <label for="{{ $setting->key }}" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                                        </label>
                                        
                                        @if ($setting->description)
                                            <p class="text-xs text-gray-500 mb-2">{{ $setting->description }}</p>
                                        @endif

                                        @if ($setting->type === 'textarea')
                                            <textarea
                                                name="settings[{{ $setting->key }}]"
                                                id="{{ $setting->key }}"
                                                rows="3"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @elseif ($setting->type === 'boolean')
                                            <select
                                                name="settings[{{ $setting->key }}]"
                                                id="{{ $setting->key }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="1" {{ old('settings.' . $setting->key, $setting->value) == '1' ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ old('settings.' . $setting->key, $setting->value) == '0' ? 'selected' : '' }}>No</option>
                                            </select>
                                        @else
                                            <input
                                                type="text"
                                                name="settings[{{ $setting->key }}]"
                                                id="{{ $setting->key }}"
                                                value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                @if ($setting->key === 'push_server_url') readonly @endif
                                            >
                                        @endif

                                        @error('settings.' . $setting->key)
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        <div class="flex items-center justify-end mt-6">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
