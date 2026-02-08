<x-guest-layout fullScreen="true">
    <div class="grid grid-cols-1 md:grid-cols-2 min-h-screen">
        <div id="login-left-panel" class="relative h-full bg-cover bg-center overflow-hidden max-h-0 opacity-0 transition-all duration-300 ease-out md:max-h-none md:opacity-100" style="background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1200 1200%22><defs><linearGradient id=%22g%22 x1=%220%22 y1=%220%22 x2=%221%22 y2=%221%22><stop offset=%220%25%22 stop-color=%22%23f59e0b%22/><stop offset=%2250%25%22 stop-color=%22%23fbbf24%22/><stop offset=%22100%25%22 stop-color=%22%23fde68a%22/></linearGradient></defs><rect width=%221200%22 height=%221200%22 fill=%22url(%23g)%22/><circle cx=%22250%22 cy=%22250%22 r=%22180%22 fill=%22%23f97316%22 opacity=%220.25%22/><circle cx=%22950%22 cy=%22950%22 r=%22220%22 fill=%22%23f97316%22 opacity=%220.25%22/><path d=%22M0 900 Q300 820 600 900 T1200 900 V1200 H0 Z%22 fill=%22%23f59e0b%22 opacity=%220.35%22/></svg>');">
            <div class="absolute inset-0 bg-black/10"></div>
            <div class="absolute inset-0 flex items-center justify-center p-10">
                <div class="max-w-2xl text-left text-blue-900">
                    <div class="text-base sm:text-lg leading-relaxed">
                        <span class="font-semibold">DepEd Davao de Oro ATLAS Plus</span> is an on-premise, technology-enabled attendance and workforce activity management system designed to support transparency, accuracy, and efficiency in the Department of Education – Schools Division of Davao de Oro. The system integrates biometric and future facial recognition–based attendance to automatically generate Draft Form 48 and Form 48 Final, while providing real-time monitoring of attendance logs and secure storage of employee activities undertaken outside the office. Built with an offline-capable architecture and strict data privacy controls, ATLAS Plus enhances timekeeping, accountability, and reporting, aligning daily operations with DepEd policies and modern digital governance standards.
                    </div>
                </div>
            </div>
        </div>

        <div class="min-h-screen flex items-center justify-center bg-white py-10 md:py-0">
            <div class="w-full max-w-md px-6 sm:px-8">
                <div class="flex items-center justify-center mb-6">
                    <x-application-logo class="w-44 sm:w-56 h-auto object-contain" />
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" class="block mt-1 w-full text-xl sm:text-2xl py-4" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" class="block mt-1 w-full text-xl sm:text-2xl py-4" type="password" name="password" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                        <x-text-input id="password_confirmation" class="block mt-1 w-full text-xl sm:text-2xl py-4" type="password" name="password_confirmation" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                    </div>

                    <x-primary-button class="w-full justify-center py-4 text-lg sm:text-xl">
                        {{ __('Reset Password') }}
                    </x-primary-button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const panel = document.getElementById('login-left-panel');
            if (!panel) return;

            const isMobile = () => window.matchMedia('(max-width: 767px)').matches;

            const togglePanel = () => {
                if (!isMobile()) {
                    panel.classList.remove('max-h-0', 'opacity-0');
                    panel.classList.add('md:max-h-none', 'md:opacity-100');
                    return;
                }

                if (window.scrollY > 10) {
                    panel.classList.remove('max-h-0', 'opacity-0');
                    panel.classList.add('max-h-[600px]', 'opacity-100');
                } else {
                    panel.classList.add('max-h-0', 'opacity-0');
                    panel.classList.remove('max-h-[600px]', 'opacity-100');
                }
            };

            togglePanel();
            window.addEventListener('scroll', togglePanel, { passive: true });
            window.addEventListener('resize', togglePanel);
        });
    </script>
</x-guest-layout>

