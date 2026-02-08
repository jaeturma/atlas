<div>
    <!-- Department Filter -->
    <div class="mb-4 flex items-center gap-3">
        <label for="departmentFilter" class="text-sm font-medium text-gray-700 whitespace-nowrap">
            {{ __('Filter by Department:') }}
        </label>
        <select wire:model.live="departmentFilter" id="departmentFilter" class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" @disabled($isDepartmentLocked)>
            <option value="">{{ $isDepartmentLocked ? __('Department Locked') : __('All Departments') }}</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </select>
        @if ($departmentFilter && !$isDepartmentLocked)
            <button wire:click="$set('departmentFilter', '')" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                {{ __('Clear') }}
            </button>
        @endif
    </div>

    @if ($employees->count() > 0)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-400">
            <div class="p-4 overflow-x-auto">
                <table id="employeeTable" class="min-w-full divide-y divide-gray-200 border border-gray-300 dark:border-gray-600">
                <thead class="bg-gray-100 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('No.') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Badge') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Position') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Email') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($employees as $employee)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $employee->first_name }}
                                @if(!empty($employee->middle_name))
                                    {{ strtoupper(substr($employee->middle_name, 0, 1)) }}.
                                @endif
                                {{ $employee->last_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                @if($employee->badge_number)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $employee->badge_number }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $employee->position?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                {{ $employee->email ?? '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                @if ($employee?->id)
                                    <a href="{{ route('employees.show', ['employee' => $employee->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-white bg-blue-600 hover:bg-blue-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>{{ __('View') }}</span>
                                    </a>
                                    <a href="{{ route('employees.edit', ['employee' => $employee->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-white bg-amber-500 hover:bg-amber-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.125L16.875 4.5" />
                                        </svg>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @unless(auth()->user()?->hasRole('DTR Incharge'))
                                        <form method="POST" action="{{ route('employees.destroy', ['employee' => $employee->id]) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-white bg-red-600 hover:bg-red-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2m-7 4v5m4-5v5m-9 0h14a1 1 0 001-1V7H4v11a1 1 0 001 1z" />
                                                </svg>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </form>
                                    @endunless
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <script>
            const initEmployeeTable = () => {
                if ($.fn.DataTable.isDataTable('#employeeTable')) {
                    $('#employeeTable').DataTable().destroy();
                }

                const table = $('#employeeTable').DataTable({
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
            };

            document.addEventListener('DOMContentLoaded', initEmployeeTable);

            document.addEventListener('livewire:navigated', () => {
                setTimeout(initEmployeeTable, 50);
            });
        </script>
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-400 px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No employees found') }}</h3>
            <p class="mt-1 text-sm text-gray-500">{{ __('Get started by adding your first employee.') }}</p>
        </div>
    @endif
</div>