<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
                <p class="text-sm text-gray-600">Update user details and access</p>
            </div>

            <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg border border-gray-300 p-6">
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

                @php
                    $userRoles = $user->roles->pluck('name')->toArray();
                    $userPermissions = $user->permissions->pluck('name')->toArray();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border rounded px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password (optional)</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Setting a password marks the user as password-enabled.</p>
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
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="rounded border-gray-300" {{ in_array($role->name, $userRoles, true) ? 'checked' : '' }}>
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">Direct Permissions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        @foreach ($permissions as $permission)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-gray-300" {{ in_array($permission->name, $userPermissions, true) ? 'checked' : '' }}>
                                {{ $permission->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">PNPKI Credentials (for Leave Approvers)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Full Name</label>
                            <input type="text" name="pnpki_full_name" value="{{ old('pnpki_full_name', $user->pnpki_full_name) }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Serial Number</label>
                            <input type="text" name="pnpki_serial_number" value="{{ old('pnpki_serial_number', $user->pnpki_serial_number) }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Valid Until</label>
                            <input type="date" name="pnpki_valid_until" value="{{ old('pnpki_valid_until', optional($user->pnpki_valid_until)->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">PNPKI Certificate (CER/CRT/PEM/P12/PFX)</label>
                            <input type="file" name="pnpki_certificate" accept=".cer,.crt,.pem,.p12,.pfx" class="w-full border rounded px-3 py-2">
                            @if (!empty($user->pnpki_certificate_path))
                                <p class="text-xs text-gray-500 mt-1">Current: <a href="{{ asset('storage/' . $user->pnpki_certificate_path) }}" target="_blank" class="text-blue-600 underline">View Certificate</a></p>
                                <label class="inline-flex items-center gap-2 text-xs text-gray-600 mt-2">
                                    <input type="checkbox" name="pnpki_certificate_clear" value="1" class="rounded border-gray-300">
                                    Remove current certificate
                                </label>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Required to approve leaves with PNPKI. Store only official credentials.</p>
                </div>

                <div class="mt-6 flex items-center gap-2">
                    <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
