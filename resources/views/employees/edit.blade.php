<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Employee') }}
            </h2>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('employees.update', $employee) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        @if ($errors->any())
                            <div class="rounded-md bg-red-50 p-4 border border-red-200">
                                <div class="text-sm text-red-800">Please fix the errors below.</div>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Basic Information') }}</h3>
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label for="badge_number" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Badge Number') }}</label>
                                        <input type="text" id="badge_number" name="badge_number" value="{{ old('badge_number', $employee->badge_number) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        @error('badge_number') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Last Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        @error('last_name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="birthdate" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Birthdate') }}</label>
                                        <input type="date" id="birthdate" name="birthdate" value="{{ old('birthdate', optional($employee->birthdate)->format('Y-m-d')) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        @error('birthdate') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Email') }}</label>
                                        <input type="email" id="email" name="email" value="{{ old('email', $employee->email) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('First Name') }} <span class="text-red-500">*</span></label>
                                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        @error('first_name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label for="gender" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Gender') }}</label>
                                        <select id="gender" name="gender" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            <option value="">-- Select --</option>
                                            <option value="Male" {{ old('gender', $employee->gender) === 'Male' ? 'selected' : '' }}>{{ __('Male') }}</option>
                                            <option value="Female" {{ old('gender', $employee->gender) === 'Female' ? 'selected' : '' }}>{{ __('Female') }}</option>
                                        </select>
                                        @error('gender') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label for="employee_group_id" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Group') }} <span class="text-red-500">*</span></label>
                                        <select id="employee_group_id" name="employee_group_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                            <option value="">-- Select Group --</option>
                                            @foreach($employeeGroups as $group)
                                                <option value="{{ $group->id }}" {{ (string) old('employee_group_id', $employee->employee_group_id) === (string) $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('employee_group_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="middle_name" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Middle Initial') }}</label>
                                            <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $employee->middle_name) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            @error('middle_name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label for="suffix" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Suffix') }}</label>
                                            <input type="text" id="suffix" name="suffix" value="{{ old('suffix', $employee->suffix) }}" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            @error('suffix') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label for="civil_status" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Civil Status') }}</label>
                                        <select id="civil_status" name="civil_status" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                            <option value="">-- Select --</option>
                                            <option value="Single" {{ old('civil_status', $employee->civil_status) === 'Single' ? 'selected' : '' }}>{{ __('Single') }}</option>
                                            <option value="Married" {{ old('civil_status', $employee->civil_status) === 'Married' ? 'selected' : '' }}>{{ __('Married') }}</option>
                                            <option value="Divorced" {{ old('civil_status', $employee->civil_status) === 'Divorced' ? 'selected' : '' }}>{{ __('Divorced') }}</option>
                                            <option value="Widowed" {{ old('civil_status', $employee->civil_status) === 'Widowed' ? 'selected' : '' }}>{{ __('Widowed') }}</option>
                                        </select>
                                        @error('civil_status') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Employment Details') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label for="position_id" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Position') }}</label>
                                    <select id="position_id" name="position_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        <option value="">-- Select Position --</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ (string) old('position_id', $employee->position_id) === (string) $position->id ? 'selected' : '' }}>{{ $position->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('position_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label for="department_id" class="block mb-2 text-sm font-medium text-gray-900">{{ __('Department') }} <span class="text-red-500">*</span></label>
                                    <select id="department_id" name="department_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                        <option value="">-- Select Department --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ (string) old('department_id', $employee->department_id) === (string) $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                @role('Admin|Superadmin|DTR Incharge')
                                <div>
                                    <label for="dtr_signatory_department_id" class="block mb-2 text-sm font-medium text-gray-900">{{ __('DTR Signatory') }}</label>
                                    <select id="dtr_signatory_department_id" name="dtr_signatory_department_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                        <option value="">Head of Office (default)</option>
                                        @foreach($departments as $department)
                                            @php
                                                $headLabel = $department->head_name ?: 'Head of Office';
                                            @endphp
                                            <option value="{{ $department->id }}" {{ (string) old('dtr_signatory_department_id', $employee->dtr_signatory_department_id) === (string) $department->id ? 'selected' : '' }}>{{ $headLabel }} — {{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('dtr_signatory_department_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                @endrole
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 pt-4 border-t border-gray-200">
                            <a href="{{ route('employees.index') }}" class="text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:ring-orange-200 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 inline-flex items-center justify-center">
                                {{ __('Update Employee') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

