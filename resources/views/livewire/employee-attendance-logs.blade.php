@if($employee->badge_number)
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-300">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Attendance Logs') }}</h3>
                <div class="flex items-center space-x-2">
                    <label for="perPage" class="text-sm font-medium text-gray-700">{{ __('Rows per page:') }}</label>
                    <select wire:model.live="perPage" id="perPage" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
            
            @if($logs->count() > 0)
                <div class="overflow-x-auto -mx-6">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date & Time') }}</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Punch Type') }}</th>
                                <th class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Device') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $log->log_datetime->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $log->status ?? '-' }}
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-900">
                                    {{ $log->punch_type ?? '-' }}
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-xs text-gray-900">
                                    @if($log->device)
                                        <a href="{{ route('devices.show', $log->device) }}" class="text-indigo-600 hover:text-indigo-900">
                                            {{ $log->device->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($logs->hasPages())
                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            @else
                <p class="text-gray-500 text-sm py-4">{{ __('No attendance logs found for this employee.') }}</p>
            @endif
        </div>
    </div>
@else
    <div class="bg-yellow-50 border border-yellow-200 rounded-md overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <p class="text-sm text-yellow-800">{{ __('No badge number assigned. Please edit the employee to add a badge number to view attendance logs.') }}</p>
        </div>
    </div>
@endif

