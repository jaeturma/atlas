<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="w-full max-w-screen-lg mx-auto px-2 sm:px-4 lg:px-6 py-6 sm:py-8">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Activity/Travel</h1>
                    <p class="mt-1 text-sm text-gray-600">View and manage your activity/travel</p>
                </div>
                <a href="{{ route('activities.create') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-3 text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    + New Activity/Travel
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
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
                <div class="mb-4 rounded-md bg-red-50 p-4">
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

            <div class="mb-4">
                <form action="{{ route('activities.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div>
                        <label for="search" class="block text-xs font-medium text-gray-700">Search</label>
                        <input type="text" id="search" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Activity/Travel Title, Description..." class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                    </div>
                    <div>
                        <label for="month" class="block text-xs font-medium text-gray-700">Month</label>
                        <input type="month" id="month" name="month" value="{{ request('month', $selectedMonth ?? now()->format('Y-m')) }}" class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                    </div>
                    <div>
                        <label for="per_page" class="block text-xs font-medium text-gray-700">Rows</label>
                        <select id="per_page" name="per_page" class="mt-1 block w-full border border-gray-300 rounded-md px-4 py-3 text-base">
                            <option value="10" {{ request('per_page', $perPage ?? 15) == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page', $perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page', $perPage ?? 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', $perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', $perPage ?? 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="flex gap-3 items-end">
                        <button type="submit" class="w-full px-4 py-3 text-base bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                        <a href="{{ route('activities.index', ['month' => now()->format('Y-m')]) }}" class="w-full px-4 py-3 text-base bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300 text-center">Reset</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
                @if($activities->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach($activities as $activity)
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            @if($activity->end_date && $activity->end_date->format('Y-m-d') !== $activity->date->format('Y-m-d'))
                                                {{ $activity->date->format('M d') }}–{{ $activity->end_date->format('d, Y') }}
                                            @else
                                                {{ $activity->date->format('M d, Y') }}
                                            @endif
                                        </div>
                                        <div class="text-sm font-semibold text-gray-700 mt-1">
                                            {{ $activity->activity_type }}
                                        </div>
                                    </div>
                                </div>

                                @if($activity->description)
                                    <div class="mt-2 text-xs text-gray-600">
                                        {{ $activity->description }}
                                    </div>
                                @endif

                                <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                    <button onclick="openAttachmentModal({{ $activity->id }}, 'memo', '{{ $activity->memorandum_link }}')" class="px-2 py-2 rounded border border-gray-900 text-gray-900 bg-gray-50 disabled:opacity-50" {{ empty($activity->memorandum_link) ? 'disabled' : '' }}>
                                        Memo
                                    </button>
                                    <button onclick="openAttachmentModal({{ $activity->id }}, 'ca', '{{ !empty($activity->certificate_attachment) ? asset('storage/' . $activity->certificate_attachment) : '' }}')" class="px-2 py-2 rounded border border-gray-900 text-gray-900 bg-gray-50 disabled:opacity-50" {{ empty($activity->certificate_attachment) ? 'disabled' : '' }}>
                                        CA/Travel
                                    </button>
                                    <button onclick="openAttachmentModal({{ $activity->id }}, 'att', '{{ !empty($activity->att_attachment) ? asset('storage/' . $activity->att_attachment) : '' }}')" class="px-2 py-2 rounded border border-gray-900 text-gray-900 bg-gray-50 disabled:opacity-50" {{ empty($activity->att_attachment) ? 'disabled' : '' }}>
                                        ATT/LOC
                                    </button>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-2">
                                    <a href="{{ route('activities.show', $activity) }}" class="w-full px-2 py-2 rounded border border-gray-900 text-white bg-gray-800 text-xs text-center">View</a>
                                    <a href="{{ route('activities.edit', $activity) }}" class="w-full px-2 py-2 rounded border border-gray-900 text-white bg-blue-700 text-xs text-center">Edit</a>
                                    <form action="{{ route('activities.destroy', $activity) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity?');" class="w-full">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full px-2 py-2 rounded border border-gray-900 text-white bg-red-700 text-xs">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-gray-500">No activity/travel records found.</p>
                        <a href="{{ route('activities.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">Create your first activity/travel</a>
                    </div>
                @endif
            </div>

            @if($activities->count() > 0)
                <div class="mt-6">
                    {{ $activities->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    @include('activities.partials.attachment-modal')
</x-admin-layout>