<x-admin-layout>
    <x-slot name="header">
        Edit Leave
    </x-slot>
    <div class="min-h-screen bg-gray-50">
    <div class="w-full max-w-5xl mx-auto px-2 sm:px-4 lg:px-8 py-6 sm:py-8">
        <div class="mb-8">
            <a href="{{ route('leaves.index') }}" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Leaves
            </a>
        </div>

        @php
            $isApproved = $leave->status === 'Approved';
        @endphp
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 sm:px-6 pt-4 text-xs text-gray-500">CS Form No. 6, Rev 2020</div>
            @if ($isApproved)
                <div class="px-4 sm:px-6 pt-2 text-xs text-amber-700">This leave is approved. Only the approved attachment can be updated.</div>
            @endif
            <form action="{{ route('leaves.update', ['leaf' => $leave->id]) }}" method="POST" class="p-4 sm:p-6 space-y-6" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-900">Employee <span class="text-red-500">*</span></label>
                                <select id="employee_id" name="employee_id" required @disabled($isApproved)
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('employee_id') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                    <option value="">Select an employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id', $leave->employee_id) == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->badge_number }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="leave_type_id" class="block text-sm font-medium text-gray-900">Leave Type <span class="text-red-500">*</span></label>
                                <select id="leave_type_id" name="leave_type_id" required @disabled($isApproved)
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('leave_type_id') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                    <option value="">Select leave type</option>
                                    @foreach ($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}" {{ old('leave_type_id', $leave->leave_type_id) == $leaveType->id ? 'selected' : '' }}>
                                            {{ $leaveType->name }}{{ $leaveType->code ? ' (' . $leaveType->code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('leave_type_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-900">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $leave->start_date->format('Y-m-d')) }}" required @disabled($isApproved)
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-900">End Date (optional)</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $leave->end_date?->format('Y-m-d')) }}" @disabled($isApproved)
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="leave_period" class="block text-sm font-medium text-gray-900">Leave Period</label>
                                <select id="leave_period" name="leave_period" @disabled($isApproved)
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('leave_period') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                    <option value="full" @selected(old('leave_period', $leave->leave_period ?? 'full') === 'full')>Full Day</option>
                                    <option value="morning" @selected(old('leave_period', $leave->leave_period ?? '') === 'morning')>Half Day - Morning</option>
                                    <option value="afternoon" @selected(old('leave_period', $leave->leave_period ?? '') === 'afternoon')>Half Day - Afternoon</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Half-day applies to a single date only.</p>
                                @error('leave_period')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @if(auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver|DTR Incharge'))
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-900">Status</label>
                                    <select id="status" name="status" @disabled($isApproved)
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="Filed" @selected(old('status', $leave->status) === 'Filed')>Filed</option>
                                        @if(auth()->user()?->hasRole('Admin|Superadmin|DTR Incharge'))
                                            <option value="Validated" @selected(old('status', $leave->status) === 'Validated')>Validated</option>
                                        @endif
                                        @if(auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver'))
                                            <option value="Approved" @selected(old('status', $leave->status) === 'Approved')>Approved</option>
                                        @endif
                                    </select>
                                    @error('status')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-900">Reason / Details</label>
                            <textarea id="reason" name="reason" rows="4" @disabled($isApproved)
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('reason') border-red-500 @enderror {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">{{ old('reason', $leave->reason) }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="attachment" class="block text-sm font-medium text-gray-900">Leave Form / Attachment</label>
                            <input type="file" id="attachment" name="attachment" accept="application/pdf,image/jpeg,image/png" @disabled($isApproved)
                                class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isApproved ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                            @if (!empty($leave->attachment))
                                <p class="text-xs text-gray-500 mt-1">Current: <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="text-blue-600 underline">View Attachment</a></p>
                            @endif
                        </div>

                        <div>
                            <label for="approved_attachment" class="block text-sm font-medium text-gray-900">Approved Leave Attachment</label>
                            <input type="file" id="approved_attachment" name="approved_attachment" accept="application/pdf,image/jpeg,image/png"
                                class="mt-1 block w-full text-sm text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Upload the PNPKI-signed leave document when approving.</p>
                            @if (!empty($leave->approved_attachment))
                                <p class="text-xs text-gray-500 mt-1">Current: <a href="{{ asset('storage/' . $leave->approved_attachment) }}" target="_blank" class="text-blue-600 underline">View Approved Attachment</a></p>
                            @endif
                        </div>
                    </div>

                    <div>
                        @include('leaves.partials.available-leave', ['availableLeave' => $availableLeave])
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('leaves.index') }}" class="w-full sm:w-auto text-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </a>
                        <button type="submit" class="w-full sm:w-auto px-4 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                            {{ $isApproved ? 'Save Approved Attachment' : 'Update Leave' }}
                        </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>
