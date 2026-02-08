<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $device->name }}
            </h2>
            <div class="space-x-2">
                @role('Superadmin')
                <a href="{{ route('devices.edit', $device) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('devices.destroy', $device) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure?')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Delete') }}
                    </button>
                </form>
                @endrole
                <a href="{{ route('devices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Device Information & Connection Status Combined Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Device Information --}}
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-300">{{ __('Device Information') }}</h3>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Name') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $device->name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Model') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $device->model ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Serial Number') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $device->serial_number ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Location') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $device->location ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('IP Address') }}</td>
                                            <td class="px-4 py-3 text-gray-900 font-mono">{{ $device->ip_address }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Port') }}</td>
                                            <td class="px-4 py-3 text-gray-900 font-mono">{{ $device->port }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Status') }}</td>
                                            <td class="px-4 py-3 text-gray-900">
                                                @if ($device->is_active)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                        {{ __('Active') }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                        {{ __('Inactive') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-gray-700 w-1/3">{{ __('Last Updated') }}</td>
                                            <td class="px-4 py-3 text-gray-900">{{ $device->updated_at->format('M d, Y H:i:s') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Connection Test & Status --}}
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-300">{{ __('Connection Test') }}</h3>
                            
                            <button onclick="testConnection()" class="w-full mb-4 inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                {{ __('Test Connection') }}
                            </button>

                            <div id="connection-status" class="space-y-3">
                                <div class="p-3 rounded-md bg-gray-50 border border-gray-200">
                                    <p class="text-sm text-gray-600">Click button to test device connection</p>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Device Status') }}</h4>
                                <div id="device-status-info" class="mb-3 p-3 rounded-md bg-gray-50 border border-gray-200 text-sm">
                                    <p class="text-gray-600">Status information will appear here</p>
                                </div>
                                <button onclick="loadDeviceStatus()" class="w-full inline-flex justify-center items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ __('Load Status') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Download Attendance Logs Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6 pb-6 border-b">{{ __('Download Attendance Logs') }}</h3>
                    
                    {{-- Quick Data Download --}}
                    <div class="mb-6 pb-6 border-b">
                        <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('Quick Download') }}</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <button onclick="downloadUsers()" class="inline-flex justify-center items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Download Users') }}
                            </button>
                            <button onclick="downloadDeviceLogs()" class="inline-flex justify-center items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                {{ __('Download All Logs') }}
                            </button>
                        </div>
                    </div>
                    
                    {{-- Date Range Selection --}}
                    <div class="mb-6 pb-6 border-b">
                        <h4 class="text-md font-medium text-gray-900 mb-3">{{ __('Download by Date Range') }}</h4>
                    
                    <div id="download-error" style="display: none;" class="mb-4 p-3 rounded-md bg-red-50 border border-red-200">
                        <p class="text-sm text-red-800"><strong>⚠ Error:</strong> <span id="error-message"></span></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Start Date') }}</label>
                            <div class="relative">
                                <input type="text" id="start_date" name="start_date" placeholder="MM/DD/YYYY" value="{{ date('m/d/Y', strtotime('-30 days')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" autocomplete="off">
                                <button type="button" onclick="openStartDatePicker()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <div id="start_date_picker" class="hidden absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50" style="display: none;"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('End Date') }}</label>
                            <div class="relative">
                                <input type="text" id="end_date" name="end_date" placeholder="MM/DD/YYYY" value="{{ date('m/d/Y') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" autocomplete="off">
                                <button type="button" onclick="openEndDatePicker()" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                                <div id="end_date_picker" class="hidden absolute top-full left-0 mt-1 bg-white border border-gray-300 rounded-md shadow-lg z-50" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="flex items-end">
                            <button id="extract-btn" onclick="extractLogs()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                {{ __("Extract Logs") }}
                            </button>
                        </div>
                        <div class="flex items-end">
                            <button id="store-btn" onclick="storeSelectedLogs()" disabled class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __("Store Selected") }}
                            </button>
                        </div>
                        <div class="flex items-end">
                            <button id="store-all-btn" onclick="directStoreAllLogs()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __("Store All") }}
                            </button>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div id="download-progress-container" style="display: none;" class="mt-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">{{ __('Downloading and storing logs...') }}</span>
                            <span id="progress-percentage" class="text-sm font-medium text-gray-700">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="download-progress-bar" class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    {{-- Status Messages --}}
                    <div id="download-status" class="mt-4"></div>

                    {{-- Extracted Logs Preview --}}
                    <div id="logs-preview-container" style="display: none;" class="mt-6">
                        <div class="border-t pt-4">
                            <h4 class="text-md font-semibold text-gray-900 mb-3">Extracted Logs Preview</h4>
                            <div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                <p class="text-sm text-blue-800"><strong>Total logs found:</strong> <span id="logs-total">0</span></p>
                                <p class="text-sm text-blue-700">Showing <span id="logs-range-start">0</span>-<span id="logs-range-end">0</span> of <span id="logs-shown">0</span> records. <strong>Selected:</strong> <span id="logs-selected-count">0</span> logs. Click "Store Logs" to save selected logs to database.</p>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                <input type="checkbox" id="select-all-checkbox" onchange="toggleSelectAll()" class="h-4 w-4 rounded border-gray-300" />
                                            </th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Badge Number</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date/Time</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logs-table-body" class="bg-white divide-y divide-gray-200">
                                    </tbody>
                                </table>
                            </div>
                            
                            {{-- Pagination Controls --}}
                            <div id="pagination-controls" class="mt-4 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                                <div class="flex flex-1 justify-between sm:hidden">
                                    <button onclick="previousPage()" id="prev-btn-mobile" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Previous
                                    </button>
                                    <button onclick="nextPage()" id="next-btn-mobile" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                        Next
                                    </button>
                                </div>
                                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
                                            Showing page <span class="font-medium" id="current-page">1</span> of <span class="font-medium" id="total-pages">1</span>
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                            <button onclick="previousPage()" id="prev-btn" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                                <span class="sr-only">Previous</span>
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <button onclick="nextPage()" id="next-btn" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                                <span class="sr-only">Next</span>
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Device Management & Maintenance Sections --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Device Time Sync Section --}}
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ __('Device Time') }}
                            </h3>
                            
                            <div id="time-info" class="mb-4 p-3 rounded-md bg-blue-50 border border-blue-200">
                                <p class="text-sm text-gray-600">Device time information will appear here</p>
                            </div>

                            <div class="space-y-2">
                                <button onclick="getDeviceTime()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('Get Device Time') }}
                                </button>
                                <button onclick="syncTime()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ __('Sync Server Time') }}
                                </button>
                            </div>
                        </div>

                        {{-- Device Maintenance Section --}}
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ __('Device Maintenance') }}
                            </h3>
                            
                            <div class="space-y-2">
                                <button onclick="clearLogs()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    {{ __('Clear Device Logs') }}
                                </button>
                                <button onclick="restartDevice()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" onclick="return confirm('Are you sure you want to restart the device?')">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ __('Restart Device') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Attendance Logs --}}
            @if ($recentLogs->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Recent Attendance Logs') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Date Time') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Badge Number') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Status') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Punch Type') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($recentLogs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->log_datetime->format('M d, Y H:i:s') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                                {{ $log->badge_number ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->status ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $log->punch_type ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <p class="text-gray-500 text-center">{{ __('No attendance logs yet') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        let connectionStatus = false;

        const US_DATE_REGEX = /^(0?[1-9]|1[0-2])\/(0?[1-9]|[12]\d|3[01])\/(\d{4})$/;

        function parseUsDate(value) {
            if (!value) return null;
            const match = value.trim().match(US_DATE_REGEX);
            if (!match) return null;

            const [, m, d, y] = match;
            const month = m.padStart(2, '0');
            const day = d.padStart(2, '0');
            const year = y;
            const date = new Date(Number(year), Number(month) - 1, Number(day));

            // Guard against invalid dates like 02/30/2024
            if (date.getFullYear() !== Number(year) || date.getMonth() !== Number(month) - 1 || date.getDate() !== Number(day)) {
                return null;
            }

            // Build ISO date string without timezone shifts (toISOString uses UTC and can roll back a day)
            const iso = `${year}-${month}-${day}`;

            return {
                iso,
                display: `${month}/${day}/${year}`
            };
        }

        // Calendar picker functions
        function createCalendar(year, month) {
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const daysInMonth = lastDay.getDate();
            const startingDayOfWeek = firstDay.getDay();
            
            let html = `
                <div class="p-4 w-80" data-year="${year}" data-month="${month}">
                    <div class="flex justify-between items-center mb-4">
                        <button type="button" class="month-nav px-2 py-1 text-sm hover:bg-gray-100 rounded" data-offset="-1">◀</button>
                        <span class="font-semibold">${new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</span>
                        <button type="button" class="month-nav px-2 py-1 text-sm hover:bg-gray-100 rounded" data-offset="1">▶</button>
                    </div>
                    <table class="w-full text-center text-sm">
                        <thead>
                            <tr class="text-gray-600">
                                <th class="p-1">Su</th>
                                <th class="p-1">Mo</th>
                                <th class="p-1">Tu</th>
                                <th class="p-1">We</th>
                                <th class="p-1">Th</th>
                                <th class="p-1">Fr</th>
                                <th class="p-1">Sa</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            let day = 1;
            for (let week = 0; week < 6; week++) {
                html += '<tr>';
                for (let dow = 0; dow < 7; dow++) {
                    if (week === 0 && dow < startingDayOfWeek) {
                        html += '<td class="p-1"></td>';
                    } else if (day > daysInMonth) {
                        html += '<td class="p-1"></td>';
                    } else {
                        const dateStr = String(day).padStart(2, '0');
                        const monthStr = String(month + 1).padStart(2, '0');
                        const dateValue = `${monthStr}/${dateStr}/${year}`;
                        html += `<td class="p-1"><button type="button" class="date-btn w-full p-1 text-sm rounded hover:bg-blue-500 hover:text-white" data-date="${dateValue}">${day}</button></td>`;
                        day++;
                    }
                }
                html += '</tr>';
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            return html;
        }

        function openStartDatePicker() {
            const pickerDiv = document.getElementById('start_date_picker');
            const currentValue = document.getElementById('start_date').value;
            const parsed = parseUsDate(currentValue);
            
            let year = new Date().getFullYear();
            let month = new Date().getMonth();
            
            if (parsed) {
                const date = new Date(parsed.iso);
                year = date.getFullYear();
                month = date.getMonth();
            }
            
            pickerDiv.innerHTML = createCalendar(year, month);
            attachPickerEvents(pickerDiv);
            pickerDiv.style.display = pickerDiv.style.display === 'none' ? 'block' : 'none';
            pickerDiv.dataset.pickerType = 'start';
        }

        function openEndDatePicker() {
            const pickerDiv = document.getElementById('end_date_picker');
            const currentValue = document.getElementById('end_date').value;
            const parsed = parseUsDate(currentValue);
            
            let year = new Date().getFullYear();
            let month = new Date().getMonth();
            
            if (parsed) {
                const date = new Date(parsed.iso);
                year = date.getFullYear();
                month = date.getMonth();
            }
            
            pickerDiv.innerHTML = createCalendar(year, month);
            attachPickerEvents(pickerDiv);
            pickerDiv.style.display = pickerDiv.style.display === 'none' ? 'block' : 'none';
            pickerDiv.dataset.pickerType = 'end';
        }

        function changeMonth(offset, btn) {
            const pickerDiv = btn.closest('[id$="_date_picker"]');
            const calendarDiv = pickerDiv.querySelector('[data-year]');
            const year = parseInt(calendarDiv.dataset.year);
            const month = parseInt(calendarDiv.dataset.month);
            
            let newMonth = month + offset;
            let newYear = year;
            
            // Adjust for month overflow/underflow
            if (newMonth > 11) {
                newYear += Math.floor(newMonth / 12);
                newMonth = newMonth % 12;
            } else if (newMonth < 0) {
                newYear += Math.floor(newMonth / 12);
                newMonth = ((newMonth % 12) + 12) % 12;
            }
            
            pickerDiv.innerHTML = createCalendar(newYear, newMonth);
            attachPickerEvents(pickerDiv);
        }

        function selectDate(dateStr, btn) {
            // Find the picker div by traversing up the DOM
            const pickerDiv = btn.closest('[id$="_date_picker"]');
            const isStart = pickerDiv.id === 'start_date_picker';
            
            if (isStart) {
                document.getElementById('start_date').value = dateStr;
                document.getElementById('start_date_picker').style.display = 'none';
            } else {
                document.getElementById('end_date').value = dateStr;
                document.getElementById('end_date_picker').style.display = 'none';
            }
        }

        function attachPickerEvents(pickerDiv) {
            // Attach click handlers to month nav buttons
            pickerDiv.querySelectorAll('.month-nav').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const offset = parseInt(btn.dataset.offset);
                    changeMonth(offset, btn);
                });
            });

            // Attach click handlers to date buttons
            pickerDiv.querySelectorAll('.date-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const dateStr = btn.dataset.date;
                    selectDate(dateStr, btn);
                });
            });
        }

        // Close pickers when clicking outside
        document.addEventListener('click', function(event) {
            const startPicker = document.getElementById('start_date_picker');
            const endPicker = document.getElementById('end_date_picker');
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            
            if (!event.target.closest('#start_date') && !event.target.closest('#start_date_picker') && !event.target.closest('button[onclick="openStartDatePicker()"]')) {
                startPicker.style.display = 'none';
            }
            if (!event.target.closest('#end_date') && !event.target.closest('#end_date_picker') && !event.target.closest('button[onclick="openEndDatePicker()"]')) {
                endPicker.style.display = 'none';
            }
        });

        function testConnection() {
            const btn = event.target;
            btn.disabled = true;
            btn.textContent = 'Testing...';

            fetch('{{ route("devices.test-connection-existing", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    const statusDiv = document.getElementById('connection-status');
                    
                    if (data.success) {
                        connectionStatus = true;
                        statusDiv.innerHTML = `
                            <div class="p-3 rounded-md bg-green-50 border border-green-200">
                                <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                                <p class="text-sm text-green-700">${data.message}</p>
                            </div>
                        `;
                        document.getElementById('extract-btn').disabled = false;
                    } else {
                        connectionStatus = false;
                        statusDiv.innerHTML = `
                            <div class="p-3 rounded-md bg-red-50 border border-red-200">
                                <p class="text-sm text-red-800"><strong>✗ Failed</strong></p>
                                <p class="text-sm text-red-700">${data.message}</p>
                            </div>
                        `;
                        document.getElementById('extract-btn').disabled = true;
                    }
                })
                .catch(error => {
                    connectionStatus = false;
                    const statusDiv = document.getElementById('connection-status');
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error</strong></p>
                            <p class="text-sm text-red-700">${error.message}</p>
                        </div>
                    `;
                    document.getElementById('extract-btn').disabled = true;
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = 'Test Connection';
                });
        }

        let extractedLogsData = null;

        function extractLogs() {
            // Check connection status first
            if (!connectionStatus) {
                const errorDiv = document.getElementById('download-error');
                const errorMsg = document.getElementById('error-message');
                errorMsg.textContent = 'Device must be connected first. Click "Test Connection" button.';
                errorDiv.style.display = 'block';
                return;
            }

            const startInput = document.getElementById('start_date').value;
            const endInput = document.getElementById('end_date').value;
            const progressContainer = document.getElementById('download-progress-container');
            const progressBar = document.getElementById('download-progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');
            const statusDiv = document.getElementById('download-status');
            const errorDiv = document.getElementById('download-error');
            const extractBtn = document.getElementById('extract-btn');
            const storeBtn = document.getElementById('store-btn');
            const storeAllBtn = document.getElementById('store-all-btn');
            const logsPreviewContainer = document.getElementById('logs-preview-container');

            const startDateParsed = parseUsDate(startInput);
            const endDateParsed = parseUsDate(endInput);

            // Validate dates
            if (!startDateParsed || !endDateParsed) {
                const errorMsg = document.getElementById('error-message');
                errorMsg.textContent = 'Please enter valid dates in MM/DD/YYYY format';
                errorDiv.style.display = 'block';
                return;
            }

            if (new Date(startDateParsed.iso) > new Date(endDateParsed.iso)) {
                const errorMsg = document.getElementById('error-message');
                errorMsg.textContent = 'Start date cannot be after end date';
                errorDiv.style.display = 'block';
                return;
            }

            // Hide error, hide preview, show progress
            errorDiv.style.display = 'none';
            logsPreviewContainer.style.display = 'none';
            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressPercentage.textContent = '0%';
            statusDiv.innerHTML = '';
            extractBtn.disabled = true;
            storeBtn.disabled = true;
            storeAllBtn.disabled = true;

            // Simulate progress
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 25;
                if (progress > 85) progress = 85;
                progressBar.style.width = progress + '%';
                progressPercentage.textContent = Math.round(progress) + '%';
            }, 400);

            // Send extract request (no confirm)
            fetch('{{ route("devices.download-logs", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start_date: startDateParsed.iso,
                    end_date: endDateParsed.iso,
                    confirm: false
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                    progressPercentage.textContent = '100%';
                    
                    const extractedLogs = data.logs || data.logs_preview || [];
                    if (data.success && extractedLogs.length > 0) {
                        extractedLogsData = {
                            ...data,
                            logs: extractedLogs
                        };
                        displayLogsPreview(extractedLogs, data.logs_total ?? extractedLogs.length);
                        storeBtn.disabled = false;
                        storeAllBtn.disabled = false;
                        statusDiv.innerHTML = `
                            <div class="p-3 rounded-md bg-blue-50 border border-blue-200">
                                <p class="text-sm text-blue-800"><strong>✓ Logs extracted successfully</strong></p>
                                <p class="text-sm text-blue-700">Review the logs below, then click "Store Selected" or "Store All" to save them to database.</p>
                            </div>
                        `;
                    } else if (data.success) {
                        progressBar.style.width = '0%';
                        progressPercentage.textContent = '0%';
                        statusDiv.innerHTML = `
                            <div class="p-3 rounded-md bg-yellow-50 border border-yellow-200">
                                <p class="text-sm text-yellow-800"><strong>ℹ No logs found</strong></p>
                                <p class="text-sm text-yellow-700">${data.message || 'No logs found for the selected date range.'}</p>
                            </div>
                        `;
                        storeAllBtn.disabled = true;
                        storeBtn.disabled = true;
                    } else {
                        progressBar.style.width = '0%';
                        progressPercentage.textContent = '0%';
                        const errorMsg = document.getElementById('error-message');
                        errorMsg.textContent = data.message || 'No logs found';
                        errorDiv.style.display = 'block';
                        statusDiv.innerHTML = '';
                        storeAllBtn.disabled = true;
                        storeBtn.disabled = true;
                    }
                    extractBtn.disabled = false;
                })
                .catch(error => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '0%';
                    progressPercentage.textContent = '0%';
                    progressContainer.style.display = 'none';
                    const errorMsg = document.getElementById('error-message');
                    errorMsg.textContent = error.message;
                    errorDiv.style.display = 'block';
                    extractBtn.disabled = false;
                })
                .finally(() => {
                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                    }, 3000);
                });
        }

        // Pagination variables
        let allLogsData = [];
        let selectedLogs = new Set();
        let currentPage = 1;
        const logsPerPage = 10;

        function displayLogsPreview(logs, total) {
            const logsPreviewContainer = document.getElementById('logs-preview-container');
            const logsTotal = document.getElementById('logs-total');
            const logsShown = document.getElementById('logs-shown');

            // Store all logs data with unique IDs
            allLogsData = logs.map((log, index) => ({
                ...log,
                _id: index
            }));
            currentPage = 1;
            selectedLogs.clear();

            logsTotal.textContent = total;
            logsShown.textContent = logs.length;

            // Render first page
            renderLogsPage();
            updateSelectedCount();

            logsPreviewContainer.style.display = 'block';
        }

        function renderLogsPage() {
            const tableBody = document.getElementById('logs-table-body');
            const totalPages = Math.ceil(allLogsData.length / logsPerPage);
            const startIndex = (currentPage - 1) * logsPerPage;
            const endIndex = Math.min(startIndex + logsPerPage, allLogsData.length);
            const pageData = allLogsData.slice(startIndex, endIndex);

            // Update pagination info
            document.getElementById('current-page').textContent = currentPage;
            document.getElementById('total-pages').textContent = totalPages;
            document.getElementById('logs-range-start').textContent = startIndex + 1;
            document.getElementById('logs-range-end').textContent = endIndex;

            // Update pagination buttons
            const prevBtns = [document.getElementById('prev-btn'), document.getElementById('prev-btn-mobile')];
            const nextBtns = [document.getElementById('next-btn'), document.getElementById('next-btn-mobile')];
            
            prevBtns.forEach(btn => {
                if (btn) {
                    btn.disabled = currentPage === 1;
                    btn.className = currentPage === 1 
                        ? btn.className.replace('hover:bg-gray-50', '') + ' opacity-50 cursor-not-allowed'
                        : btn.className.replace(' opacity-50 cursor-not-allowed', '') + ' hover:bg-gray-50';
                }
            });
            
            nextBtns.forEach(btn => {
                if (btn) {
                    btn.disabled = currentPage === totalPages;
                    btn.className = currentPage === totalPages 
                        ? btn.className.replace('hover:bg-gray-50', '') + ' opacity-50 cursor-not-allowed'
                        : btn.className.replace(' opacity-50 cursor-not-allowed', '') + ' hover:bg-gray-50';
                }
            });

            // Render table rows
            tableBody.innerHTML = '';
            pageData.forEach((log, index) => {
                const globalIndex = startIndex + index;
                const logId = log._id;
                const isSelected = selectedLogs.has(logId);
                const row = document.createElement('tr');
                row.className = index % 2 === 0 ? 'bg-white' : 'bg-gray-50';
                row.innerHTML = `
                    <td class="px-4 py-2 text-sm">
                        <input type="checkbox" class="log-checkbox h-4 w-4 rounded border-gray-300" data-log-id="${logId}" ${isSelected ? 'checked' : ''} onchange="updateLogSelection(${logId}, this.checked)" />
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900">${globalIndex + 1}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${log.user_id}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">${log.record_time}</td>
                    <td class="px-4 py-2 text-sm">
                        <span class="px-2 py-1 text-xs font-semibold rounded ${
                            log.status === 'In' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">${log.status}</span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-600">${log.punch_type}</td>
                `;
                tableBody.appendChild(row);
            });

            // Update select-all checkbox state
            updateSelectAllCheckboxState();
        }

        function updateLogSelection(logId, isChecked) {
            if (isChecked) {
                selectedLogs.add(logId);
            } else {
                selectedLogs.delete(logId);
            }
            updateSelectedCount();
            updateSelectAllCheckboxState();
        }

        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const isChecked = selectAllCheckbox.checked;

            if (isChecked) {
                // Select all logs
                allLogsData.forEach(log => selectedLogs.add(log._id));
            } else {
                // Deselect all logs
                selectedLogs.clear();
            }

            updateSelectedCount();
            renderLogsPage();
        }

        function updateSelectAllCheckboxState() {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const totalPages = Math.ceil(allLogsData.length / logsPerPage);
            const startIndex = (currentPage - 1) * logsPerPage;
            const endIndex = Math.min(startIndex + logsPerPage, allLogsData.length);
            const pageData = allLogsData.slice(startIndex, endIndex);

            // Check if all logs on current page are selected
            const allPageLogsSelected = pageData.length > 0 && pageData.every(log => selectedLogs.has(log._id));
            selectAllCheckbox.checked = allPageLogsSelected;
            selectAllCheckbox.indeterminate = selectedLogs.size > 0 && selectedLogs.size < allLogsData.length;
        }

        function updateSelectedCount() {
            document.getElementById('logs-selected-count').textContent = selectedLogs.size;
        }

        function previousPage() {
            if (currentPage > 1) {
                currentPage--;
                renderLogsPage();
            }
        }

        function nextPage() {
            const totalPages = Math.ceil(allLogsData.length / logsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderLogsPage();
            }
        }

        function storeLogs() {
            return storeSelectedLogs();
        }

        function directStoreAllLogs() {
            return storeAllLogs();
        }

        function storeSelectedLogs() {
            if (!extractedLogsData) {
                alert('Please extract logs first');
                return;
            }

            if (selectedLogs.size === 0) {
                alert('Please select at least one log to store');
                return;
            }

            // Get selected log data
            const selectedLogsData = allLogsData.filter(log => selectedLogs.has(log._id));

            performStoreLogs(selectedLogsData, 'selected');
        }

        function storeAllLogs() {
            if (!extractedLogsData) {
                alert('Please extract logs first');
                return;
            }

            const allLogs = extractedLogsData.logs || [];
            if (allLogs.length === 0) {
                alert('No logs available to store');
                return;
            }

            performStoreLogs(allLogs, 'all');
        }

        function performStoreLogs(logsPayload, mode, existingProgressInterval) {
            const progressContainer = document.getElementById('download-progress-container');
            const progressBar = document.getElementById('download-progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');
            const statusDiv = document.getElementById('download-status');
            const errorDiv = document.getElementById('download-error');
            const storeBtn = document.getElementById('store-btn');
            const storeAllBtn = document.getElementById('store-all-btn');
            const extractBtn = document.getElementById('extract-btn');

            // Only initialize progress if not already running (for Store Selected)
            let progressInterval = existingProgressInterval;
            if (!progressInterval) {
                errorDiv.style.display = 'none';
                progressContainer.style.display = 'block';
                progressBar.style.width = '0%';
                progressPercentage.textContent = '0%';
                statusDiv.innerHTML = '';
                storeBtn.disabled = true;
                storeAllBtn.disabled = true;
                extractBtn.disabled = true;

                let progress = 0;
                progressInterval = setInterval(() => {
                    progress += Math.random() * 25;
                    if (progress > 85) progress = 85;
                    progressBar.style.width = progress + '%';
                    progressPercentage.textContent = Math.round(progress) + '%';
                }, 400);
            }

            const payload = {
                start_date: extractedLogsData.start_date,
                end_date: extractedLogsData.end_date,
                confirm: true
            };

            if (logsPayload && Array.isArray(logsPayload)) {
                payload.logs = logsPayload;
            }

            fetch('{{ route("devices.download-logs", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                    progressPercentage.textContent = '100%';
                    
                    if (data.success) {
                        statusDiv.innerHTML = `
                            <div class="p-3 rounded-md bg-green-50 border border-green-200">
                                <p class="text-sm text-green-800"><strong>✓ Success (${mode === 'all' ? 'All' : 'Selected'} stored)</strong></p>
                                <p class="text-sm text-green-700">${data.message}</p>
                                <p class="text-sm text-green-700 mt-1"><strong>Logs stored:</strong> ${data.logs_count}</p>
                            </div>
                        `;
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        progressBar.style.width = '0%';
                        progressPercentage.textContent = '0%';
                        const errorMsg = document.getElementById('error-message');
                        errorMsg.textContent = data.message;
                        errorDiv.style.display = 'block';
                        statusDiv.innerHTML = '';
                        storeBtn.disabled = false;
                        storeAllBtn.disabled = false;
                        extractBtn.disabled = false;
                    }
                })
                .catch(error => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '0%';
                    progressPercentage.textContent = '0%';
                    progressContainer.style.display = 'none';
                    const errorMsg = document.getElementById('error-message');
                    errorMsg.textContent = error.message;
                    errorDiv.style.display = 'block';
                    storeBtn.disabled = false;
                    storeAllBtn.disabled = false;
                    extractBtn.disabled = false;
                })
                .finally(() => {
                    setTimeout(() => {
                        progressContainer.style.display = 'none';
                    }, 3000);
                });
        }

        // Get device time
        function getDeviceTime() {
            const timeInfoDiv = document.getElementById('time-info');
            timeInfoDiv.innerHTML = '<p class="text-sm text-gray-600">Loading device time...</p>';

            fetch('{{ route("devices.device-time", $device) }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let deviceTimeStr = data.device_time;
                    let serverTimeStr = data.server_time;
                    
                    // Parse time strings more carefully
                    let deviceDate = null;
                    let serverDate = null;
                    
                    try {
                        // Parse device time
                        deviceDate = new Date(deviceTimeStr);
                        if (isNaN(deviceDate)) {
                            // Try manual parsing for Y-m-d H:i:s format
                            const parts = deviceTimeStr.match(/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/);
                            if (parts) {
                                deviceDate = new Date(parseInt(parts[1]), parseInt(parts[2]) - 1, parseInt(parts[3]), 
                                                     parseInt(parts[4]), parseInt(parts[5]), parseInt(parts[6]));
                            }
                        }
                    } catch (e) {
                        console.warn('Could not parse device time:', deviceTimeStr);
                    }
                    
                    try {
                        // Parse server time
                        serverDate = new Date(serverTimeStr);
                        if (isNaN(serverDate)) {
                            const parts = serverTimeStr.match(/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/);
                            if (parts) {
                                serverDate = new Date(parseInt(parts[1]), parseInt(parts[2]) - 1, parseInt(parts[3]), 
                                                     parseInt(parts[4]), parseInt(parts[5]), parseInt(parts[6]));
                            }
                        }
                    } catch (e) {
                        console.warn('Could not parse server time:', serverTimeStr);
                    }
                    
                    let html = `
                        <div class="space-y-3 text-sm">
                            <div class="border-l-4 border-blue-500 pl-3">
                                <p><strong>Device Time:</strong> <code class="bg-gray-100 px-2 py-1 rounded">`;
                    
                    if (deviceDate && !isNaN(deviceDate)) {
                        html += deviceDate.toLocaleString();
                    } else {
                        html += deviceTimeStr;
                    }
                    
                    html += `</code></p>`;
                    
                    if (data.device_timezone) {
                        html += `<p class="text-gray-600 text-xs">Timezone: <strong>${data.device_timezone}</strong></p>`;
                    }
                    
                    html += `</div>
                    
                            <div class="border-l-4 border-green-500 pl-3">
                                <p><strong>Server Time:</strong> <code class="bg-gray-100 px-2 py-1 rounded">`;
                    
                    if (serverDate && !isNaN(serverDate)) {
                        html += serverDate.toLocaleString();
                    } else {
                        html += serverTimeStr;
                    }
                    
                    html += `</code></p>`;
                    
                    if (data.server_timezone) {
                        html += `<p class="text-gray-600 text-xs">Timezone: <strong>${data.server_timezone}</strong></p>`;
                    }
                    
                    html += `</div>`;
                    
                    // Show time difference if available
                    if (data.time_difference_seconds !== null && data.time_difference_seconds !== undefined) {
                        const diff = Math.abs(data.time_difference_seconds);
                        const sign = data.time_difference_seconds >= 0 ? '+' : '-';
                        const hours = Math.floor(diff / 3600);
                        const minutes = Math.floor((diff % 3600) / 60);
                        const seconds = diff % 60;
                        
                        let diffStr = '';
                        if (hours > 0) diffStr += hours + 'h ';
                        if (minutes > 0) diffStr += minutes + 'm ';
                        diffStr += seconds + 's';
                        
                        const diffClass = Math.abs(data.time_difference_seconds) < 2 ? 'text-green-600' : 'text-orange-600';
                        html += `<p class="text-xs ${diffClass} border-t pt-2"><strong>Time Difference:</strong> ${sign}${diffStr}</p>`;
                    }
                    
                    if (data.note) {
                        html += `<p class="text-blue-600 italic text-xs mt-2 p-2 bg-blue-50 rounded"><em>Note: ${data.note}</em></p>`;
                    }
                    
                    // Show raw device time if available (for debugging)
                    if (data.device_time_raw && data.device_time_raw !== data.device_time) {
                        html += `<p class="text-gray-500 text-xs mt-2"><strong>Raw Device Response:</strong> ${data.device_time_raw}</p>`;
                    }
                    
                    html += `</div>`;
                    timeInfoDiv.innerHTML = html;
                } else {
                    let errorMsg = data.message || 'Unknown error';
                    
                    let html = `
                        <div class="space-y-2 text-sm">
                            <p class="text-red-600"><strong>Error:</strong> ${errorMsg}</p>
                    `;
                    
                    // Add device info if available
                    if (data.device_ip) {
                        html += `<p class="text-gray-600 text-xs"><strong>Device:</strong> ${data.device_ip}:${data.device_port || '4370'}</p>`;
                    }
                    
                    // Add error detail if available
                    if (data.error_detail) {
                        html += `<p class="text-gray-600 text-xs"><strong>Details:</strong> ${data.error_detail}</p>`;
                    }
                    
                    // Add troubleshooting steps if available
                    if (data.troubleshooting && Array.isArray(data.troubleshooting)) {
                        html += `
                            <p class="text-gray-600 text-xs">
                                <strong>Troubleshooting:</strong>
                                <ul class="list-disc ml-4">
                        `;
                        data.troubleshooting.forEach(step => {
                            html += `<li>${step}</li>`;
                        });
                        html += `
                                </ul>
                            </p>
                        `;
                    }
                    
                    html += `</div>`;
                    timeInfoDiv.innerHTML = html;
                }
            })
            .catch(error => {
                timeInfoDiv.innerHTML = `
                    <div class="space-y-2 text-sm text-red-600">
                        <p><strong>Error:</strong> ${error.message}</p>
                        <p class="text-gray-600 text-xs">Failed to reach the server. Please check your connection.</p>
                    </div>
                `;
            });
        }

        // Sync server time to device
        function syncTime() {
            if (!confirm('Sync server time to device? This will change the device\'s system time.')) {
                return;
            }

            const timeInfoDiv = document.getElementById('time-info');
            timeInfoDiv.innerHTML = '<p class="text-sm text-blue-600">Synchronizing time...</p>';

            fetch('{{ route("devices.sync-time", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeInfoDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                            <p class="text-sm text-green-700">${data.message}</p>
                            <p class="text-sm text-green-700 mt-1"><strong>Device Time:</strong> ${data.device_time}</p>
                        </div>
                    `;
                } else {
                    timeInfoDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error:</strong></p>
                            <p class="text-sm text-red-700">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                timeInfoDiv.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Download users from device
        function downloadUsers() {
            const timeInfoDiv = document.getElementById('time-info');
            timeInfoDiv.innerHTML = '<p class="text-sm text-blue-600">Downloading users...</p>';

            fetch('{{ route("devices.download-users", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    timeInfoDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                            <p class="text-sm text-green-700">${data.message}</p>
                        </div>
                    `;
                } else {
                    timeInfoDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error:</strong></p>
                            <p class="text-sm text-red-700">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                timeInfoDiv.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Download logs from device
        function downloadDeviceLogs() {
            const statusDiv = document.getElementById('download-status');
            const progressContainer = document.getElementById('download-progress-container');
            const progressBar = document.getElementById('download-progress-bar');
            const progressPercentage = document.getElementById('progress-percentage');
            const startInput = document.getElementById('start_date').value;
            const endInput = document.getElementById('end_date').value;
            const errorDiv = document.getElementById('download-error');
            const errorMsg = document.getElementById('error-message');

            // Validate dates
            const startDateParsed = parseUsDate(startInput);
            const endDateParsed = parseUsDate(endInput);

            if (!startDateParsed || !endDateParsed) {
                errorMsg.textContent = 'Please enter valid dates in MM/DD/YYYY format';
                errorDiv.style.display = 'block';
                return;
            }

            if (new Date(startDateParsed.iso) > new Date(endDateParsed.iso)) {
                errorMsg.textContent = 'Start date cannot be after end date';
                errorDiv.style.display = 'block';
                return;
            }

            errorDiv.style.display = 'none';

            progressContainer.style.display = 'block';
            progressBar.style.width = '0%';
            progressPercentage.textContent = '0%';
            statusDiv.innerHTML = '';

            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 30;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                progressPercentage.textContent = Math.round(progress) + '%';
            }, 300);

            fetch('{{ route("devices.download-device-logs", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    start_date: startDateParsed.iso,
                    end_date: endDateParsed.iso,
                })
            })
            .then(response => response.json())
            .then(data => {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                progressPercentage.textContent = '100%';

                if (data.success) {
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                            <p class="text-sm text-green-700">${data.message}</p>
                            <p class="text-sm text-green-700 mt-1"><strong>Logs downloaded:</strong> ${data.logs_count}</p>
                            <p class="text-sm text-green-700 mt-1"><strong>Date range:</strong> ${startDateParsed.display} to ${endDateParsed.display}</p>
                        </div>
                    `;
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    progressBar.style.width = '0%';
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error:</strong></p>
                            <p class="text-sm text-red-700">${data.message}</p>
                            ${data.device_logs_total !== undefined ? `<p class="text-sm text-red-700 mt-1">Device returned ${data.device_logs_total} log(s) total.</p>` : ''}
                        </div>
                    `;
                }
            })
            .catch(error => {
                clearInterval(progressInterval);
                progressBar.style.width = '0%';
                statusDiv.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Clear device logs
        function clearLogs() {
            if (!confirm('Are you sure you want to clear all logs from the device? This action cannot be undone.')) {
                return;
            }

            const statusDiv = document.getElementById('download-status');
            statusDiv.innerHTML = '<p class="text-sm text-blue-600">Clearing device logs...</p>';

            fetch('{{ route("devices.clear-logs", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                            <p class="text-sm text-green-700">${data.message}</p>
                        </div>
                    `;
                } else {
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error:</strong></p>
                            <p class="text-sm text-red-700">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusDiv.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Restart device
        function restartDevice() {
            if (!confirm('Are you sure you want to restart the device? This may interrupt current operations.')) {
                return;
            }

            const statusDiv = document.getElementById('download-status');
            statusDiv.innerHTML = '<p class="text-sm text-blue-600">Sending restart command...</p>';

            fetch('{{ route("devices.restart", $device) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-green-50 border border-green-200">
                            <p class="text-sm text-green-800"><strong>✓ Success</strong></p>
                            <p class="text-sm text-green-700">${data.message}</p>
                        </div>
                    `;
                } else {
                    statusDiv.innerHTML = `
                        <div class="p-3 rounded-md bg-red-50 border border-red-200">
                            <p class="text-sm text-red-800"><strong>✗ Error:</strong></p>
                            <p class="text-sm text-red-700">${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                statusDiv.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Load device status
        function loadDeviceStatus() {
            const statusInfo = document.getElementById('device-status-info');
            statusInfo.innerHTML = '<p class="text-sm text-gray-600">Loading status...</p>';

            fetch('{{ route("devices.status", $device) }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                let html = '<div class="space-y-3 text-sm">';
                
                // Connection status badge
                const statusBadges = {
                    'online_protocol_ok': { label: 'Online (Protocol OK)', color: 'green' },
                    'online_no_protocol': { label: 'Online (No Protocol)', color: 'yellow' },
                    'reachable_port_closed': { label: 'Reachable (Port Closed)', color: 'orange' },
                    'offline': { label: 'Offline', color: 'red' },
                    'unknown': { label: 'Unknown', color: 'gray' },
                    'error': { label: 'Error', color: 'red' },
                };
                
                const badge = statusBadges[data.connection.status] || statusBadges['unknown'];
                const colorClass = {
                    'green': 'bg-green-100 text-green-800',
                    'yellow': 'bg-yellow-100 text-yellow-800',
                    'orange': 'bg-orange-100 text-orange-800',
                    'red': 'bg-red-100 text-red-800',
                    'gray': 'bg-gray-100 text-gray-800',
                }[badge.color];
                
                html += `<div>`;
                html += `<strong>Status:</strong> <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">${badge.label}</span>`;
                html += `</div>`;
                
                // Ping status
                html += `<div><strong>Ping:</strong> ${data.connection.ping ? '✓ Reachable' : '✗ Unreachable'}</div>`;
                html += `<div><strong>Port ${data.port}:</strong> ${data.connection.socket ? '✓ Open' : '✗ Closed'}</div>`;
                html += `<div><strong>Protocol:</strong> ${data.connection.protocol ? '✓ OK' : '✗ Not Responding'}</div>`;
                
                if (data.device_info) {
                    html += `<hr class="my-2">`;
                    html += `<div><strong>Vendor:</strong> ${data.device_info.vendor}</div>`;
                    html += `<div><strong>Model:</strong> ${data.device_info.model}</div>`;
                    html += `<div><strong>Version:</strong> ${data.device_info.version}</div>`;
                    html += `<div><strong>Serial:</strong> ${data.device_info.serial}</div>`;
                }
                
                html += `<div class="text-xs text-gray-500 mt-2">Last checked: ${new Date(data.connection.last_checked).toLocaleString()}</div>`;
                html += '</div>';
                
                statusInfo.innerHTML = html;
            })
            .catch(error => {
                statusInfo.innerHTML = `<p class="text-sm text-red-600"><strong>Error:</strong> ${error.message}</p>`;
            });
        }

        // Load device status on page load
        window.addEventListener('load', function() {
            loadDeviceStatus();
        });
    </script>
</x-admin-layout>

