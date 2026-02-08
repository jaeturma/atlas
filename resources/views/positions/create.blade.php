<x-admin-layout>
    <x-slot name="header">
        {{ isset($position) ? 'Edit Position' : 'Create Position' }}
    </x-slot>
    <div class="min-h-screen bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('positions.index') }}" class="text-blue-600 hover:text-blue-900 mb-4 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Positions
            </a>
            <h1 class="text-3xl font-bold text-gray-900">
                @if(isset($position))
                    Edit Position
                @else
                    Create New Position
                @endif
            </h1>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ isset($position) ? route('positions.update', $position) : route('positions.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                @if(isset($position))
                    @method('PUT')
                @endif

                <!-- Position Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-900">Position Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $position->name ?? '') }}" required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $position->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Daily Rate -->
                <div>
                    <label for="daily_rate" class="block text-sm font-medium text-gray-900">Daily Rate (₱)</label>
                    <input type="number" name="daily_rate" id="daily_rate" step="0.01" min="0" 
                        value="{{ old('daily_rate', $position->daily_rate ?? '') }}"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('daily_rate') border-red-500 @enderror">
                    @error('daily_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div>
                    <label for="is_active" class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                            {{ old('is_active', $position->is_active ?? true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm font-medium text-gray-900">Active</span>
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                    <a href="{{ route('positions.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        @if(isset($position))
                            Update Position
                        @else
                            Create Position
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>

