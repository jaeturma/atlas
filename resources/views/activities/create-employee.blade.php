<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span>Create Activity or Travel</span>
            <a href="{{ route('activities.index') }}" class="text-sm text-blue-600 hover:text-blue-800">&lt; Back</a>
        </div>
    </x-slot>
    <div class="min-h-screen bg-gray-50">
    <div class="w-full max-w-3xl mx-auto px-2 sm:px-4 lg:px-6 py-6 sm:py-8">
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('activities.store') }}" method="POST" class="p-4 sm:p-6 space-y-6" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                <div class="rounded-md bg-blue-50 p-3 text-sm text-blue-800">
                    Employee: <span class="font-semibold">{{ $employee->getFullName() }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-900">Date <span class="text-red-500">*</span></label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" required
                            class="mt-1 block w-full px-4 py-3 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-900">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}"
                            class="mt-1 block w-full px-4 py-3 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="activity_type" class="block text-sm font-medium text-gray-900">Activity/Travel Title <span class="text-red-500">*</span></label>
                    <input type="text" id="activity_type" name="activity_type" value="{{ old('activity_type') }}" placeholder="e.g., Training, Leave, Meeting" required
                        class="mt-1 block w-full px-4 py-3 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('activity_type') border-red-500 @enderror">
                    @error('activity_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-900">Description</label>
                    <textarea id="description" name="description" rows="4" placeholder="Optional description..."
                        class="mt-1 block w-full px-4 py-3 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="memorandum_link" class="block text-sm font-medium text-gray-900">Memorandum PDF Link</label>
                        <input type="url" id="memorandum_link" name="memorandum_link" value="{{ old('memorandum_link') }}" placeholder="https://drive.google.com/file/d/..." 
                            class="mt-1 block w-full px-4 py-3 text-base border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Google Drive shared link (publicly accessible).</p>
                    </div>

                    <div>
                        <label for="certificate_attachment" class="block text-sm font-medium text-gray-900">Certificate of Appearance/Travel Form</label>
                        <input type="file" id="certificate_attachment" name="certificate_attachment" accept="application/pdf,image/jpeg,image/png"
                            class="mt-1 block w-full text-base text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">CA/Travel. PDF, JPG, or PNG file. Optional.</p>
                    </div>

                    <div>
                        <label for="att_attachment" class="block text-sm font-medium text-gray-900">Authority to Travel/Locator (ATT/LOC)</label>
                        <input type="file" id="att_attachment" name="att_attachment" accept="application/pdf,image/jpeg,image/png"
                            class="mt-1 block w-full text-base text-gray-700 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">PDF, JPG, or PNG file. Optional.</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('activities.index') }}" class="w-full sm:w-auto text-center px-4 py-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="w-full sm:w-auto px-4 py-3 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700">
                        Create Activity/Travel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-admin-layout>