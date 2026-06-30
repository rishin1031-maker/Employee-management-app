<button type="button"
        @click="toggle()"
        :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        class="flex-shrink-0 p-1.5 rounded-lg text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
    <i class="fas text-xs w-4 text-center" :class="collapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
</button>
