{{-- ============================================================ --}}
{{-- FILTER COMPACT — Sticky filter pills with per-pill clear --}}
{{-- ============================================================ --}}
<div class="fixed mobile-filter-top md:top-0 left-0 right-0 md:left-[72px] z-[55] t2i-filter-wrap"
    x-data="{ openFilter: null }" x-ref="filterBar" x-init="
        const bar = $refs.filterBar;
        const ro = new ResizeObserver(() => {
            document.documentElement.style.setProperty('--filter-bar-h', bar.offsetHeight + 'px');
        });
        ro.observe(bar);
        const stop = () => ro.disconnect();
        window.addEventListener('livewire:navigating', stop, { once: true });
    ">
    <div class="t2i-topbar">
        <div class="max-w-5xl mx-auto px-4 py-2">
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1">
                {{-- Total count --}}
                @php $totalImages = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $history->total() : $historyCollection->count(); @endphp
                @if($totalImages > 0)
                    <span class="text-white/30 text-xs font-medium shrink-0 mr-1"><i
                            class="fa-solid fa-images mr-1"></i>{{ $totalImages }} ảnh</span>
                @endif

                {{-- Date Filter --}}
                <div class="relative shrink-0">
                    <button @click="openFilter = openFilter === 'date' ? null : 'date'"
                        class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterDate !== 'all' ? 'glass-chip-active' : '' }}">
                        <i class="fa-regular fa-calendar text-[10px]"></i>
                        <span>{{ $filterDate === 'all' ? 'Ngày' : ['week' => 'Tuần', 'month' => 'Tháng', '3months' => '3T'][$filterDate] ?? 'Ngày' }}</span>
                        @if($filterDate !== 'all')
                            <span @click.stop="$wire.set('filterDate', 'all')"
                                class="ml-0.5 hover:text-white cursor-pointer"><i
                                    class="fa-solid fa-xmark text-[9px]"></i></span>
                        @else
                            <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform"
                                :class="openFilter === 'date' ? 'rotate-180' : ''"></i>
                        @endif
                    </button>
                    {{-- Date Dropdown Desktop --}}
                    <div x-show="openFilter === 'date'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover hidden sm:block absolute top-full left-0 mt-1.5 w-44 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
                        @foreach(['all' => 'Tất cả', 'week' => 'Tuần qua', 'month' => 'Tháng qua', '3months' => '3 tháng qua'] as $val => $lbl)
                            <button wire:click="$set('filterDate', '{{ $val }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors duration-150
                                                    {{ $filterDate === $val ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span>{{ $lbl }}</span>
                                @if($filterDate === $val)
                                    <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    
                    {{-- Date Bottom Sheet Mobile --}}
                    <template x-teleport="body">
                        <div x-show="openFilter === 'date'" x-cloak
                            class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-sm"
                            @click.self="openFilter = null">
                            <div x-show="openFilter === 'date'" @click.stop
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-y-0"
                                x-transition:leave-end="translate-y-full"
                                class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh] border-b-0 pb-safe">
                                <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                    <span class="text-white font-semibold text-base">Lọc theo Thời gian</span>
                                    <button @click="openFilter = null"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="p-2 overflow-y-auto">
                                    @foreach(['all' => 'Tất cả', 'week' => 'Tuần qua', 'month' => 'Tháng qua', '3months' => '3 tháng qua'] as $val => $lbl)
                                        <button wire:click="$set('filterDate', '{{ $val }}')" @click="openFilter = null"
                                            class="w-full flex items-center justify-between p-3.5 rounded-2xl transition-all mb-1
                                                {{ $filterDate === $val ? 'bg-purple-500/20 text-white border border-purple-500/30' : 'bg-transparent text-white/70 active:bg-white/5 border border-transparent' }}">
                                            <span class="text-sm font-medium">{{ $lbl }}</span>
                                            @if($filterDate === $val)
                                                <i class="fa-solid fa-check text-purple-400 text-sm"></i>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Model Filter --}}
                <div class="relative shrink-0">
                    <button @click="openFilter = openFilter === 'model' ? null : 'model'"
                        class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterModel !== 'all' ? 'glass-chip-active' : '' }}">
                        <i class="fa-solid fa-microchip text-[10px]"></i>
                        <span
                            class="max-w-[80px] truncate">{{ $filterModel === 'all' ? 'Model' : (collect($availableModels)->firstWhere('id', $filterModel)['name'] ?? $filterModel) }}</span>
                        @if($filterModel !== 'all')
                            <span @click.stop="$wire.set('filterModel', 'all')"
                                class="ml-0.5 hover:text-white cursor-pointer"><i
                                    class="fa-solid fa-xmark text-[9px]"></i></span>
                        @else
                            <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform"
                                :class="openFilter === 'model' ? 'rotate-180' : ''"></i>
                        @endif
                    </button>
                    {{-- Model Dropdown Desktop --}}
                    <div x-show="openFilter === 'model'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover hidden sm:block absolute top-full left-0 mt-1.5 w-52 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
                        <button wire:click="$set('filterModel', 'all')" @click="openFilter = null"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors duration-150
                                {{ $filterModel === 'all' ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                            <span>Tất cả</span>
                            @if($filterModel === 'all')
                                <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                            @endif
                        </button>
                        @foreach($availableModels as $model)
                            <button wire:click="$set('filterModel', '{{ $model['id'] }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors duration-150
                                                    {{ $filterModel === $model['id'] ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span class="truncate pr-2">{{ $model['name'] }}</span>
                                @if($filterModel === $model['id'])
                                    <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    
                    {{-- Model Bottom Sheet Mobile --}}
                    <template x-teleport="body">
                        <div x-show="openFilter === 'model'" x-cloak
                            class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-sm"
                            @click.self="openFilter = null">
                            <div x-show="openFilter === 'model'" @click.stop
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-y-0"
                                x-transition:leave-end="translate-y-full"
                                class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh] border-b-0 pb-safe">
                                <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                    <span class="text-white font-semibold text-base">Lọc theo Model</span>
                                    <button @click="openFilter = null"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="p-2 overflow-y-auto">
                                    <button wire:click="$set('filterModel', 'all')" @click="openFilter = null"
                                        class="w-full flex items-center justify-between p-3.5 rounded-2xl transition-all mb-1
                                            {{ $filterModel === 'all' ? 'bg-purple-500/20 text-white border border-purple-500/30' : 'bg-transparent text-white/70 active:bg-white/5 border border-transparent' }}">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-white/50"><i class="fa-solid fa-border-all text-xs"></i></div>
                                            <span class="text-sm font-medium">Tất cả Models</span>
                                        </div>
                                        @if($filterModel === 'all')
                                            <i class="fa-solid fa-check text-purple-400 text-sm"></i>
                                        @endif
                                    </button>
                                    @foreach($availableModels as $model)
                                        <button wire:click="$set('filterModel', '{{ $model['id'] }}')" @click="openFilter = null"
                                            class="w-full flex items-center justify-between p-3.5 rounded-2xl transition-all mb-1
                                                {{ $filterModel === $model['id'] ? 'bg-purple-500/20 text-white border border-purple-500/30' : 'bg-transparent text-white/70 active:bg-white/5 border border-transparent' }}">
                                            <div class="flex items-center gap-3 min-w-0 pr-4">
                                                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                                                    :class="$wire.filterModel === '{{ $model['id'] }}' ? 'bg-purple-500' : 'bg-white/10'">
                                                    <span class="text-sm">{{ $model['icon'] ?? '⚡' }}</span>
                                                </div>
                                                <div class="text-left truncate">
                                                    <div class="text-sm font-medium text-white truncate">{{ $model['name'] }}</div>
                                                    <div class="text-[10px] text-white/40 truncate">{{ $model['desc'] ?? '' }}</div>
                                                </div>
                                            </div>
                                            @if($filterModel === $model['id'])
                                                <i class="fa-solid fa-check text-purple-400 text-sm shrink-0"></i>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Ratio Filter --}}
                <div class="relative shrink-0">
                    <button @click="openFilter = openFilter === 'ratio' ? null : 'ratio'"
                        class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterRatio !== 'all' ? 'glass-chip-active' : '' }}">
                        <i class="fa-solid fa-crop text-[10px]"></i>
                        <span>{{ $filterRatio === 'all' ? 'Tỉ lệ' : $filterRatio }}</span>
                        @if($filterRatio !== 'all')
                            <span @click.stop="$wire.set('filterRatio', 'all')"
                                class="ml-0.5 hover:text-white cursor-pointer"><i
                                    class="fa-solid fa-xmark text-[9px]"></i></span>
                        @else
                            <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform"
                                :class="openFilter === 'ratio' ? 'rotate-180' : ''"></i>
                        @endif
                    </button>
                    {{-- Ratio Dropdown Desktop --}}
                    <div x-show="openFilter === 'ratio'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover hidden sm:block absolute top-full left-0 mt-1.5 w-40 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
                        @foreach(['all' => 'Tất cả', 'auto' => 'Auto', '1:1' => '1:1', '16:9' => '16:9', '9:16' => '9:16', '4:3' => '4:3', '3:4' => '3:4', '3:2' => '3:2', '2:3' => '2:3', '5:4' => '5:4', '4:5' => '4:5', '21:9' => '21:9'] as $val => $lbl)
                            <button wire:click="$set('filterRatio', '{{ $val }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors duration-150
                                                    {{ $filterRatio === $val ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span>{{ $lbl }}</span>
                                @if($filterRatio === $val)
                                    <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    
                    {{-- Ratio Bottom Sheet Mobile --}}
                    <template x-teleport="body">
                        <div x-show="openFilter === 'ratio'" x-cloak
                            class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-sm"
                            @click.self="openFilter = null">
                            <div x-show="openFilter === 'ratio'" @click.stop
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="translate-y-full"
                                x-transition:enter-end="translate-y-0"
                                x-transition:leave="transition ease-in duration-200"
                                x-transition:leave-start="translate-y-0"
                                x-transition:leave-end="translate-y-full"
                                class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh] border-b-0 pb-safe">
                                <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                    <span class="text-white font-semibold text-base">Lọc theo Tỉ lệ</span>
                                    <button @click="openFilter = null"
                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                                <div class="p-4 overflow-y-auto">
                                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
                                        @foreach(['all' => 'Tất cả', 'auto' => 'Auto', '1:1' => '1:1', '16:9' => '16:9', '9:16' => '9:16', '4:3' => '4:3', '3:4' => '3:4', '3:2' => '3:2', '2:3' => '2:3', '5:4' => '5:4', '4:5' => '4:5', '21:9' => '21:9'] as $val => $lbl)
                                            <button type="button" wire:click="$set('filterRatio', '{{ $val }}')" @click="openFilter = null"
                                                class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-xl transition-all
                                                {{ $filterRatio === $val ? 'bg-purple-500/30 border border-purple-500/50 text-white' : 'bg-white/5 border border-transparent text-white/70 active:bg-white/10' }}">
                                                <span class="text-[11px] font-medium">{{ $lbl }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Reset all (only if any filter active) --}}
                @if($filterDate !== 'all' || $filterModel !== 'all' || $filterRatio !== 'all')
                    <button wire:click="resetFilters"
                        class="inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition-all duration-200 active:scale-[0.98]">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                        <span>Xóa tất cả</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>