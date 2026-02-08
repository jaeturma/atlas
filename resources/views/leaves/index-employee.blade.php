<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="w-full max-w-screen-lg mx-auto px-2 sm:px-4 lg:px-6 py-6 sm:py-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Leaves</h1>
                    <p class="mt-1 text-sm text-gray-600">View and manage your leave filings</p>
                </div>
                <a href="{{ route('leaves.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-3 text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    + File Leave
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
                <form action="{{ route('leaves.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700">Month</label>
                        <input type="month" id="month" name="month" value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}" class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                        <input type="text" id="search" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Leave Type, Status..." class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Per Page</label>
                        <select id="per_page" name="per_page" class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                            @foreach ([10, 15, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ (int) request('per_page', $perPage ?? 15) === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="w-full px-4 py-3 text-base bg-blue-600 text-white rounded-md">Apply</button>
                        <a href="{{ route('leaves.index', ['month' => now()->format('Y-m')]) }}" class="w-full px-4 py-3 text-base bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300 text-center">Reset</a>
                    </div>
                </form>
            </div>

            @if($leaves->count() > 0)
                <div class="grid grid-cols-1 gap-4">
                    @foreach($leaves as $leave)
                        @php
                            $canEdit = $leave->status === 'Filed';
                            $canDelete = $leave->status === 'Filed';
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-4 bg-white shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $leave->leaveType?->name ?? 'Leave' }}</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ $leave->start_date->format('M d, Y') }}
                                        @if($leave->end_date && $leave->end_date->format('Y-m-d') !== $leave->start_date->format('Y-m-d'))
                                            - {{ $leave->end_date->format('M d, Y') }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600">Days: {{ number_format($leave->number_of_days, 2) }}</p>
                                    <p class="text-sm text-gray-600">
                                        Approved Attachment:
                                        @if (!empty($leave->approved_attachment))
                                            <a href="{{ asset('storage/' . $leave->approved_attachment) }}" target="_blank" class="text-blue-600 underline">View</a>
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                                <div class="text-sm text-gray-700">Status: <span class="font-semibold">{{ $leave->status }}</span></div>
                            </div>
                            <div class="mt-3 flex flex-col sm:flex-row gap-2">
                                <a href="{{ route('leaves.show', $leave) }}" class="w-full sm:w-auto px-3 py-2 rounded border border-gray-900 text-white bg-gray-800 text-xs text-center">View</a>
                                @if(!empty($leave?->id) && $canEdit)
                                    <a href="{{ route('leaves.edit', ['leaf' => $leave->id]) }}" class="w-full sm:w-auto px-3 py-2 rounded border border-gray-900 text-white bg-blue-700 text-xs text-center">Edit</a>
                                @else
                                    <span class="w-full sm:w-auto px-3 py-2 rounded border border-gray-900 text-gray-700 bg-gray-200 text-xs text-center cursor-not-allowed">Edit</span>
                                @endif
                                    @if(!empty($leave?->id) && $canDelete)
                                        <form action="{{ route('leaves.destroy', ['leaf' => $leave->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this leave?');" class="w-full sm:w-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full px-3 py-2 rounded border border-gray-900 text-white bg-red-600 hover:bg-red-700 text-xs text-center">Delete</button>
                                        </form>
                                    @else
                                        <span class="w-full sm:w-auto px-3 py-2 rounded border border-gray-900 text-gray-700 bg-gray-200 text-xs text-center cursor-not-allowed">Delete</span>
                                    @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500">No leave records found.</p>
                    <a href="{{ route('leaves.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">File your first leave</a>
                </div>
            @endif

            @if($leaves->count() > 0)
                <div class="mt-6">
                    {{ $leaves->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
