<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'My Portal') — EMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen overflow-hidden">

    <aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <div class="bg-teal-600 rounded-lg p-2">
                <i class="fas fa-user text-white text-lg"></i>
            </div>
            <div>
                <p class="text-sm font-bold">My Portal</p>
                <p class="text-xs text-gray-400">{{ Auth::guard('employee')->user()->employee_id }}</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1">
            @php
                $nav = [
                    ['route' => 'employee.dashboard',       'icon' => 'fa-gauge-high',  'label' => 'Dashboard'],
                    ['route' => 'employee.attendance.index','icon' => 'fa-clock',        'label' => 'Attendance'],
                    ['route' => 'employee.leave.index',     'icon' => 'fa-calendar-xmark','label' => 'My Leaves'],
                    ['route' => 'employee.profile',         'icon' => 'fa-user-circle',  'label' => 'My Profile'],
                ];
            @endphp
            @foreach($nav as $item)
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs($item['route']) ? 'bg-teal-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i class="fas {{ $item['icon'] }} w-5 text-center"></i> {{ $item['label'] }}
            </a>
            @endforeach
        </nav>

        <div class="px-4 py-4 border-t border-gray-700">
            <form method="POST" action="{{ route('employee.logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-red-700 hover:text-white transition">
                    <i class="fas fa-right-from-bracket w-5 text-center"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden">
    <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>

        <div class="flex items-center gap-4">
            {{-- Bell --}}
            <x-notification-bell guard="employee" />

            {{-- Employee avatar --}}
            <div class="flex items-center gap-3">
                <img src="{{ Auth::guard('employee')->user()->image_url }}"
                    class="w-8 h-8 rounded-full object-cover border border-gray-200">
                <span class="text-sm text-gray-700">{{ Auth::guard('employee')->user()->name }}</span>
            </div>
        </div>
    </header>

        <main class="flex-1 overflow-y-auto p-6">
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-circle-check"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 flex items-center gap-3 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-triangle-exclamation"></i> {{ session('warning') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-circle-xmark"></i> {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
