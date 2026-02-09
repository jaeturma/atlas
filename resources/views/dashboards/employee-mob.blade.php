<x-admin-layout>
    <x-slot name="header"></x-slot>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@700&display=swap');

        .employee-dashboard {
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 700;
        }

        .employee-app-shell {
            background: radial-gradient(1200px 600px at 15% 10%, #f1e57b 0%, #f8fafc 55%, #96c0f6 100%);
        }

        .employee-card {
            border-radius: 8px;
            border: 1px solid #4b5563;
            box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
        }

        .employee-time-card {
            border: 3px double #1f2937;
            box-shadow: 0 16px 32px rgba(15, 23, 42, 0.18);
        }

        .employee-card-light {
            background: #ffffff;
        }

        .employee-card-accent {
            background: linear-gradient(135deg, #c3d8f4 0%, #eef2ff 100%);
            border-color: #4b5563;
        }

        .employee-dashboard main {
            padding: 0 !important;
            padding-top: 47px !important;
        }

        @media (min-width: 640px) {
            .employee-dashboard main {
                padding-top: 63px !important;
            }
        }

        .employee-dashboard footer {
            padding-left: 0 !important;
            padding-right: 0 !important;
            background: #93c5fd;
        }

        .employee-dashboard.employee-footer-space main {
            padding-bottom: 5rem;
        }

        .employee-dashboard .employee-footer {
            height: 4rem;
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }

        .employee-dashboard .employee-footer nav > div {
            height: 100%;
        }

        .employee-float-in {
            animation: employee-float-in 0.5s ease-out both;
        }

        @keyframes employee-float-in {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="min-h-screen employee-app-shell">
        <div class="w-full max-w-none mx-0 px-0 py-8">
            <div class="employee-float-in" style="animation-delay: 0ms;">
                <div class="flex justify-center px-4">
                    <x-application-logo class="w-[80%] max-w-[320px] sm:w-40 h-auto object-contain" />
                </div>
                <div class="mt-4 text-center">
                    <div class="text-3xl sm:text-4xl font-semibold text-gray-900">
                        {{ $employee?->getFullName() ?? 'Employee' }}
                    </div>
                    <div class="text-sm text-gray-900 mt-1 font-semibold">Badge No: {{ $employee?->badge_number ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5 px-4 sm:px-0">
                <div class="employee-card employee-card-light employee-time-card employee-float-in p-6 sm:p-7 text-center" style="animation-delay: 60ms;">
                    <div id="ph-time" class="font-light text-gray-900 leading-none" style="font-size: clamp(2.8rem, 7.5vw, 4.5rem);">--:--:--</div>
                    <div id="ph-date" class="mt-2 text-sm text-gray-600">---</div>
                </div>
                <div class="employee-card employee-card-accent employee-float-in p-3 sm:p-4" style="animation-delay: 120ms;">
                    <div class="text-center text-sm sm:text-base text-blue-800">
                        <span>Today's Log: </span>
                        <span class="font-bold text-blue-900">{{ $todayLogs }}</span>
                        <span class="mx-5 text-blue-400">|</span>
                        <span class="mx-5">This Month: </span>
                        <span class="font-bold text-blue-900">{{ $monthLogs }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-6 px-4 sm:px-0">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('activities.index') }}" class="employee-card employee-float-in p-5 bg-orange-200 hover:bg-orange-300 transition" style="animation-delay: 150ms; background-color: #fb923c;">
                        <div class="flex items-center gap-2 text-xs uppercase tracking-widest text-white">
                            <i class="fa-solid fa-calendar-check text-3xl text-white"></i>
                            <span>Personal Activities</span>
                        </div>
                    </a>
                    <a href="{{ route('leaves.index') }}" class="employee-card employee-float-in p-5 bg-green-100 hover:bg-green-200 transition" style="animation-delay: 210ms; background-color: #22c55e;">
                        <div class="flex items-center gap-2 text-xs uppercase tracking-widest text-white">
                            <i class="fa-solid fa-umbrella-beach text-3xl text-white"></i>
                            <span>Leave Management</span>
                        </div>
                    </a>
                    <a href="{{ route('attendance-logs.daily-time-record') }}" class="employee-card employee-float-in p-5 bg-indigo-100 hover:bg-indigo-200 transition" style="animation-delay: 270ms; background-color: #4f46e5;">
                        <div class="flex items-center gap-2 text-xs uppercase tracking-widest text-white">
                            <i class="fa-solid fa-clock text-3xl text-white"></i>
                            <span>Daily Time Record</span>
                        </div>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="employee-card employee-float-in p-5 bg-blue-100 hover:bg-blue-200 transition" style="animation-delay: 330ms; background-color: #3b82f6;">
                        <div class="flex items-center gap-2 text-xs uppercase tracking-widest text-white">
                            <i class="fa-solid fa-user text-3xl text-white"></i>
                            <span>Personal Profile</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.body.classList.add('employee-dashboard');
            const timeEl = document.getElementById('ph-time');
            const dateEl = document.getElementById('ph-date');
            if (!timeEl || !dateEl) return;

            const timeFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const dateFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: '2-digit'
            });

            const updateClock = () => {
                const now = new Date();
                timeEl.textContent = timeFormatter.format(now);
                dateEl.textContent = dateFormatter.format(now);
            };

            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
</x-admin-layout>