<x-admin-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Login OTPs</h1>
                <p class="mt-1 text-sm text-gray-600">View OTP records for first-time login.</p>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-hidden border border-gray-200">
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">OTP</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Attempts</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expires</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Used</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($otps as $otp)
                                @php
                                    $user = $users->get($otp->email);
                                    $status = $otp->used_at ? 'Used' : ($otp->expires_at && $otp->expires_at->isPast() ? 'Expired' : 'Active');
                                    $otpValue = '—';
                                    if (!empty($otp->code_encrypted)) {
                                        try {
                                            $otpValue = \Illuminate\Support\Facades\Crypt::decryptString($otp->code_encrypted);
                                        } catch (\Exception $e) {
                                            $otpValue = '—';
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $user?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $otp->email }}</td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $otpValue }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $status === 'Active' ? 'bg-green-100 text-green-800' : ($status === 'Used' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $otp->attempts }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ optional($otp->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ optional($otp->expires_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $otp->used_at ? $otp->used_at->format('Y-m-d H:i') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $otps->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
