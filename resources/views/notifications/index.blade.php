@extends($guard === 'admin' ? 'layouts.app' : 'employee.layouts.app')
@section('title', 'Notifications')
@section('page-title', 'All Notifications')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 text-sm">Notifications</h2>
            <form method="POST"
                  action="{{ $guard === 'admin' ? route('admin.notifications.read-all') : route('employee.notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-xs text-indigo-600 hover:underline">Mark all as read</button>
            </form>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($notifications as $notif)
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
                $readRoute  = $guard === 'admin' ? 'admin.notifications.read' : 'employee.notifications.read';
            @endphp
            <a href="{{ route($readRoute, $notif->id) }}"
               class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition {{ $notif->read_at ? '' : 'bg-indigo-50/40' }}">
                <div class="w-10 h-10 rounded-full {{ $colorClass }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $data['icon'] ?? 'fa-bell' }} text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-medium text-gray-900 {{ $notif->read_at ? '' : 'font-semibold' }}">
                            {{ $data['title'] ?? '' }}
                        </p>
                        <span class="text-xs text-gray-400 flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $data['message'] ?? '' }}</p>
                    @if(!$notif->read_at)
                    <span class="inline-block mt-1.5 text-xs bg-indigo-100 text-indigo-600 px-2 py-0.5 rounded-full font-medium">Unread</span>
                    @endif
                </div>
            </a>
            @empty
            <div class="px-6 py-16 text-center">
                <i class="fas fa-bell-slash text-gray-300 text-3xl mb-3"></i>
                <p class="text-gray-400">No notifications yet.</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection