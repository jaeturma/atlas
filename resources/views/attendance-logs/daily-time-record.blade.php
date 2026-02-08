<x-admin-layout>
    {{-- No sidebar/header title --}}
    <div class="min-h-screen bg-gray-50">
        <div class="w-full px-2 sm:px-4 lg:px-8 py-6 sm:py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Daily Time Record</h1>
                <p class="mt-1 text-sm text-gray-600">View and manage employee daily time records by department</p>
            </div>

            @php
                $employee_id = $employee_id ?? request('employee_id');
            @endphp

            <!-- Filters Section -->
            <div class="bg-white shadow-md rounded-lg p-4 sm:p-6 mb-6">
                <form method="GET" action="{{ route('attendance-logs.daily-time-record') }}" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Month Selector -->
                        <div>
                            <label for="month" class="block text-sm font-medium text-gray-700 mb-2">Month</label>
                            <input type="month" name="month" id="month" value="{{ $month }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        @if(auth()->user()?->hasRole('Admin|Superadmin'))
                            <!-- Period Selector -->
                            <div>
                                <label for="period" class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                                <select name="period" id="period" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="whole" @selected($period === 'whole')>Whole Month</option>
                                    <option value="1-15" @selected($period === '1-15')>1-15</option>
                                    <option value="16-31" @selected($period === '16-31')>16-31</option>
                                </select>
                            </div>

                            <!-- Department Filter -->
                            <div>
                                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                                <select name="department_id" id="department_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- All Departments --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}" @selected($department_id == $dept->id)>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(!empty($showEmployeeFilter))
                            <div class="sm:col-span-2 lg:col-span-2">
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                                <select name="employee_id" id="employee_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- All Employees --</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected((string) $employee_id === (string) $employee->id)>
                                            {{ $employee->getFullName() }} ({{ $employee->badge_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Submit Button -->
                        <div class="flex items-end gap-3">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md border border-gray-900 transition">
                                Filter
                            </button>
                            <a href="{{ route('attendance-logs.daily-time-record', ['month' => $month, 'period' => $period]) }}" class="w-full px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-900 font-medium rounded-md text-center">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Period Info -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-blue-800">
                    <strong>Period:</strong> {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}
                    @if ($department_id)
                        @php
                            $dept = $departments->firstWhere('id', $department_id);
                        @endphp
                        @if ($dept)
                            | <strong>Department:</strong> {{ $dept->name }}
                        @endif
                    @endif
                    @if ($employee_id)
                        @php
                            $selectedEmployee = $employees->firstWhere('id', (int) $employee_id);
                        @endphp
                        @if ($selectedEmployee)
                            | <strong>Employee:</strong> {{ $selectedEmployee->getFullName() }}
                        @endif
                    @endif
                </p>
            </div>

            @php
                $displayEmployees = $employees;
                if (!empty($employee_id)) {
                    $displayEmployees = $employees->where('id', (int) $employee_id);
                }
            @endphp

            <!-- Employees Table -->
            @if ($displayEmployees->count() > 0)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <!-- Mobile cards -->
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($displayEmployees as $employee)
                            <div class="p-4">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $employee->getFullName() }}
                                </div>
                                <div class="mt-3 grid grid-cols-1 gap-2">
                                    <a href="/attendance-logs/employee/{{ $employee->id }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                       class="inline-flex items-center justify-center px-3 py-2 bg-green-50 hover:bg-green-100 text-green-700 rounded-md border border-gray-900 transition">
                                        Raw Logs
                                    </a>
                                    <a href="{{ route('attendance-logs.form-48', $employee->id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                       class="inline-flex items-center justify-center px-3 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-md border border-gray-900 transition">
                                        Form 48
                                    </a>
                                    <a href="{{ route('attendance-logs.final-form', $employee->id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                       class="inline-flex items-center justify-center px-3 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md border border-gray-900 transition">
                                        Final Form
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Desktop table -->
                    <div class="hidden md:block">
                    <table class="w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($displayEmployees as $employee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="/attendance-logs/employee/{{ $employee->id }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" class="text-sm font-medium text-blue-600 hover:text-blue-900">
                                            {{ $employee->getFullName() }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(auth()->user()?->hasRole('Admin|Superadmin|DTR Incharge'))
                                            <div class="flex items-center gap-1 flex-nowrap whitespace-nowrap">
                                                <a href="/attendance-logs/employee/{{ $employee->id }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}" 
                                                   class="inline-flex items-center justify-center px-2 py-1 text-[10px] leading-tight bg-green-50 hover:bg-green-100 text-green-700 rounded border border-gray-900 transition" title="View raw data">
                                                    Raw Logs
                                                </a>
                                                <a href="{{ route('attendance-logs.form-48', $employee->id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                                   class="inline-flex items-center justify-center px-2 py-1 text-[10px] leading-tight bg-purple-50 hover:bg-purple-100 text-purple-700 rounded border border-gray-900 transition" title="Print Form 48 DTR">
                                                    Form 48
                                                </a>
                                                <a href="{{ route('attendance-logs.final-form', $employee->id) }}?start_date={{ $startDate->format('Y-m-d') }}&end_date={{ $endDate->format('Y-m-d') }}"
                                                   class="inline-flex items-center justify-center px-2 py-1 text-[10px] leading-tight bg-blue-50 hover:bg-blue-100 text-blue-700 rounded border border-gray-900 transition" title="Print Final DTR">
                                                    Final Form
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Total Employees -->
                <div class="mt-4 text-sm text-gray-600">
                    Showing <strong>{{ $displayEmployees->count() }}</strong> employee(s)
                </div>
            @else
                <div class="bg-white rounded-lg shadow p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No employees</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($department_id)
                            No employees found in the selected department.
                        @else
                            No employees found. Try selecting a department.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>

