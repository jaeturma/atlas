<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="w-full max-w-5xl mx-auto px-6 lg:px-10 py-8">
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('leaves.index', ['month' => request('month', now()->format('Y-m'))]) }}" class="text-blue-600 hover:text-blue-900 inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Leaves
                </a>
                <div class="text-sm text-gray-500">Leave ID: {{ $leave->id }}</div>
            </div>

            <div class="bg-white shadow rounded-lg p-6 space-y-6">
                @php
                    $canManageValidated = auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge');
                    $canApprove = auth()->user()?->hasRole('Admin|Superadmin|Leave Incharge|Leave Approver');
                    $employeeOwner = auth()->user()?->employee;
                    $isOwner = $employeeOwner && $leave->employee_id === $employeeOwner->id;
                    $canEdit = true;
                    if ($leave->status === 'Validated' && !$canManageValidated) {
                        $canEdit = false;
                    }
                    if ($leave->status === 'Approved' && !$canApprove) {
                        $canEdit = false;
                    }
                    if ($leave->status === 'Filed' && !$canManageValidated && !$isOwner) {
                        $canEdit = false;
                    }
                    $canDelete = false;
                    if ($leave->status === 'Filed' && ($canManageValidated || $isOwner)) {
                        $canDelete = true;
                    }
                    if ($leave->status === 'Validated' && $canManageValidated) {
                        $canDelete = true;
                    }
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Employee</p>
                        <p class="text-lg text-gray-900 font-semibold">{{ $leave->employee?->getFullName() ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Leave Type</p>
                        <p class="text-lg text-gray-900 font-semibold">{{ $leave->leaveType?->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Date Range</p>
                        <p class="text-lg text-gray-900 font-semibold">
                            @if ($leave->start_date)
                                {{ $leave->start_date->format('M d, Y') }}
                                @if($leave->end_date && $leave->end_date->format('Y-m-d') !== $leave->start_date->format('Y-m-d'))
                                    - {{ $leave->end_date->format('M d, Y') }}
                                @endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Days</p>
                        <p class="text-lg text-gray-900 font-semibold">{{ number_format($leave->number_of_days, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="text-lg text-gray-900 font-semibold">{{ $leave->status }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Reason / Details</p>
                    <p class="text-gray-900">{{ $leave->reason ?: '—' }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500">Attachment</p>
                    @if (!empty($leave->attachment))
                        <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="text-blue-600 underline">View Attachment</a>
                    @else
                        <p class="text-gray-900">—</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs text-gray-500">Approved Leave Attachment</p>
                    @if (!empty($leave->approved_attachment))
                        <a href="{{ asset('storage/' . $leave->approved_attachment) }}" target="_blank" class="text-blue-600 underline">View Approved Attachment</a>
                    @else
                        <p class="text-gray-900">—</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Validated By</p>
                        <p class="text-gray-900">{{ $leave->validator?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Validated At</p>
                        <p class="text-gray-900">{{ $leave->validated_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-500">Approved By</p>
                        <p class="text-gray-900">{{ $leave->approved_pnpki_full_name ?: ($leave->approver?->name ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">PNPKI Serial Number</p>
                        <p class="text-gray-900">{{ $leave->approved_pnpki_serial_number ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Approved At</p>
                        <p class="text-gray-900">{{ $leave->approved_at?->format('M d, Y h:i A') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">PNPKI Certificate</p>
                        @if (!empty($leave->approved_pnpki_certificate_path))
                            <a href="{{ asset('storage/' . $leave->approved_pnpki_certificate_path) }}" target="_blank" class="text-blue-600 underline">View Certificate</a>
                        @else
                            <p class="text-gray-900">—</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @if(!empty($leave?->id))
                        <a href="{{ route('leaves.print', ['leaf' => $leave->id]) }}" target="_blank" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900">Print</a>
                        <a href="{{ route('leaves.download-pdf', ['leaf' => $leave->id]) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">PDF</a>
                    @else
                        <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">Print</span>
                        <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">PDF</span>
                    @endif
                    @if(!empty($leave?->id))
                        @if ($canEdit)
                            <a href="{{ route('leaves.edit', ['leaf' => $leave->id]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Edit</a>
                        @else
                            <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">Edit</span>
                        @endif
                    @else
                        <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">Edit</span>
                    @endif
                    @if(!empty($leave?->id))
                        @if ($canDelete)
                            <form action="{{ route('leaves.destroy', ['leaf' => $leave->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                            </form>
                        @else
                            <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">Delete</span>
                        @endif
                    @else
                        <span class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg cursor-not-allowed">Delete</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
