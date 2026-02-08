<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Edit Permission</h1>
                <p class="text-sm text-gray-600">Update permission name</p>
            </div>

            <form method="POST" action="{{ route('permissions.update', $permission) }}" class="bg-white shadow-md rounded-lg border border-gray-300 p-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" class="w-full border rounded px-3 py-2" required>
                </div>

                <div class="mt-6 flex items-center gap-2">
                    <a href="{{ route('permissions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
