@props([
    'href',
    'icon',
    'label',
    'active' => false,
    'activeClass' => 'bg-indigo-600 text-white',
    'accent' => 'indigo',
])

<a href="{{ $href }}"
   title="{{ $label }}"
   {{ $attributes->merge([
       'class' => 'ems-nav-link flex items-center gap-3 px-4 py-2.5 text-sm font-medium '
           . ($active
               ? ($accent === 'teal' ? 'is-active-teal' : 'is-active')
               : 'text-gray-300 hover:bg-white/5 hover:text-white'),
   ]) }}
   :class="collapsed ? 'justify-center !px-2' : ''">
    <i class="fas {{ $icon }} w-5 text-center flex-shrink-0"></i>
    <span x-show="!collapsed" x-cloak class="truncate">{{ $label }}</span>
</a>
