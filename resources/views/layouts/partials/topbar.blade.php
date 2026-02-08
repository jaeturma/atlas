<nav class="app-topbar fixed top-0 z-40 border-b border-[#FBC968] dark:bg-gray-800 dark:border-gray-700" style="background-color:#FBC968;">
  <div class="px-3 py-3 lg:px-5 lg:pl-3">
    <div class="flex items-center justify-between">
        <div class="flex items-center justify-start rtl:justify-end">
        <button id="sidebarToggle" type="button" class="inline-flex items-center p-2 text-sm text-gray-900 rounded-lg hover:bg-orange-300 focus:outline-none focus:ring-2 focus:ring-white/50" title="Toggle sidebar">
          <span class="sr-only">Toggle sidebar</span>
          <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 15.25zm0-5.25a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z" />
          </svg>
        </button>
        <div class="ml-3 hidden sm:flex items-center text-gray-900" aria-live="polite">
          <span id="pstTime" class="text-lg font-bold">--:--</span>
          <span class="mx-2 text-gray-500">•</span>
          <span id="pstDate" class="text-sm font-medium text-gray-700">--</span>
        </div>
      </div>
      @php
        $currentUser = auth()->user();
        $displayName = $currentUser?->name
            ?? $currentUser?->employee?->getFullName()
            ?? $currentUser?->email
            ?? 'User';
      @endphp
      <div class="flex items-center ms-auto min-w-0">
          <div class="flex items-center ms-3 gap-2 min-w-0">
            <span class="inline-flex items-center text-sm font-semibold text-gray-900 bg-white/80 px-3 py-1 rounded-full border border-gray-900/20 whitespace-nowrap max-w-[220px] truncate">
              {{ $displayName }}
            </span>
            <div>
              <button type="button" class="flex items-center gap-2 text-sm bg-white/50 rounded-full px-2 py-1 focus:ring-4 focus:ring-white/50" aria-expanded="false" data-dropdown-toggle="dropdown-user" title="{{ $displayName }}">
                <span class="sr-only">Open user menu</span>
                <div class="w-8 h-8 rounded-full bg-white/60 flex items-center justify-center text-gray-900 font-semibold">
                  {{ substr($displayName, 0, 1) }}
                </div>
              </button>
            </div>
            <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow dark:bg-gray-700 dark:divide-gray-600" id="dropdown-user">
              <div class="px-4 py-3" role="none">
                <p class="text-sm text-gray-900 dark:text-white" role="none">
                  {{ Auth::user()->name ?? 'User' }}
                </p>
                <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                  {{ Auth::user()->email ?? '' }}
                </p>
              </div>
              <ul class="py-1" role="none">
                <li>
                  <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Dashboard</a>
                </li>
                <li>
                  <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Settings</a>
                </li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white" role="menuitem">Sign out</button>
                  </form>
                </li>
              </ul>
            </div>
          </div>
        </div>
    </div>
  </div>
</nav>

<script>
  (function () {
    const timeTarget = document.getElementById('pstTime');
    const dateTarget = document.getElementById('pstDate');
    if (!timeTarget || !dateTarget) return;

    const timeFormatter = new Intl.DateTimeFormat('en-PH', {
      timeZone: 'Asia/Manila',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
      hour12: true,
    });

    const dateFormatter = new Intl.DateTimeFormat('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    });

    const update = () => {
      const now = new Date();
      timeTarget.textContent = timeFormatter.format(now);
      dateTarget.textContent = `${dateFormatter.format(now)} PST`;
    };

    update();
    setInterval(update, 1000);
  })();
</script>

<script>
  (function () {
    const toggleButton = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('logo-sidebar');
    if (!toggleButton || !sidebar) return;

    const overlayId = 'sidebarOverlay';
    const getOverlay = () => document.getElementById(overlayId);

    const openSidebar = () => {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
      document.body.classList.add('overflow-hidden');

      let overlay = getOverlay();
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = overlayId;
        overlay.className = 'fixed inset-0 z-[55] bg-black/40 sm:hidden';
        overlay.addEventListener('click', closeSidebar);
        document.body.appendChild(overlay);
      }
    };

    const closeSidebar = () => {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
      document.body.classList.remove('overflow-hidden');
      const overlay = getOverlay();
      if (overlay) overlay.remove();
    };

    const toggleSidebar = () => {
      const isHidden = sidebar.classList.contains('-translate-x-full');
      if (isHidden) {
        openSidebar();
      } else {
        closeSidebar();
      }
    };

    toggleButton.addEventListener('click', (event) => {
      event.preventDefault();
      toggleSidebar();
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth >= 640) {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        document.body.classList.remove('overflow-hidden');
        const overlay = getOverlay();
        if (overlay) overlay.remove();
      } else {
        sidebar.classList.add('-translate-x-full');
      }
    });
  })();
</script>

