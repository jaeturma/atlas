<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $employee->first_name }} {{ $employee->last_name }}
            </h2>
            <div class="space-x-2">
                <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 focus:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Edit') }}
                </a>
                <form method="POST" action="{{ route('employees.destroy', $employee) }}" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure?')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('Delete') }}
                    </button>
                </form>
                <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <div class="space-y-8">
                        {{-- Basic Information --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-300">{{ __('Basic Information') }}</h3>
                            <div class="overflow-x-auto -mx-4 sm:mx-0">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Badge Number') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900" colspan="3">
                                                @if($employee->badge_number)
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 font-mono">
                                                        {{ $employee->badge_number }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-500">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('First Name') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->first_name }}</td>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Middle Name') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->middle_name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Last Name') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->last_name }}</td>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Suffix') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->suffix ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Birthdate') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->birthdate ? $employee->birthdate->format('M d, Y') : '-' }}</td>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Gender') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->gender ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Civil Status') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900" colspan="3">{{ $employee->civil_status ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        {{-- Employment Details --}}
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-300">{{ __('Employment Details') }}</h3>
                            <div class="overflow-x-auto -mx-4 sm:mx-0">
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-gray-200">
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Position') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->position?->name ?? '-' }}</td>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Department') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->department?->name ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-3 sm:px-4 py-3 font-medium text-gray-700 w-1/4">{{ __('Group') }}</td>
                                            <td class="px-3 sm:px-4 py-3 text-gray-900">{{ $employee->employeeGroup?->name ?? '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Attendance Logs - Livewire Component --}}
                        <livewire:employee-attendance-logs :employee="$employee" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>