{{-- ============================================================ --}}
{{-- FIXED FILTER BAR --}}
{{-- ============================================================ --}}
<div class="fixed top-14 md:top-0 left-0 right-0 md:left-[72px] z-[55]" x-data="{ openFilter: null }" x-ref="filterBar"
    x-init="
        const bar = $refs.filterBar;
        const ro = new ResizeObserver(() => {
            document.documentElement.style.setProperty('--filter-bar-h', bar.offsetHeight + 'px');
        });
        ro.observe(bar);
        $cleanup(() => ro.disconnect());
    ">
    <div class="bg-[#0a0a0f]/80 backdrop-blur-[20px] saturate-[180%] border-b border-white/[0.08]">
        <div class="max-w-5xl mx-auto px-4 py-2.5">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Total count --}}
                @php $totalImages = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $history->total() : $historyCollection->count(); @endphp
                @if($totalImages > 0)
                    <span class="text-white/30 text-xs font-medium shrink-0 mr-1"><i
                            class="fa-solid fa-images mr-1"></i>{{ $totalImages }} ảnh</span>
                @endif

                {{-- Date Filter --}}
                <div class="relative">
                    <button @click="openFilter = openFilter === 'date' ? null : 'date'" class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
                            {{ $filterDate !== 'all'
    ? 'bg-purple-500/20 border border-purple-500/40 text-purple-300'
    : 'bg-white/[0.05] border border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:text-white/90' }}">
                        <i class="fa-regular fa-calendar text-xs"></i>
                        <span>{{ $filterDate === 'all' ? 'Theo ngày' : ['week' => 'Tuần qua', 'month' => 'Tháng qua', '3months' => '3 tháng'][$filterDate] ?? 'Theo ngày' }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform"
                            :class="openFilter === 'date' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFilter === 'date'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 mt-2 w-52 p-1.5 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-50">
                        @foreach(['all' => 'Tất cả', 'week' => 'Tuần qua', 'month' => 'Tháng qua', '3months' => '3 tháng qua'] as $val => $lbl)
                            <button wire:click="$set('filterDate', '{{ $val }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors duration-150
                                                    {{ $filterDate === $val ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span>{{ $lbl }}</span>
                                @if($filterDate === $val)
                                    <i class="fa-solid fa-check text-purple-400 text-xs"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Model Filter --}}
                <div class="relative">
                    <button @click="openFilter = openFilter === 'model' ? null : 'model'" class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
                            {{ $filterModel !== 'all'
    ? 'bg-purple-500/20 border border-purple-500/40 text-purple-300'
    : 'bg-white/[0.05] border border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:text-white/90' }}">
                        <i class="fa-solid fa-microchip text-xs"></i>
                        <span
                            class="hidden sm:inline">{{ $filterModel === 'all' ? 'Theo model' : (collect($availableModels)->firstWhere('id', $filterModel)['name'] ?? $filterModel) }}</span>
                        <span class="sm:hidden">Model</span>
                        <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform"
                            :class="openFilter === 'model' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFilter === 'model'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 mt-2 w-56 p-1.5 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-50">
                        <button wire:click="$set('filterModel', 'all')" @click="openFilter = null"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors duration-150
                                {{ $filterModel === 'all' ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                            <span>Tất cả model</span>
                            @if($filterModel === 'all')
                                <i class="fa-solid fa-check text-purple-400 text-xs"></i>
                            @endif
                        </button>
                        @foreach($availableModels as $model)
                            <button wire:click="$set('filterModel', '{{ $model['id'] }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors duration-150
                                                    {{ $filterModel === $model['id'] ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span>{{ $model['name'] }}</span>
                                @if($filterModel === $model['id'])
                                    <i class="fa-solid fa-check text-purple-400 text-xs"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Ratio Filter --}}
                <div class="relative">
                    <button @click="openFilter = openFilter === 'ratio' ? null : 'ratio'" class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
                            {{ $filterRatio !== 'all'
    ? 'bg-purple-500/20 border border-purple-500/40 text-purple-300'
    : 'bg-white/[0.05] border border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:text-white/90' }}">
                        <i class="fa-solid fa-crop text-xs"></i>
                        <span>{{ $filterRatio === 'all' ? 'Tỉ lệ' : $filterRatio }}</span>
                        <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 transition-transform"
                            :class="openFilter === 'ratio' ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="openFilter === 'ratio'" x-cloak @click.away="openFilter = null"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full left-0 mt-2 w-44 p-1.5 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-50">
                        @foreach(['all' => 'Tất cả', 'auto' => 'Auto', '1:1' => '1:1', '16:9' => '16:9', '9:16' => '9:16', '4:3' => '4:3', '3:4' => '3:4', '3:2' => '3:2', '2:3' => '2:3', '5:4' => '5:4', '4:5' => '4:5', '21:9' => '21:9'] as $val => $lbl)
                            <button wire:click="$set('filterRatio', '{{ $val }}')" @click="openFilter = null"
                                class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-colors duration-150
                                                    {{ $filterRatio === $val ? 'text-white/95 bg-white/[0.06]' : 'text-white/70 hover:bg-white/[0.06] hover:text-white' }}">
                                <span>{{ $lbl }}</span>
                                @if($filterRatio === $val)
                                    <i class="fa-solid fa-check text-purple-400 text-xs"></i>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Reset --}}
                @if($filterDate !== 'all' || $filterModel !== 'all' || $filterRatio !== 'all')
                    <button wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium bg-red-500/10 border border-red-500/20 text-red-400 hover:bg-red-500/20 transition-all duration-200 active:scale-[0.98]">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Xóa lọc</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>