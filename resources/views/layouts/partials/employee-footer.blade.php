@role('Employee')
    <footer class="employee-footer fixed bottom-0 left-0 right-0 z-50 border-t border-blue-700 bg-transparent print:hidden">
        <nav class="mx-auto max-w-5xl">
            <div class="flex items-center justify-around relative">
                     <a href="{{ route('activities.index') }}"
                         class="flex items-center justify-center py-0 text-xs font-medium text-slate-200"
                   aria-label="Activities">
                    <i class="fa-solid fa-calendar-check text-2xl text-slate-200" style="color: #3b3b3b;"></i>
                </a>
                            <a href="{{ route('leaves.index') }}"
                                class="flex items-center justify-center py-0 text-xs font-medium text-slate-200"
                   aria-label="Leave">
                    <i class="fa-solid fa-umbrella-beach text-2xl text-slate-200" style="color: #3b3b3b;"></i>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center justify-center py-0 text-[10px] font-semibold text-slate-200" aria-label="Dashboard">
                    <span class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-800 shadow-2xl -translate-y-7 ring-4 ring-blue-200">
                        <img src="{{ asset('images/atlas_sq.png') }}" alt="ATLAS" class="h-12 w-12 object-contain" />
                    </span>
                </a>
                <a href="{{ route('attendance-logs.daily-time-record') }}"
                         class="flex items-center justify-center py-0 text-xs font-medium text-slate-200"
                   aria-label="Daily Time Record">
                    <i class="fa-solid fa-clock text-2xl text-slate-200" style="color: #3b3b3b;"></i>
                </a>
                <a href="{{ route('profile.edit') }}"
                         class="flex items-center justify-center py-0 text-xs font-medium text-slate-200"
                   aria-label="Profile">
                    <i class="fa-solid fa-user text-2xl text-slate-200" style="color: #3b3b3b;"></i>
                </a>
            </div>
        </nav>
    </footer>
@endrole
