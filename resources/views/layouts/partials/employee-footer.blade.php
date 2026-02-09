@role('Employee')
    <footer class="employee-footer fixed bottom-0 left-0 right-0 z-50 border-t border-blue-700 bg-transparent print:hidden">
        <div class="mx-auto max-w-5xl relative">
            <nav>
                <div class="flex items-end justify-around pt-4">
                    <a href="{{ route('activities.index') }}"
                       class="flex items-center justify-center py-0 text-xs font-medium text-white"
                       aria-label="Activities">
                        <i class="fa-solid fa-calendar-check text-xl text-white"></i>
                    </a>
                    <a href="{{ route('leaves.index') }}"
                       class="flex items-center justify-center py-0 text-xs font-medium text-white"
                       aria-label="Leave">
                        <i class="fa-solid fa-umbrella-beach text-xl text-white"></i>
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center justify-center py-0 text-xs font-medium text-white"
                       aria-label="Dashboard">
                        <i class="fa-solid fa-house text-xl text-white"></i>
                    </a>
                    <a href="{{ route('attendance-logs.daily-time-record') }}"
                       class="flex items-center justify-center py-0 text-xs font-medium text-white"
                       aria-label="Daily Time Record">
                        <i class="fa-solid fa-clock text-xl text-white"></i>
                    </a>
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center justify-center py-0 text-xs font-medium text-white"
                       aria-label="Profile">
                        <i class="fa-solid fa-user text-xl text-white"></i>
                    </a>
                </div>
            </nav>
            
        </div>
    </footer>
@endrole
