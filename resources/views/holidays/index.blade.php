<x-admin-layout>
    {{-- No sidebar/header title --}}
    <div class="min-h-screen bg-gray-50">
    <div class="w-full max-w-screen-2xl mx-auto px-6 lg:px-10 py-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Holidays</h1>
                <p class="mt-1 text-sm text-gray-600">Manage public and special holidays</p>
            </div>
            <a href="{{ route('holidays.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                + New Holiday
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

        <!-- Filters -->
        <div class="mb-4">
            <form action="{{ route('holidays.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-medium text-gray-700">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search', $search ?? '') }}" placeholder="Holiday name or description..." class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                </div>
                <div>
                    <label for="type" class="block text-xs font-medium text-gray-700">Type</label>
                    <select id="type" name="type" class="mt-1 block border border-gray-300 rounded-md px-3 py-2">
                        <option value="">All Types</option>
                        <option value="regular" {{ request('type') === 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="special" {{ request('type') === 'special' ? 'selected' : '' }}>Special</option>
                    </select>
                </div>
                <div>
                    <label for="per_page" class="block text-xs font-medium text-gray-700">Rows</label>
                    <select id="per_page" name="per_page" class="mt-1 block border border-gray-300 rounded-md px-3 py-2">
                        <option value="10" {{ request('per_page', $perPage ?? 15) == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page', $perPage ?? 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page', $perPage ?? 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page', $perPage ?? 15) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page', $perPage ?? 15) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Filter</button>
                <a href="{{ route('holidays.index') }}" class="px-4 py-2 bg-gray-200 text-gray-900 rounded-md hover:bg-gray-300">Reset</a>
            </form>
        </div>

        <!-- Holidays Table -->
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-400">
            @if($holidays->count() > 0)
                <div class="p-4 overflow-x-auto">
                <table id="holidaysTable" class="min-w-full divide-y divide-gray-200 border border-gray-300 dark:border-gray-600">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Holiday Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Memo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($holidays as $holiday)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ ($holidays->currentPage() - 1) * $holidays->perPage() + $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    @if($holiday->end_date && $holiday->end_date->format('Y-m-d') !== $holiday->date->format('Y-m-d'))
                                        {{ $holiday->date->format('M d') }}-{{ $holiday->end_date->format('d, Y') }}
                                    @else
                                        {{ $holiday->date->format('M d, Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $holiday->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $holiday->type === 'regular' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' }}">
                                        {{ ucfirst($holiday->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-700">
                                    @if(!empty($holiday->memorandum_attachment))
                                        <a href="{{ $holiday->memorandum_attachment }}" target="_blank" class="hover:text-blue-900" title="View Memorandum">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('holidays.edit', $holiday) }}" class="px-3 py-1 bg-blue-700 text-white rounded hover:bg-blue-800 text-xs">Edit</a>
                                        <form action="{{ route('holidays.destroy', $holiday) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-700 text-white rounded hover:bg-red-800 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">No holidays found.</p>
                    <a href="{{ route('holidays.create') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-900">Create your first holiday</a>
                </div>
            @endif
        </div>

        @if($holidays->count() > 0)
            <div class="mt-6">
                {{ $holidays->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
    </div>
</x-admin-layout>