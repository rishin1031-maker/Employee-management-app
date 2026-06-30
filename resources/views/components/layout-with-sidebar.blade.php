@props([
    'storageKey' => 'ems-sidebar-collapsed',
])

<div
    x-data="{
        collapsed: localStorage.getItem(@js($storageKey)) === '1',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem(@js($storageKey), this.collapsed ? '1' : '0');
        },
    }"
    class="flex h-screen overflow-hidden"
>
    <aside
        :class="collapsed ? 'w-[4.5rem]' : 'w-64'"
        class="ems-sidebar text-white flex flex-col flex-shrink-0 transition-[width] duration-200 ease-in-out overflow-hidden"
    >
        {{ $sidebar }}
    </aside>

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
        {{ $slot }}
    </div>
</div>
