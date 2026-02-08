<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
    <div class="w-full max-w-screen-2xl mx-auto px-6 lg:px-10 py-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Leave Requests</h1>
                <p class="mt-1 text-sm text-gray-600">Manage employee leave filings</p>
            </div>
            <a href="{{ route('leaves.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                + Create Leave Form
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="text-sm text-green-800">
                        {{ session('success') }}
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form action="{{ route('leaves.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                    <input type="month" id="month" name="month" value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Employee, Leave Type, Status..." class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                    <select id="per_page" name="per_page" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        @foreach ([10, 15, 25, 50, 100] as $size)
                            <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Apply</button>
                    <a href="{{ route('leaves.index', ['month' => now()->format('Y-m')]) }}" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300">Reset</a>
                </div>
            </form>
        </div>

        @php
            $canBatchApprove = auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');
            $canManageValidated = auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge');
            $canApprove = auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');
            $canValidate = auth()->user()?->hasRole('Admin|Superadmin|DTR Incharge');
        @endphp

        @if ($errors->has('batch_approve'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-800">
                    {{ $errors->first('batch_approve') }}
                </div>
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($leaves->count() > 0)
                @if ($canBatchApprove)
                    <form method="POST" action="{{ route('leaves.batch-approve') }}">
                        @csrf
                        <div class="flex items-center justify-between p-4 border-b border-gray-200">
                            <div class="text-sm text-gray-600">Select validated leaves with PNPKI-signed attachments for batch approval.</div>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Batch Approve</button>
                        </div>
                @endif
                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            @if ($canBatchApprove)
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Select</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Leave Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Range</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Approved Attachment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($leaves as $leave)
                            @php
                                $canEdit = true;
                                if ($leave->status === 'Validated' && !$canManageValidated) {
                                    $canEdit = false;
                                }
                                if ($leave->status === 'Approved' && !$canApprove) {
                                    $canEdit = false;
                                }
                                if ($leave->status === 'Filed' && !($canManageValidated || $canValidate || $canApprove)) {
                                    $canEdit = false;
                                }

                                $canDelete = false;
                                if ($leave->status === 'Filed' && $canManageValidated) {
                                    $canDelete = true;
                                }
                                if ($leave->status === 'Validated' && $canManageValidated) {
                                    $canDelete = true;
                                }
                            @endphp
                            <tr>
                                @if ($canBatchApprove)
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <input type="checkbox" name="leave_ids[]" value="{{ $leave->id }}" class="rounded border-gray-300" @disabled($leave->status !== 'Validated')>
                                    </td>
                                @endif
                                <td class="px-6 py-4 text-sm text-gray-900">{{ ($leaves->currentPage() - 1) * $leaves->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->employee?->getFullName() ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->leaveType?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $leave->start_date->format('M d, Y') }}
                                    @if($leave->end_date && $leave->end_date->format('Y-m-d') !== $leave->start_date->format('Y-m-d'))
                                        - {{ $leave->end_date->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($leave->number_of_days, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $leave->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if (!empty($leave->approved_attachment))
                                        <a href="{{ asset('storage/' . $leave->approved_attachment) }}" target="_blank" class="text-blue-600 underline">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 space-x-2">
                                    <a href="{{ route('leaves.show', $leave) }}" class="px-3 py-1 bg-gray-800 text-white rounded hover:bg-gray-900 text-xs">View</a>
                                    @if(!empty($leave?->id) && $canEdit)
                                        <a href="{{ route('leaves.edit', ['leaf' => $leave->id]) }}" class="px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800 text-xs">Edit</a>
                                    @else
                                        <span class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-xs cursor-not-allowed">Edit</span>
                                    @endif
                                        @if(!empty($leave?->id) && $canDelete)
                                            <form action="{{ route('leaves.destroy', ['leaf' => $leave->id]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Delete</button>
                                            </form>
                                        @else
                                            <span class="px-3 py-1 bg-gray-300 text-gray-700 rounded text-xs cursor-not-allowed">Delete</span>
                                        @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($canBatchApprove)
                    </form>
                @endif
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">No leave records found.</p>
                    <a href="{{ route('leaves.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">File the first leave</a>
                </div>
            @endif
        </div>

        @if($leaves->count() > 0)
            <div class="mt-6">
                {{ $leaves->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
    </div>
</x-admin-layout>
