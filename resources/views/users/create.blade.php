<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Create User</h1>
                <p class="text-sm text-gray-600">Add a new system user and assign roles/permissions</p>
            </div>

            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg border border-gray-300 p-6">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 rounded-md bg-red-50 p-4">
                        <ul class="list-disc list-inside text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password (optional)</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Leave blank to require OTP first login.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Roles</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach ($roles as $role)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-gray-300">
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">PNPKI Credentials (for Leave Approvers)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Full Name</label>
                            <input type="text" name="pnpki_full_name" value="{{ old('pnpki_full_name') }}" class="w-full border rounded px-3 py-2" placeholder="e.g., JUAN DELA CRUZ">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Serial Number</label>
                            <input type="text" name="pnpki_serial_number" value="{{ old('pnpki_serial_number') }}" class="w-full border rounded px-3 py-2" placeholder="Serial/Reference No.">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Valid Until</label>
                            <input type="date" name="pnpki_valid_until" value="{{ old('pnpki_valid_until') }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Certificate (CER/CRT/PEM/P12/PFX)</label>
                            <input type="file" name="pnpki_certificate" accept=".cer,.crt,.pem,.p12,.pfx" class="w-full border rounded px-3 py-2">
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Required to approve leaves with PNPKI. Store only official credentials.</p>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Direct Permissions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach ($permissions as $permission)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-gray-300">
                                {{ $permission->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Create User</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
