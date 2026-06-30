@php
    $guard = $guard ?? 'admin';
    $user  = Auth::guard($guard)->user();
    $unread = $user ? $user->unreadNotifications->count() : 0;
    $recent = $user ? $user->notifications()->take(8)->get() : collect();
    $indexRoute   = $guard === 'admin' ? 'admin.notifications.index'    : 'employee.notifications.index';
    $readAllRoute = $guard === 'admin' ? 'admin.notifications.read-all' : 'employee.notifications.read-all';
    $readRoute    = $guard === 'admin' ? 'admin.notifications.read'     : 'employee.notifications.read';
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    {{-- Bell button --}}
    <button @click="open = !open"
            class="relative p-2 rounded-lg text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition focus:outline-none">
        <i class="fas fa-bell text-lg"></i>
        @if($unread > 0)
        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
            {{ $unread > 9 ? '9+' : $unread }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-transition
         class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-gray-800 dark:text-gray-100 text-sm">Notifications</h3>
            <div class="flex items-center gap-2">
                @if($unread > 0)
                <form method="POST" action="{{ route($readAllRoute) }}">
                    @csrf
                    <button type="submit" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Mark all read</button>
                </form>
                @endif
                <a href="{{ route($indexRoute) }}" class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">View all</a>
            </div>
        </div>

        {{-- List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-700">
            @forelse($recent as $notif)
            @php
                $data  = $notif->data;
                $color = $data['color'] ?? 'indigo';
                $colors = [
                    'green'  => 'bg-green-100 text-green-600',
                    'red'    => 'bg-red-100 text-red-600',
                    'yellow' => 'bg-yellow-100 text-yellow-600',
                    'blue'   => 'bg-blue-100 text-blue-600',
                    'indigo' => 'bg-indigo-100 text-indigo-600',
                ];
                $colorClass = $colors[$color] ?? $colors['indigo'];
            @endphp
            <a href="{{ route($readRoute, $notif->id) }}"
               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $notif->read_at ? '' : 'bg-indigo-50/40' }}">
                <div class="w-8 h-8 rounded-full {{ $colorClass }} flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas {{ $data['icon'] ?? 'fa-bell' }} text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 {{ $notif->read_at ? '' : 'font-semibold' }}">
                        {{ $data['title'] ?? 'Notification' }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
                @if(!$notif->read_at)
                <div class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0 mt-2"></div>
                @endif
            </a>
            @empty
            <div class="px-4 py-8 text-center">
                <i class="fas fa-bell-slash text-gray-300 dark:text-gray-600 text-2xl mb-2"></i>
                <p class="text-sm text-gray-400">No notifications yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
