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
        @php
            $totalImages = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $history->total() : $historyCollection->count();
            $activeFilterCount = 0;
            if ($filterDate !== 'all')
                $activeFilterCount++;
            if ($filterModel !== 'all')
                $activeFilterCount++;
            if ($filterRatio !== 'all')
                $activeFilterCount++;
        @endphp
        <div class="max-w-5xl mx-auto px-4 py-1.5">
            <div class="flex items-center justify-between">
                {{-- Left: Mobile Filter Button (moved to left) + Desktop Total count --}}
                <div class="flex items-center gap-3">
                    {{-- MOBILE FILTER BUTTON --}}
                    <button @click="openFilter = 'mobile_sheet'"
                        class="sm:hidden glass-chip inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $activeFilterCount > 0 ? 'glass-chip-active' : '' }}">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                        <span>Bộ lọc {{ $activeFilterCount > 0 ? "($activeFilterCount)" : '' }}</span>
                    </button>

                    {{-- Image Count (Desktop Only) --}}
                    @if($totalImages > 0)
                        <span class="hidden sm:inline-flex text-white/40 text-xs font-medium"><i
                                class="fa-solid fa-images mr-1.5"></i>{{ $totalImages }} ảnh</span>
                    @endif
                </div>

                {{-- Right: Controls --}}
                <div class="flex items-center gap-2">

                    {{-- DESKTOP FILTER CONTROLS --}}
                    <div class="hidden sm:flex items-center gap-1.5">
                        {{-- Date Filter Desktop --}}
                        <div class="relative shrink-0">
                            <button @click="openFilter = openFilter === 'date' ? null : 'date'"
                                class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterDate !== 'all' ? 'glass-chip-active' : '' }}">
                                <i class="fa-regular fa-calendar text-[10px]"></i>
                                <span>{{ $filterDate === 'all' ? 'Ngày' : ['week' => 'Tuần', 'month' => 'Tháng', '3months' => '3T'][$filterDate] ?? 'Ngày' }}</span>
                                    <span @click.stop="$wire.set('filterDate', 'all')" title="Xóa bộ lọc Ngày"
                                        class="ml-0.5 hover:text-white cursor-pointer"><i
                                            class="fa-solid fa-xmark text-[9px]"></i></span>
                                @else
                                    <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform" aria-hidden="true"
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

                        {{-- Model Filter Desktop --}}
                        <div class="relative shrink-0">
                            <button @click="openFilter = openFilter === 'model' ? null : 'model'"
                                class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterModel !== 'all' ? 'glass-chip-active' : '' }}">
                                <i class="fa-solid fa-microchip text-[10px]"></i>
                                <span
                                    class="max-w-[80px] truncate">{{ $filterModel === 'all' ? 'Model' : (collect($availableModels)->firstWhere('id', $filterModel)['name'] ?? $filterModel) }}</span>
                                @if($filterModel !== 'all')
                                    <span @click.stop="$wire.set('filterModel', 'all')" title="Xóa bộ lọc Model"
                                        class="ml-0.5 hover:text-white cursor-pointer"><i
                                            class="fa-solid fa-xmark text-[9px]"></i></span>
                                @else
                                    <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform" aria-hidden="true"
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
                                    <button wire:click="$set('filterModel', '{{ $model['id'] }}')"
                                        @click="openFilter = null"
                                        class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors duration-150
                                                                    {{ $filterModel === $model['id'] ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                        <span class="truncate pr-2">{{ $model['name'] }}</span>
                                        @if($filterModel === $model['id'])
                                            <i class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Ratio Filter Desktop --}}
                        <div class="relative shrink-0">
                            <button @click="openFilter = openFilter === 'ratio' ? null : 'ratio'"
                                class="glass-chip inline-flex items-center gap-1 h-8 px-3 rounded-lg text-xs font-medium transition-all duration-200 active:scale-[0.98] {{ $filterRatio !== 'all' ? 'glass-chip-active' : '' }}">
                                <i class="fa-solid fa-crop text-[10px]"></i>
                                <span>{{ $filterRatio === 'all' ? 'Tỉ lệ' : $filterRatio }}</span>
                                @if($filterRatio !== 'all')
                                    <span @click.stop="$wire.set('filterRatio', 'all')" title="Xóa bộ lọc Tỉ lệ"
                                        class="ml-0.5 hover:text-white cursor-pointer"><i
                                            class="fa-solid fa-xmark text-[9px]"></i></span>
                                @else
                                    <i class="fa-solid fa-chevron-down text-[8px] ml-0.5 transition-transform" aria-hidden="true"
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

                        {{-- Reset all Desktop --}}
                        @if($activeFilterCount > 0)
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
    </div>

    {{-- MOBILE MASTER FILTER BOTTOM SHEET --}}
    <template x-teleport="body">
        <div x-show="openFilter === 'mobile_sheet'" x-cloak
            class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-sm"
            @click.self="openFilter = null">
            <div x-show="openFilter === 'mobile_sheet'" @click.stop
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full"
                x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh] border-b-0 pb-safe">

                {{-- Sheet Header --}}
                <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                    <span class="text-white font-semibold text-base">Bộ lọc hiển thị</span>
                    <button @click="openFilter = null" aria-label="Đóng bảng lọc"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Sheet Body --}}
                <div class="p-4 overflow-y-auto space-y-6">

                    {{-- Date Section --}}
                    <div>
                        <div class="text-white/40 text-[11px] font-medium mb-2.5 uppercase tracking-wider">Thời gian
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['all' => 'Tất cả', 'week' => 'Tuần qua', 'month' => 'Tháng qua', '3months' => '3 tháng qua'] as $val => $lbl)
                                <button wire:click="$set('filterDate', '{{ $val }}')"
                                    class="flex items-center justify-center p-2.5 rounded-xl transition-all text-sm
                                                                {{ $filterDate === $val ? 'bg-purple-500/20 text-white border border-purple-500/30 font-medium' : 'bg-white/5 border border-transparent text-white/70 active:bg-white/10' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Ratio Section --}}
                    <div>
                        <div class="text-white/40 text-[11px] font-medium mb-2.5 uppercase tracking-wider">Tỉ lệ khung
                            hình</div>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(['all' => 'Tất cả', 'auto' => 'Auto', '1:1' => '1:1', '16:9' => '16:9', '9:16' => '9:16', '4:3' => '4:3', '3:4' => '3:4', '3:2' => '3:2', '2:3' => '2:3', '5:4' => '5:4', '4:5' => '4:5', '21:9' => '21:9'] as $val => $lbl)
                                <button type="button" wire:click="$set('filterRatio', '{{ $val }}')"
                                    class="flex items-center justify-center py-2 rounded-xl transition-all text-[11px]
                                                            {{ $filterRatio === $val ? 'bg-purple-500/20 text-white border border-purple-500/30 font-medium' : 'bg-white/5 border border-transparent text-white/70 active:bg-white/10' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Model Section --}}
                    <div>
                        <div class="text-white/40 text-[11px] font-medium mb-2.5 uppercase tracking-wider">Model xử lý
                        </div>
                        <div class="space-y-1.5">
                            <button wire:click="$set('filterModel', 'all')"
                                class="w-full flex items-center justify-between p-3 rounded-xl transition-all
                                    {{ $filterModel === 'all' ? 'bg-purple-500/20 text-white border border-purple-500/30' : 'bg-white/5 text-white/70 active:bg-white/10 border border-transparent' }}">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-border-all text-xs w-5 text-center text-white/40"></i>
                                    <span class="text-sm">Tất cả Models</span>
                                </div>
                                @if($filterModel === 'all')
                                    <i class="fa-solid fa-check text-purple-400 text-sm"></i>
                                @endif
                            </button>
                            @foreach($availableModels as $model)
                                <button wire:click="$set('filterModel', '{{ $model['id'] }}')"
                                    class="w-full flex items-center justify-between p-3 rounded-xl transition-all
                                                                {{ $filterModel === $model['id'] ? 'bg-purple-500/20 text-white border border-purple-500/30' : 'bg-white/5 text-white/70 active:bg-white/10 border border-transparent' }}">
                                    <div class="flex items-center gap-3 min-w-0 pr-4">
                                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 {{ $filterModel === $model['id'] ? 'bg-purple-500' : 'bg-white/10' }}">
                                            <span class="text-xs">{{ $model['icon'] ?? '⚡' }}</span>
                                        </div>
                                        <div class="text-left truncate">
                                            <div class="text-sm text-white truncate">{{ $model['name'] }}</div>
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

                {{-- Sheet Footer --}}
                <div class="p-4 border-t border-white/5 shrink-0 flex items-center gap-3">
                    <button wire:click="resetFilters"
                        class="h-11 px-4 rounded-xl font-medium transition-all text-sm flex-1 flex items-center justify-center gap-2
                            {{ $activeFilterCount > 0 ? 'bg-red-500/10 text-red-400 hover:bg-red-500/20' : 'bg-white/5 text-white/30 cursor-not-allowed' }}"
                        {{ $activeFilterCount === 0 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-rotate-right"></i> Đặt lại
                    </button>
                    <button @click="openFilter = null"
                        class="h-11 px-4 rounded-xl bg-purple-500 text-white font-medium hover:bg-purple-600 transition-all text-sm flex-[2] flex items-center justify-center">
                        Áp dụng & Đóng
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>