<form wire:submit.prevent="save" class="space-y-6">
    @if (!$employee)
        <div class="rounded-md bg-yellow-50 p-4 border border-yellow-200 text-yellow-800">
            <div class="text-sm font-medium">Employee data not loaded.</div>
            <div class="text-xs mt-1">
                Route param: {{ is_object(request()->route('employee')) ? get_class(request()->route('employee')) : (request()->route('employee') ?? 'null') }}
            </div>
            <div class="text-xs">Employee ID prop: {{ $employeeId ?? 'null' }}</div>
        </div>
    @endif
    @error('save')
        <div class="rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                </div>
            </div>
        </div>
    @enderror
    {{-- Basic Information Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Basic Information') }}</h3>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-4">
                <div>
                    <label for="badge_number" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Badge Number') }}</label>
                    <input wire:model="badge_number" type="text" id="badge_number" name="badge_number" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('badge_number') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Last Name') }} <span class="text-red-500">*</span></label>
                    <input wire:model="last_name" type="text" id="last_name" name="last_name" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('last_name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="birthdate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Birthdate') }}</label>
                    <input wire:model="birthdate" type="date" id="birthdate" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('birthdate') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Email') }}</label>
                    <input wire:model="email" type="email" id="email" name="email" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('email') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('First Name') }} <span class="text-red-500">*</span></label>
                    <input wire:model="first_name" type="text" id="first_name" name="first_name" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    @error('first_name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="gender" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Gender') }}</label>
                    <select wire:model="gender" id="gender" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">-- Select --</option>
                        <option value="Male">{{ __('Male') }}</option>
                        <option value="Female">{{ __('Female') }}</option>
                    </select>
                    @error('gender') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label for="employee_group_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Group') }} <span class="text-red-500">*</span></label>
                    <select wire:model="employee_group_id" id="employee_group_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">-- Select Group --</option>
                        @foreach($employeeGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                    @error('employee_group_id') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="middle_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Middle Initial') }}</label>
                        <input wire:model="middle_name" type="text" id="middle_name" name="middle_name" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('middle_name') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="suffix" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Suffix') }}</label>
                        <input wire:model="suffix" type="text" id="suffix" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        @error('suffix') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div>
                    <label for="civil_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Civil Status') }}</label>
                    <select wire:model="civil_status" id="civil_status" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="">-- Select --</option>
                        <option value="Single">{{ __('Single') }}</option>
                        <option value="Married">{{ __('Married') }}</option>
                        <option value="Divorced">{{ __('Divorced') }}</option>
                        <option value="Widowed">{{ __('Widowed') }}</option>
                    </select>
                    @error('civil_status') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Employment Details Section --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Employment Details') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label for="position_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Position') }}</label>
                <select wire:model="position_id" id="position_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">-- Select Position --</option>
                    @foreach($positions as $position)
                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="department_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('Department') }} <span class="text-red-500">*</span></label>
                <select wire:model="department_id" id="department_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">-- Select Department --</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            @role('Admin|Superadmin|DTR Incharge')
            <div>
                <label for="dtr_signatory_department_id" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">{{ __('DTR Signatory') }}</label>
                <select wire:model="dtr_signatory_department_id" id="dtr_signatory_department_id" class="bg-gray-50 border border-gray-400 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                    <option value="">Head of Office (default)</option>
                    @foreach($departments as $department)
                        @php
                            $headLabel = $department->head_name ?: 'Head of Office';
                        @endphp
                        <option value="{{ $department->id }}">{{ $headLabel }} — {{ $department->name }}</option>
                    @endforeach
                </select>
                @error('dtr_signatory_department_id') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
            </div>
            @endrole
        </div>
    </div>

    {{-- Form Actions --}}
    <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center gap-3 pt-4 border-t border-gray-200 dark:border-gray-600">
        <a href="{{ route('employees.index') }}" class="text-white bg-orange-500 hover:bg-orange-600 focus:ring-4 focus:ring-orange-200 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-orange-600 dark:hover:bg-orange-700 focus:outline-none dark:focus:ring-orange-800 inline-flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            {{ __('Cancel') }}
        </a>
        <button type="submit" wire:loading.attr="disabled" wire:target="save" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800 inline-flex items-center justify-center">
            <span>{{ $employee ? __('Update Employee') : __('Create Employee') }}</span>
            <span wire:loading wire:target="save" class="inline-flex items-center ml-2">
                <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                </svg>
            </span>
        </button>
    </div>
</form>

