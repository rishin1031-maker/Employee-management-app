<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Portal') — EMS</title>
    <x-theme-init />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans text-gray-900 dark:text-gray-100 transition-colors duration-200">

<x-layout-with-sidebar storage-key="ems-employee-sidebar">
    <x-slot:sidebar>
        <div class="flex items-center border-b border-white/10 flex-shrink-0"
             :class="collapsed ? 'flex-col gap-2 px-2 py-4' : 'justify-between gap-3 px-4 py-5'">
            <div class="flex items-center gap-3 min-w-0" :class="collapsed && 'justify-center'">
                <div class="bg-gradient-to-br from-teal-500 to-teal-700 rounded-xl p-2.5 flex-shrink-0 shadow-lg shadow-teal-900/40">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
                <div x-show="!collapsed" x-cloak class="min-w-0">
                    <p class="text-sm font-bold truncate">My Portal</p>
                    <p class="text-xs text-gray-400 truncate">{{ Auth::guard('employee')->user()->employee_id }}</p>
                </div>
            </div>
            <x-sidebar-toggle />
        </div>

        <nav class="flex-1 px-2 py-6 space-y-1 overflow-y-auto" :class="!collapsed && 'px-4'">
            @php
                $nav = [
                    ['route' => 'employee.dashboard',         'icon' => 'fa-gauge-high',       'label' => 'Dashboard'],
                    ['route' => 'employee.attendance.index', 'icon' => 'fa-clock',          'label' => 'Attendance', 'active' => 'employee.attendance.index'],
                    ['route' => 'employee.attendance.charts', 'icon' => 'fa-chart-bar',     'label' => 'Work Hours', 'active' => 'employee.attendance.charts'],
                    ['route' => 'employee.leave.index',       'icon' => 'fa-calendar-xmark', 'label' => 'My Leaves'],
                    ['route' => 'employee.profile',           'icon' => 'fa-user-circle',    'label' => 'My Profile'],
                ];
            @endphp
            @foreach($nav as $item)
                <x-sidebar-nav-link
                    :href="route($item['route'])"
                    :icon="$item['icon']"
                    :label="$item['label']"
                    :active="request()->routeIs($item['active'] ?? $item['route'])"
                    accent="teal"
                />
            @endforeach
        </nav>

        <div class="px-2 py-4 border-t border-white/10 space-y-1 flex-shrink-0" :class="!collapsed && 'px-4'">
            <div class="flex items-center py-2"
                 :class="collapsed ? 'justify-center px-0' : 'justify-between px-4'">
                <span x-show="!collapsed" x-cloak class="text-xs text-gray-400">Theme</span>
                <x-theme-toggle class="!p-1.5 text-gray-400 hover:bg-gray-800 hover:text-white" />
            </div>
            <form method="POST" action="{{ route('employee.logout') }}">
                @csrf
                <button type="submit"
                        title="Logout"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-red-700 hover:text-white transition"
                        :class="collapsed ? 'justify-center !px-2' : ''">
                    <i class="fas fa-right-from-bracket w-5 text-center flex-shrink-0"></i>
                    <span x-show="!collapsed" x-cloak>Logout</span>
                </button>
            </form>
        </div>
    </x-slot:sidebar>

    <header class="ems-header px-6 py-3.5 flex items-center justify-between transition-colors duration-200 sticky top-0 z-10">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button"
                    @click="toggle()"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100 truncate leading-tight">@yield('page-title', 'Dashboard')</h1>
                <p class="text-xs text-gray-400 hidden sm:block">Employee Portal</p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            <x-theme-toggle />

            <div class="ems-clock text-right hidden sm:block">
                <p id="live-time" class="text-sm font-semibold text-gray-800 dark:text-gray-100 tabular-nums"></p>
                <p id="live-date" class="text-xs text-gray-400"></p>
            </div>

            <x-notification-bell guard="employee" />

            <div class="ems-user-chip flex items-center gap-2.5">
                <img src="{{ Auth::guard('employee')->user()->image_url }}"
                     class="w-8 h-8 rounded-full object-cover ring-2 ring-teal-500/30"
                     alt="">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200 hidden sm:inline pr-1">{{ Auth::guard('employee')->user()->name }}</span>
            </div>
        </div>
    </header>

    <script>
        function updateClock() {
            const now = new Date();

            const time = now.toLocaleTimeString('en-US', {
                hour:   '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const date = now.toLocaleDateString('en-US', {
                weekday: 'long',
                day:     'numeric',
                month:   'long',
                year:    'numeric'
            });

            document.getElementById('live-time').textContent = time;
            document.getElementById('live-date').textContent = date;
        }

        updateClock();
        setInterval(updateClock, 1000);
    </script>

    <main class="ems-main flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
        <x-flash-messages />

        @yield('content')
    </main>
</x-layout-with-sidebar>

</body>
</html>
