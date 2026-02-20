{{-- ============================================================ --}}
{{-- FILTER COMPACT — Sticky filter pills with per-pill clear --}}
{{-- ============================================================ --}}
<div class="fixed top-[57px] md:top-0 left-0 right-0 md:left-[72px] z-[55] t2i-filter-wrap"
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
            <div class="flex items-center gap-1.5 flex-wrap">
                {{-- Total count --}}
                @php $totalImages = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $history->total() : $historyCollection->count(); @endphp
                @if($totalImages > 0)
                    <span class="text-white/30 text-xs font-medium shrink-0 mr-1"><i
                            class="fa-solid fa-images mr-1"></i>{{ $totalImages }} ảnh</span>
                @endif

                {{-- Date Filter --}}
                <div class="relative">
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
                    <div x-show="openFilter === 'date'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover absolute top-full left-0 mt-1.5 w-44 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
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
                </div>

                {{-- Model Filter --}}
                <div class="relative">
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
                    <div x-show="openFilter === 'model'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover absolute top-full left-0 mt-1.5 w-52 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
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
                                <span>{{ $model['name'] }}</span>
                                @if($filterModel === $model['id'])
                                    <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Ratio Filter --}}
                <div class="relative">
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
                    <div x-show="openFilter === 'ratio'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="glass-popover absolute top-full left-0 mt-1.5 w-40 p-1.5 rounded-xl z-50 max-h-60 overflow-y-auto">
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