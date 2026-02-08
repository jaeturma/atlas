<aside id="logo-sidebar" class="fixed top-0 left-0 z-[60] w-64 h-screen transition-transform -translate-x-full bg-gray-200 border-r border-gray-300 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700" style="z-index:60;" aria-label="Sidebar">
   <div class="h-full px-3 pb-4 overflow-y-auto bg-gray-200 dark:bg-gray-800">
      <!-- Logo and App Name -->
      <div class="flex items-center p-3 mb-6 mt-4 border-b border-gray-200 dark:border-gray-700">
         <img id="sidebarLogo" src="{{ asset('images/atlas-logo.png') }}" data-full="{{ asset('images/atlas-logo.png') }}" data-collapsed="{{ asset('images/atlas.png') }}" alt="{{ config('app.name', 'Laravel') }}" class="w-full h-auto max-h-20 object-contain" />
      </div>
      <ul class="space-y-2 font-medium">
         <li>
            <a href="{{ route('dashboard') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                  <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                  <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
               </svg>
               <span class="sidebar-label ms-3">Dashboard</span>
            </a>
         </li>
         @role('Admin|Superadmin')
         <li>
            <a href="{{ route('devices.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('devices.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                  <path d="M16 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2ZM6 14H4V6h2v8Zm4 0H8V4h2v10Zm4 0h-2V9h2v5Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Devices</span>
            </a>
         </li>
         @endrole
         @role('Admin|Superadmin|Employee|DTR Incharge')
         <li>
            <a href="{{ route('activities.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('activities.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8 2a2 2 0 0 0-2 2v1H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V4a2 2 0 0 0-2-2H8Zm0 3V4h4v1H8Zm-1 3h6v2H7V8Zm0 4h6v2H7v-2Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Activities</span>
            </a>
         </li>
         @endrole
         @role('Admin|Superadmin|Employee|DTR Incharge|Leave Incharge|Leave Approver')
         <li>
            <a href="{{ route('leaves.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('leaves.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M5 3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1h1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1V3Zm2 1h6V3H7v1Zm-1 4h8v2H6V8Zm0 4h8v2H6v-2Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Leaves</span>
            </a>
         </li>
         @endrole
         @hasanyrole('Admin|Superadmin|DTR Incharge')
         <li>
            <a href="{{ route('employees.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('employees.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                  <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Employees</span>
            </a>
         </li>
         @endhasanyrole
         @role('Admin|Superadmin')
         <li>
            <a href="{{ route('departments.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('departments.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                  <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 9a1 1 0 0 1-2 0V7h2v2Zm0-5a2 2 0 1 1 4 0v1H7V4Zm6 5a1 1 0 1 1-2 0V7h2v2Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Departments</span>
            </a>
             </li>

                <li>
                      <a href="{{ route('positions.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('positions.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                  <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                     <path d="M10 2a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v2h2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-2v2a2 2 0 0 1-2 2h-2v2a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-2H4a2 2 0 0 1-2-2v-2H0a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2h2V8a2 2 0 0 1 2-2h2V4a2 2 0 0 1 2-2z"/>
                  </svg>
                  <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Positions</span>
               </a>
            </li>

            <li>
               <a href="{{ route('attendance-logs.live-monitor') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('attendance-logs.live-monitor') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                  <svg class="flex-shrink-0 w-5 h-5 text-green-500 transition duration-75 dark:text-green-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                     <path d="M4 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H4zm0 2h12v10H4V5zm2 2v6h8V7H6z"/>
                  </svg>
                  <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Live Monitoring</span>
               </a>
                </li>
             <li>
                  <a href="{{ route('holidays.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('holidays.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M6 1a1 1 0 0 0-2 0h2zM4 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4zm2-1v1m8-1v1m-5 5a1 1 0 1 0-2 0 1 1 0 0 0 2 0z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Holidays</span>
            </a>
         </li>
         @endrole
         <li>
            <a href="{{ route('attendance-logs.daily-time-record') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('attendance-logs.daily-time-record') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M6 2a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h9l5-5V4a2 2 0 0 0-2-2H6zm0 2h12v11h-4a1 1 0 0 0-1 1v4H6V4zm4 4a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2h-6zm0 4a1 1 0 1 0 0 2h4a1 1 0 1 0 0-2h-4z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Daily Time Record</span>
            </a>
         </li>
         @role('Admin|Superadmin')
         <li>
            <a href="{{ route('attendance-logs.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('attendance-logs.index') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 13.982a1 1 0 0 1-1.414 0l-3.274-3.274A1.012 1.012 0 0 1 9 10V6a1 1 0 0 1 2 0v3.586l2.982 2.982a1 1 0 0 1 0 1.414Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Attendance Logs</span>
            </a>
         </li>
         @endrole

         @role('Admin|Superadmin')
         <li>
            <button type="button" class="sidebar-item flex items-center w-full p-2 text-base text-gray-900 transition duration-75 rounded-lg group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700" aria-controls="dropdown-users" data-collapse-toggle="dropdown-users">
                  <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                     <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                  </svg>
               <span class="sidebar-label flex-1 ms-3 text-left rtl:text-right whitespace-nowrap">User Management</span>
               <svg class="sidebar-chevron w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                     <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                  </svg>
            </button>
            <ul id="dropdown-users" class="hidden py-2 space-y-2">
                  <li>
                     <a href="{{ route('users.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('users.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="ms-2">User List</span>
                     </a>
                  </li>
                  <li>
                     <a href="{{ route('roles.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('roles.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="ms-2">Roles</span>
                     </a>
                  </li>
                  <li>
                     <a href="{{ route('permissions.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('permissions.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="ms-2">Permissions</span>
                     </a>
                  </li>
                  @role('Superadmin')
                  <li>
                     <a href="{{ route('login-otps.index') }}" class="flex items-center w-full p-2 text-gray-900 transition duration-75 rounded-lg pl-11 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700 {{ request()->routeIs('login-otps.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
                        <span class="ms-2">Login OTP</span>
                     </a>
                  </li>
                  @endrole
            </ul>
         </li>
         @endrole

         @role('Superadmin')
         <li>
            <a href="{{ route('report-settings.index') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('report-settings.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M18 7.5h-.423l-.452-1.09.3-.3a1.5 1.5 0 0 0 0-2.121L16.01 2.575a1.5 1.5 0 0 0-2.121 0l-.3.3-1.089-.452V2A1.5 1.5 0 0 0 11 .5H9A1.5 1.5 0 0 0 7.5 2v.423l-1.09.452-.3-.3a1.5 1.5 0 0 0-2.121 0L2.576 3.99a1.5 1.5 0 0 0 0 2.121l.3.3L2.423 7.5H2A1.5 1.5 0 0 0 .5 9v2A1.5 1.5 0 0 0 2 12.5h.423l.452 1.09-.3.3a1.5 1.5 0 0 0 0 2.121l1.415 1.413a1.5 1.5 0 0 0 2.121 0l.3-.3 1.09.452V18A1.5 1.5 0 0 0 9 19.5h2a1.5 1.5 0 0 0 1.5-1.5v-.423l1.09-.452.3.3a1.5 1.5 0 0 0 2.121 0l1.415-1.414a1.5 1.5 0 0 0 0-2.121l-.3-.3.452-1.09H18a1.5 1.5 0 0 0 1.5-1.5V9A1.5 1.5 0 0 0 18 7.5Zm-8 6a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Report Settings</span>
            </a>
         </li>
         @endrole
         <li>
            <a href="{{ route('profile.edit') }}" class="sidebar-item flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group {{ request()->routeIs('profile.*') ? 'bg-gray-100 dark:bg-gray-700' : '' }}">
               <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z"/>
                    <path d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1.969 1.969 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z"/>
                    <path d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z"/>
               </svg>
               <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Profile</span>
            </a>
         </li>
         <li>
            <form method="POST" action="{{ route('logout') }}">
               @csrf
               <button type="submit" class="sidebar-item flex items-center justify-start w-full p-2 text-left text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group">
                  <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                     <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                  </svg>
                  <span class="sidebar-label flex-1 ms-3 whitespace-nowrap">Sign Out</span>
               </button>
            </form>
         </li>
      </ul>
   </div>
</aside>

