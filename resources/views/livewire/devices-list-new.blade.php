<div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-400">
    @if ($devices->count() > 0)
        <div class="overflow-x-auto">
            <table id="devicesTable" class="min-w-full divide-y divide-gray-200 border border-gray-300 dark:border-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Model') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('IP Address') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Location') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($devices as $device)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $device->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $device->model ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700 dark:text-gray-300">
                                {{ $device->ip_address }}:{{ $device->port }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $device->location ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($device->is_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">{{ __('Active') }}</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                <a href="{{ route('devices.show', $device) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">{{ __('View') }}</a>
                                @role('Superadmin')
                                    <a href="{{ route('devices.edit', $device) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('devices.destroy', $device) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">{{ __('Delete') }}</button>
                                    </form>
                                @endrole
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if ($.fn.DataTable.isDataTable('#devicesTable')) {
                    $('#devicesTable').DataTable().destroy();
                }

                $('#devicesTable').DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [[5, 10, 15, 25, 50, 100], [5, 10, 15, 25, 50, 100]],
                    language: {
                        search: "Filter:",
                        lengthMenu: "Show _MENU_ rows per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ entries",
                        paginate: {
                            first: "First",
                            last: "Last",
                            next: "Next",
                            previous: "Previous"
                        },
                        zeroRecords: "No matching records found"
                    },
                    dom: '<"flex flex-col sm:flex-row gap-4 mb-4 items-center justify-between"<"flex items-center gap-2"l>f>t<"flex flex-col sm:flex-row gap-4 items-center justify-between"ip>',
                    columnDefs: [
                        { targets: -1, orderable: false, searchable: false }
                    ]
                });
            });

            // Reinitialize on Livewire updates
            document.addEventListener('livewire:navigated', function() {
                if ($.fn.DataTable.isDataTable('#devicesTable')) {
                    $('#devicesTable').DataTable().destroy();
                }
                setTimeout(() => {
                    $('#devicesTable').DataTable({
                        responsive: true,
                        pageLength: 10,
                        lengthMenu: [[5, 10, 15, 25, 50, 100], [5, 10, 15, 25, 50, 100]],
                        language: {
                            search: "Filter:",
                            lengthMenu: "Show _MENU_ rows per page",
                            info: "Showing _START_ to _END_ of _TOTAL_ entries",
                            paginate: {
                                first: "First",
                                last: "Last",
                                next: "Next",
                                previous: "Previous"
                            },
                            zeroRecords: "No matching records found"
                        },
                        dom: '<"flex flex-col sm:flex-row gap-4 mb-4 items-center justify-between"<"flex items-center gap-2"l>f>t<"flex flex-col sm:flex-row gap-4 items-center justify-between"ip>',
                        columnDefs: [
                            { targets: -1, orderable: false, searchable: false }
                        ]
                    });
                }, 100);
            });
        </script>
    @else
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No devices found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Add your first device to begin tracking.') }}</p>
        </div>
    @endif
</div>
