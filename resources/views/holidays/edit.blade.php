<x-admin-layout>
    <x-slot name="header">
        Edit Holiday
    </x-slot>
    <div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('holidays.index') }}" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Holidays
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Holiday</h1>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('holidays.update', $holiday) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Date -->
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-900">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" id="date" name="date" value="{{ old('date', $holiday->date->format('Y-m-d')) }}" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-900">End Date</label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $holiday->end_date?->format('Y-m-d')) }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $holiday->name) }}" placeholder="e.g., New Year's Day" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-900">Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
                        <option value="">Select a type</option>
                        <option value="regular" {{ old('type', $holiday->type) === 'regular' ? 'selected' : '' }}>Regular Holiday</option>
                        <option value="special" {{ old('type', $holiday->type) === 'special' ? 'selected' : '' }}>Special Holiday</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Optional description..."
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $holiday->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Memorandum Link -->
                <div>
                    <label for="memorandum_attachment" class="block text-sm font-medium text-gray-900">Memorandum Link (Google Drive)</label>
                    <input type="url" id="memorandum_attachment" name="memorandum_attachment" value="{{ old('memorandum_attachment', $holiday->memorandum_attachment) }}" placeholder="https://drive.google.com/..."
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('memorandum_attachment') border-red-500 @enderror">
                    @error('memorandum_attachment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('holidays.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        Update Holiday
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>
