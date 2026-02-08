<!DOCTYPE html>
@props(['hideSidebar' => false, 'hideTopbar' => false])
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin Dashboard</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        .sidebar-collapsed #logo-sidebar {
            width: 4.5rem;
        }
        .sidebar-collapsed #logo-sidebar .sidebar-label {
            display: none;
        }
        .sidebar-collapsed #logo-sidebar .sidebar-item {
            justify-content: center;
        }
        .sidebar-collapsed #logo-sidebar .sidebar-item .sidebar-chevron {
            display: none;
        }
        .sidebar-collapsed #logo-sidebar #dropdown-users {
            display: none !important;
        }
        #logo-sidebar .sidebar-item svg {
            color: #1e3a8a !important;
        }
        .sidebar-collapsed .app-main {
            margin-left: 4.5rem;
        }
        .app-topbar {
            left: 0;
            width: 100%;
        }
        @media (min-width: 640px) {
            .app-topbar {
                left: 16rem;
                width: calc(100% - 16rem);
            }
            .sidebar-collapsed .app-topbar {
                left: 4.5rem;
                width: calc(100% - 4.5rem);
            }
        }
        @media (max-width: 640px) {
            .sidebar-collapsed .app-main {
                margin-left: 0;
            }
        }
        .employee-footer-space main {
            padding-bottom: 6.5rem;
        }
        .employee-no-scroll {
            overflow: hidden;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 @role('Employee') employee-footer-space employee-no-scroll @endrole">
@if(!$hideSidebar)

    <!-- Sidebar -->
    <div class="print:hidden">
        @include('layouts.partials.sidebar')
    </div>
@endif

    <div class="app-main {{ !$hideSidebar ? 'sm:ml-64' : '' }} print:ml-0">
        <!-- Top Navigation -->
        @if(!$hideTopbar)
            <div class="print:hidden">
                @include('layouts.partials.topbar')
            </div>
        @endif

        <!-- Main Content -->
        <main class="p-4 md:ml-0 h-auto {{ $hideTopbar ? 'pt-4' : 'pt-20' }} print:p-0 print:pt-0">
            <!-- Page Heading -->
            @isset($header)
                <div class="mb-4 print:hidden">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $header }}</h1>
                </div>
            @endisset

            <!-- Page Content -->
            {{ $slot }}
        </main>

        <footer class="px-4 pb-4 text-xs text-gray-600 print:hidden">
            DepEd - Schools Division of Davao de Oro
        </footer>
    </div>

    @include('layouts.partials.employee-footer')

    <script>
        const showFlashAlerts = () => {
            if (typeof Swal === 'undefined') return;

            const flash = {
                success: @json(session('success')),
                error: @json(session('error')),
                warning: @json(session('warning')),
                info: @json(session('info')),
            };
            const redirectAfterSuccess = @json(session('redirect_after_success'));

            const errors = @json($errors->all());

            const show = (type, message) => {
                if (!message) return;
                Swal.fire({
                    icon: type,
                    title: type.charAt(0).toUpperCase() + type.slice(1),
                    text: message,
                    confirmButtonColor: '#2563eb',
                });
            };

            if (errors && errors.length) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: '<ul class="text-left list-disc list-inside">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>',
                    confirmButtonColor: '#2563eb',
                    width: '32rem',
                });
            } else if (flash.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: flash.success,
                    confirmButtonColor: '#2563eb',
                }).then(() => {
                    if (redirectAfterSuccess) {
                        window.location.href = redirectAfterSuccess;
                    }
                });
            } else if (flash.error) {
                show('error', flash.error);
            } else if (flash.warning) {
                show('warning', flash.warning);
            } else if (flash.info) {
                show('info', flash.info);
            }
        };

        document.addEventListener('DOMContentLoaded', showFlashAlerts);
        document.addEventListener('livewire:navigated', showFlashAlerts);
    </script>

    <script>
        document.addEventListener('livewire:init', () => {
            if (typeof Livewire === 'undefined' || typeof Swal === 'undefined') return;

            Livewire.on('swal:success', ({ message, redirect }) => {
                if (!message) return;
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    confirmButtonColor: '#2563eb',
                }).then(() => {
                    if (redirect) {
                        window.location.href = redirect;
                    }
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleButton = document.getElementById('sidebarToggle');
            const savedState = localStorage.getItem('sidebar-collapsed');
            const isDesktop = () => window.matchMedia('(min-width: 640px)').matches;

            const applyLogoState = () => {
                const logo = document.getElementById('sidebarLogo');
                if (!logo) return;
                const collapsed = document.body.classList.contains('sidebar-collapsed');
                const fullSrc = logo.getAttribute('data-full');
                const collapsedSrc = logo.getAttribute('data-collapsed');
                logo.src = collapsed ? collapsedSrc : fullSrc;
            };

            if (savedState === 'true' && isDesktop()) {
                document.body.classList.add('sidebar-collapsed');
            }

            if (!isDesktop()) {
                document.body.classList.remove('sidebar-collapsed');
            }

            applyLogoState();

            if (!toggleButton) return;

            toggleButton.addEventListener('click', () => {
                if (!isDesktop()) return;
                document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
                applyLogoState();
            });

            window.addEventListener('resize', () => {
                if (!isDesktop()) {
                    document.body.classList.remove('sidebar-collapsed');
                    applyLogoState();
                } else if (localStorage.getItem('sidebar-collapsed') === 'true') {
                    document.body.classList.add('sidebar-collapsed');
                    applyLogoState();
                }
            });
        });
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>

