<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EMS Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <div class="bg-indigo-600 rounded-lg p-2">
                <i class="fas fa-users text-white text-lg"></i>
            </div>
            <span class="text-xl font-bold tracking-wide">EMS Admin</span>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i class="fas fa-gauge-high w-5 text-center"></i> Dashboard
            </a>
            <a href="{{ route('employees.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('employees.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i class="fas fa-id-card w-5 text-center"></i> Employees
            </a>
            <a href="{{ route('departments.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('departments.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i class="fas fa-building w-5 text-center"></i> Departments
            </a>
            <a href="{{ route('designations.index') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium
                      {{ request()->routeIs('designations.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                <i class="fas fa-briefcase w-5 text-center"></i> Designations
            </a>
        </nav>

        <div class="px-4 py-4 border-t border-gray-700">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-3 w-full px-4 py-2.5 rounded-lg text-sm font-medium text-gray-300 hover:bg-red-700 hover:text-white transition">
                    <i class="fas fa-right-from-bracket w-5 text-center"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Topbar --}}
        <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white text-sm font-bold">
                    {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm text-gray-700">{{ Auth::guard('admin')->user()->name }}</span>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    <i class="fas fa-circle-check"></i> {{ session('success') }}
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