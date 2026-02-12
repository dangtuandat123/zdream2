<div class="relative min-h-screen pb-48 md:pb-32" @if($isGenerating) wire:poll.2s="pollImageStatus" @endif
    x-data="textToImage" @keydown.window="handleKeydown($event)">

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl flex items-center gap-2"
        :class="{ 'bg-green-500/95': toastType==='success', 'bg-red-500/95': toastType==='error', 'bg-yellow-500/95 text-black': toastType==='warning' }">
        <i
            :class="{ 'fa-solid fa-check-circle': toastType==='success', 'fa-solid fa-exclamation-circle': toastType==='error', 'fa-solid fa-triangle-exclamation': toastType==='warning' }"></i>
        <span x-text="toastMessage"></span>
    </div>

    @php
        // 1. Grouping Logic
        $historyCollection = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? $history->getCollection()
            : collect($history);
        $groupedHistory = $historyCollection->groupBy(function ($item) {
            return $item->final_prompt . '|' .
                ($item->generation_params['model_id'] ?? '') . '|' .
                ($item->generation_params['aspect_ratio'] ?? '');
        })->reverse(); // Reverse so newest (from latest() query) display at bottom

        // 2. Flatten for JS (keep reversed order)
        $flatHistoryForJs = $groupedHistory->flatten(1)->map(fn($img) => [
            'id' => $img->id,
            'url' => $img->image_url,
            'prompt' => $img->final_prompt,
            'model' => $img->generation_params['model_id'] ?? null,
            'ratio' => $img->generation_params['aspect_ratio'] ?? null,
            'created_at' => $img->created_at->diffForHumans(),
        ])->values()->toArray();
    @endphp

    {{-- ============================================================ --}}
    {{-- FIXED FILTER BAR --}}
    {{-- ============================================================ --}}
    <div class="fixed top-14 md:top-0 left-0 right-0 md:left-[72px] z-[55]" x-data="{ openFilter: null }">
        <div class="bg-[#0a0a0f]/80 backdrop-blur-[20px] saturate-[180%] border-b border-white/[0.08]">
            <div class="max-w-5xl mx-auto px-4 py-2.5">
                <div class="flex items-center gap-2 flex-wrap">
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
                            @foreach(['all' => 'Tất cả', '1:1' => '1:1', '16:9' => '16:9', '9:16' => '9:16', '4:3' => '4:3', '3:4' => '3:4', '3:2' => '3:2', '2:3' => '2:3', '21:9' => '21:9'] as $val => $lbl)
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

    {{-- ============================================================ --}}
    {{-- SCROLLABLE GALLERY AREA --}}
    {{-- ============================================================ --}}
    <div id="gallery-scroll">
        <div class="max-w-5xl mx-auto px-4 pt-16 pb-48">

            {{-- Error --}}
            @if($errorMessage)
                <div x-data="{ show: true }" x-show="show" x-cloak
                    class="mb-4 p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3"
                    role="alert">
                    <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                    <span class="flex-1">{{ $errorMessage }}</span>
                    @if($lastPrompt)
                        <button wire:click="retry"
                            class="shrink-0 h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs font-medium text-white/80 transition-all active:scale-[0.98]">
                            <i class="fa-solid fa-redo mr-1"></i>Thử lại
                        </button>
                    @endif
                    <button @click="show = false"
                        class="shrink-0 h-8 w-8 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/80 flex items-center justify-center transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>
            @endif

            {{-- Gallery Feed --}}
            <div class="space-y-6 px-1 md:px-2 pt-6" id="gallery-feed">

                @php $absoluteIndex = 0; @endphp

                <div class="space-y-14">
                    {{-- Infinite Scroll Sentinel (auto-load older images on scroll up) --}}
                    @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasMorePages())
                        <div id="load-more-sentinel" class="flex justify-center py-4" x-data="{
                                    observer: null,
                                    isLoading: false,
                                    ready: false,
                                    init() {
                                        // Wait 2s before activating to avoid triggering on initial page load
                                        setTimeout(() => {
                                            this.ready = true;
                                            this.observer = new IntersectionObserver((entries) => {
                                                entries.forEach(entry => {
                                                    if (entry.isIntersecting && !this.isLoading && this.ready) {
                                                        this.isLoading = true;
                                                        const scrollH = document.documentElement.scrollHeight;
                                                        $wire.loadMore().then(() => {
                                                            this.$nextTick(() => {
                                                                setTimeout(() => {
                                                                    const newScrollH = document.documentElement.scrollHeight;
                                                                    document.documentElement.scrollTop += (newScrollH - scrollH);
                                                                    this.isLoading = false;
                                                                }, 150);
                                                            });
                                                        }).catch(() => { this.isLoading = false; });
                                                    }
                                                });
                                            }, { rootMargin: '100px 0px 0px 0px' });
                                            this.observer.observe(this.$el);
                                        }, 2000);
                                    },
                                    destroy() {
                                        if (this.observer) this.observer.disconnect();
                                    }
                                }">
                            <div class="flex items-center gap-2 text-white/40 text-sm" wire:loading.flex
                                wire:target="loadMore">
                                <i class="fa-solid fa-spinner fa-spin text-purple-400"></i>
                                <span>Đang tải thêm...</span>
                            </div>
                            <div class="text-white/20 text-xs" wire:loading.remove wire:target="loadMore">
                                <i class="fa-solid fa-ellipsis"></i>
                            </div>
                        </div>
                    @endif

                    {{-- Grouped Batches --}}
                    @forelse($groupedHistory as $groupKey => $groupItems)
                        @php
                            $firstItem = $groupItems->first();
                            $modelId = $firstItem->generation_params['model_id'] ?? null;
                            $ratio = $firstItem->generation_params['aspect_ratio'] ?? '1:1';

                            $modelName = $modelId;
                            if ($modelId && isset($availableModels)) {
                                $found = collect($availableModels)->firstWhere('id', $modelId);
                                $modelName = $found['name'] ?? $modelId;
                            }

                            // Calculate CSS aspect-ratio value (width / height)
                            $aspectRatioCss = '1 / 1';
                            if ($ratio !== 'Auto' && strpos($ratio, ':') !== false) {
                                [$w, $h] = explode(':', $ratio);
                                $aspectRatioCss = $w . ' / ' . $h;
                            }
                        @endphp

                        {{-- Batch Group --}}
                        <div class="space-y-2.5" x-data="{ expanded: false }">

                            {{-- Header Row: Mode > Prompt + Actions --}}
                            <div class="flex items-center gap-2">
                                <div class="flex items-center justify-between gap-2 flex-1 text-sm min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-white/70 text-xs font-medium shrink-0">Tạo ảnh</span>
                                        <i class="fa-solid fa-chevron-right text-white/30 text-[9px] shrink-0"></i>
                                        <button
                                            class="min-w-0 flex-1 text-sm text-left text-white/50 first-letter:capitalize hover:text-white/80 transition-colors duration-200 cursor-pointer truncate overflow-hidden"
                                            @click="expanded = !expanded" title="Nhấn để xem toàn bộ prompt">
                                            {{ $firstItem->final_prompt }}
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-0.5 shrink-0">
                                        {{-- Copy Prompt (ghost button) --}}
                                        <button
                                            @click="navigator.clipboard.writeText(@js($firstItem->final_prompt)); notify('Đã copy prompt')"
                                            class="inline-flex items-center justify-center h-7 px-2 rounded-lg bg-transparent text-white/50 hover:bg-white/[0.05] hover:text-white/90 text-xs transition-all duration-200 active:scale-[0.98]"
                                            title="Copy prompt">
                                            <i class="fa-regular fa-copy text-[11px] mr-1"></i>
                                            <span class="hidden sm:inline">Copy</span>
                                        </button>
                                        {{-- Reuse (ghost button) --}}
                                        <button wire:click="reusePrompt({{ $firstItem->id }})"
                                            class="inline-flex items-center justify-center h-7 px-2 rounded-lg bg-transparent text-white/50 hover:bg-white/[0.05] hover:text-white/90 text-xs transition-all duration-200 active:scale-[0.98]"
                                            title="Dùng lại prompt + cài đặt">
                                            <i class="fa-solid fa-arrow-rotate-left text-[10px] mr-1"></i>
                                            <span class="hidden sm:inline">Reuse</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Expanded Prompt Detail (Glass panel) --}}
                            <div x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="px-3 py-2.5 rounded-xl bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] text-sm text-white/70 leading-relaxed">
                                {{ $firstItem->final_prompt }}
                                <div class="flex items-center gap-2 mt-2 text-[11px] text-white/40">
                                    <span class="text-purple-300/70">{{ $modelName }}</span>
                                    <span class="text-white/20">•</span>
                                    <span>{{ $ratio }}</span>
                                    <span class="text-white/20">•</span>
                                    <span>{{ $groupItems->count() }} ảnh</span>
                                    <span class="text-white/20">•</span>
                                    <span>{{ $firstItem->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            {{-- Image Grid --}}
                            <div class="grid grid-cols-2 xl:grid-cols-4 gap-1 rounded-lg overflow-hidden">
                                @foreach($groupItems as $image)
                                    <div class="block group cursor-pointer" @click="openPreview(null, {{ $absoluteIndex }})">
                                        <div class="h-full bg-white/[0.02]">
                                            <div class="relative overflow-hidden" style="aspect-ratio: {{ $aspectRatioCss }};"
                                                x-data="{ loaded: false }">
                                                {{-- Shimmer --}}
                                                <div x-show="!loaded" class="absolute inset-0 bg-white/[0.04]">
                                                    <div
                                                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.06] to-transparent animate-shimmer">
                                                    </div>
                                                </div>
                                                {{-- Image --}}
                                                <img src="{{ $image->image_url }}" alt="Preview"
                                                    class="w-full h-full object-cover transition-all duration-300 ease-out group-hover:scale-[1.05]"
                                                    :class="loaded ? 'opacity-100' : 'opacity-0'" loading="lazy"
                                                    draggable="false" @load="loaded = true">
                                                {{-- Hover Overlay + Actions --}}
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                    <div class="absolute bottom-2 right-2 flex gap-1.5">
                                                        <a href="{{ $image->image_url }}" download @click.stop
                                                            class="h-8 w-8 rounded-lg bg-black/50 backdrop-blur-[8px] hover:bg-white/20 text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1] active:scale-[0.95]"
                                                            title="Tải xuống">
                                                            <i class="fa-solid fa-download text-[11px]"></i>
                                                        </a>
                                                        <button wire:click="deleteImage({{ $image->id }})" @click.stop
                                                            wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                            class="h-8 w-8 rounded-lg bg-black/50 backdrop-blur-[8px] hover:bg-red-500/80 text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1] active:scale-[0.95]"
                                                            title="Xóa">
                                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @php $absoluteIndex++; @endphp
                                @endforeach
                            </div>
                        </div>

                    @empty
                        @if(!$isGenerating)
                            <div class="py-16 sm:py-24 text-center"
                                x-data="{ prompts: ['Một chú mèo dễ thương ngủ trên mây', 'Phong cảnh núi tuyết hoàng hôn', 'Logo công nghệ gradient xanh'] }">
                                <div
                                    class="w-16 h-16 mx-auto rounded-2xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                                    <i class="fa-solid fa-image text-3xl text-white/20"></i>
                                </div>
                                <h3 class="text-white/95 font-medium text-lg mb-2">Chưa có hình ảnh nào</h3>
                                <p class="text-white/50 text-sm max-w-sm mx-auto mb-6">
                                    Hãy thử tạo một hình ảnh mới bằng cách nhập mô tả vào khung chat bên dưới.
                                </p>
                                <div class="flex flex-wrap justify-center gap-2">
                                    <template x-for="p in prompts">
                                        <button @click="$wire.set('prompt', p)"
                                            class="h-9 px-4 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs text-white/70 hover:text-white transition-all active:scale-[0.98]">
                                            <span x-text="p"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    @endforelse
                </div>

            </div>

            {{-- Loading Skeleton (bottom, like chatbot) --}}
            @if($isGenerating && !$generatedImageUrl)
                <div x-data="{ elapsed: 0, timer: null }" x-init="
                                                                startLoading();
                                                                timer = setInterval(() => elapsed++, 1000);
                                                                $nextTick(() => setTimeout(() => document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }), 100));
                                                             "
                    x-effect="if (!@js($isGenerating)) { stopLoading(); clearInterval(timer); }"
                    x-on:remove="clearInterval(timer)">
                    <div
                        class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl overflow-hidden shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                        {{-- Progress bar --}}
                        <div class="h-0.5 bg-white/[0.03] overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-purple-500 animate-pulse"
                                style="width: 100%; animation: progress-slide 2s ease-in-out infinite;"></div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                    <div
                                        class="w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin">
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-white/90 text-sm font-medium"
                                        x-text="loadingMessages[currentLoadingMessage]">Đang tạo ảnh...</p>
                                    <p class="text-white/40 text-xs mt-0.5">
                                        <span
                                            x-text="Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : ''"></span>
                                        <span x-text="(elapsed % 60) + ' giây'"></span>
                                    </p>
                                </div>
                                <button wire:click="cancelGeneration"
                                    class="h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-red-500/20 border border-white/[0.08] text-xs text-white/50 hover:text-red-400 transition-all active:scale-[0.95]">
                                    <i class="fa-solid fa-xmark mr-1"></i>Hủy
                                </button>
                            </div>
                            {{-- Single image placeholder (we generate 1 image per request) --}}
                            <div class="grid grid-cols-2 xl:grid-cols-4 gap-1 rounded-lg overflow-hidden">
                                @for ($i = 0; $i < $batchSize; $i++)
                                    <div class="bg-white/[0.03] flex items-center justify-center"
                                        style="aspect-ratio: {{ $aspectRatio !== 'auto' && strpos($aspectRatio, ':') !== false ? str_replace(':', ' / ', $aspectRatio) : '1 / 1' }};">
                                        <div
                                            class="w-6 h-6 border-2 border-purple-500/40 border-t-transparent rounded-full animate-spin">
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- FIXED INPUT BAR (inside textToImage Alpine scope) --}}
    {{-- ============================================================ --}}
    <div class="fixed bottom-[60px] md:bottom-0 left-0 right-0 md:left-[72px] z-[60]"
        @click.away="showRatioDropdown = false; showModelDropdown = false">


        <div class="max-w-3xl mx-auto px-3 sm:px-4 pb-3 sm:pb-4 pt-3">
            <div class="relative">
                {{-- Glow effect --}}
                <div
                    class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-20 blur-md sm:blur-lg transition-opacity duration-500">
                </div>

                {{-- Input container --}}
                <div
                    class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/50 backdrop-blur-2xl border border-white/15 shadow-2xl">

                    {{-- Textarea --}}
                    <textarea x-ref="promptInput" wire:model.live="prompt" rows="3"
                        placeholder="Mô tả ý tưởng của bạn..."
                        class="w-full h-20 bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/40 text-sm sm:text-base resize-none focus:placeholder-white/60 transition-all overflow-y-auto"
                        @keydown.ctrl.enter.prevent="$wire.generate()" @keydown.meta.enter.prevent="$wire.generate()" {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    {{-- Character counter + keyboard hint --}}
                    <div class="flex items-center justify-between -mt-1 mb-1">
                        <span class="text-[11px] text-white/30"
                            :class="{ 'text-amber-400/70': $wire.prompt?.length > 1800, 'text-red-400/70': $wire.prompt?.length > 2000 }"
                            x-text="($wire.prompt?.length || 0) + ' / 2000'"></span>
                        <span class="text-[11px] text-white/20 hidden sm:inline">
                            <kbd class="px-1 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">Ctrl</kbd>
                            +
                            <kbd class="px-1 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">Enter</kbd>
                            để tạo
                        </span>
                    </div>

                    {{-- Bottom row: icons + button --}}
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex items-center gap-2"
                            @click.away="showRatioDropdown = false; showModelDropdown = false">

                            {{-- Image Reference Picker (same as /home) --}}
                            <div class="relative">
                                {{-- Image Button with Count Badge --}}
                                <button type="button"
                                    @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                                    class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg transition-all cursor-pointer"
                                    :class="selectedImages.length > 0 
                                        ? 'bg-purple-500/30 border border-purple-500/50' 
                                        : 'bg-gradient-to-br from-purple-500/20 to-pink-500/20 hover:from-purple-500/30 hover:to-pink-500/30 border border-purple-500/30'">
                                    {{-- Show thumbnails if images selected --}}
                                    <template x-if="selectedImages.length > 0">
                                        <div class="flex items-center gap-1">
                                            <div class="flex -space-x-1">
                                                <template x-for="(img, idx) in selectedImages.slice(0, 3)"
                                                    :key="img.id">
                                                    <img :src="img.url"
                                                        class="w-5 h-5 rounded border border-purple-500/50 object-cover">
                                                </template>
                                            </div>
                                            <span class="text-purple-300 text-xs font-medium"
                                                x-text="selectedImages.length"></span>
                                        </div>
                                    </template>
                                    <template x-if="selectedImages.length === 0">
                                        <i class="fa-solid fa-image text-purple-400 text-sm"></i>
                                    </template>
                                </button>

                                {{-- Clear all button --}}
                                <button x-show="selectedImages.length > 0" @click.stop="clearAll()"
                                    class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Aspect Ratio Button --}}
                            <div class="relative">
                                <button type="button" data-dropdown-trigger="ratio"
                                    @click="showRatioDropdown = !showRatioDropdown; showModelDropdown = false"
                                    class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showRatioDropdown }">
                                    <i class="fa-solid fa-crop text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                        x-text="selectedRatio === 'auto' ? 'Tỉ lệ' : selectedRatio"></span>
                                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform hidden sm:inline"
                                        :class="{ 'rotate-180': showRatioDropdown }"></i>
                                </button>

                                {{-- Ratio Dropdown - Desktop --}}
                                <template x-teleport="body">
                                    <div x-show="showRatioDropdown" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-2"
                                        class="hidden sm:block fixed w-80 p-3 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[9999]"
                                        x-init="$watch('showRatioDropdown', value => {
                                            if (value) {
                                                const btn = document.querySelector('[data-dropdown-trigger=ratio]');
                                                if (btn) {
                                                    const rect = btn.getBoundingClientRect();
                                                    $el.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                                                    $el.style.left = rect.left + 'px';
                                                }
                                            }
                                        })" @click.stop>
                                        <div class="text-white/50 text-xs font-medium mb-2">Tỉ lệ khung hình</div>
                                        <div class="grid grid-cols-5 gap-1.5">
                                            <template x-for="ratio in ratios" :key="ratio.id">
                                                <button type="button" @click="selectRatio(ratio.id)"
                                                    class="flex flex-col items-center gap-1 p-2 rounded-lg transition-all"
                                                    :class="selectedRatio === ratio.id ? 'bg-purple-500/20 border border-purple-500/50' : 'bg-white/[0.03] hover:bg-white/[0.06] border border-transparent'">
                                                    <div class="w-6 h-6 flex items-center justify-center">
                                                        <template x-if="ratio.icon">
                                                            <i :class="'fa-solid ' + ratio.icon"
                                                                class="text-white/60 text-sm"></i>
                                                        </template>
                                                        <template x-if="!ratio.icon">
                                                            <div class="border border-white/40 rounded-sm" :style="{
                                                                width: ratio.id.split(':')[0] > ratio.id.split(':')[1] ? '20px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '16px' : '12px'),
                                                                height: ratio.id.split(':')[1] > ratio.id.split(':')[0] ? '20px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '16px' : '12px')
                                                            }"></div>
                                                        </template>
                                                    </div>
                                                    <span class="text-white/70 text-[10px] font-medium"
                                                        x-text="ratio.label"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Ratio Bottom Sheet - Mobile --}}
                                <template x-teleport="body">
                                    <div x-show="showRatioDropdown" x-cloak
                                        class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                        @click.self="showRatioDropdown = false" @click.stop>
                                        <div x-show="showRatioDropdown"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="translate-y-full"
                                            x-transition:enter-end="translate-y-0"
                                            class="w-full max-w-lg bg-[#0f0f18]/95 backdrop-blur-[24px] saturate-[180%] border-t border-white/[0.1] rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                            <div
                                                class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                                <span class="text-white font-semibold text-base">Tùy chỉnh khung
                                                    hình</span>
                                                <button type="button" @click="showRatioDropdown = false"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>
                                            <div class="p-4 overflow-y-auto">
                                                <div class="grid grid-cols-4 gap-2 mb-6">
                                                    <template x-for="ratio in ratios" :key="ratio.id">
                                                        <button type="button" @click="selectRatio(ratio.id)"
                                                            class="flex flex-col items-center gap-1.5 p-3 rounded-xl transition-all"
                                                            :class="selectedRatio === ratio.id ? 'bg-purple-500/30 border border-purple-500/50' : 'bg-white/5 active:bg-white/10 border border-transparent'">
                                                            <div class="w-8 h-8 flex items-center justify-center">
                                                                <template x-if="ratio.icon">
                                                                    <i :class="'fa-solid ' + ratio.icon"
                                                                        class="text-white/60 text-lg"></i>
                                                                </template>
                                                                <template x-if="!ratio.icon">
                                                                    <div class="border-2 border-white/40 rounded-sm"
                                                                        :style="{
                                                                            width: ratio.id.split(':')[0] > ratio.id.split(':')[1] ? '28px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '24px' : '16px'),
                                                                            height: ratio.id.split(':')[1] > ratio.id.split(':')[0] ? '28px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '24px' : '16px')
                                                                        }"></div>
                                                                </template>
                                                            </div>
                                                            <span class="text-white/70 text-xs font-medium"
                                                                x-text="ratio.label"></span>
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Model Selector --}}
                            <div class="relative">
                                <button type="button" data-dropdown-trigger="model"
                                    @click="showModelDropdown = !showModelDropdown; showRatioDropdown = false"
                                    class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showModelDropdown }">
                                    <i class="fa-solid fa-microchip text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                        x-text="getSelectedModel().name"></span>
                                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform hidden sm:inline"
                                        :class="{ 'rotate-180': showModelDropdown }"></i>
                                </button>

                                {{-- Model Dropdown --}}
                                <template x-teleport="body">
                                    <div x-show="showModelDropdown" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-2"
                                        class="hidden sm:block fixed w-64 p-2 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[9999]"
                                        x-init="$watch('showModelDropdown', value => {
                                            if (value) {
                                                const btn = document.querySelector('[data-dropdown-trigger=model]');
                                                if (btn) {
                                                    const rect = btn.getBoundingClientRect();
                                                    $el.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                                                    $el.style.left = rect.left + 'px';
                                                }
                                            }
                                        })" @click.stop>
                                        <div class="space-y-1">
                                            <template x-for="model in models" :key="model.id">
                                                <button type="button" @click="selectModel(model.id)"
                                                    class="w-full flex items-center gap-3 p-2 rounded-lg transition-all"
                                                    :class="selectedModel === model.id ? 'bg-purple-500/20' : 'hover:bg-white/[0.06]'">
                                                    <span class="text-lg" x-text="model.icon"></span>
                                                    <div class="text-left">
                                                        <div class="text-white/90 text-sm font-medium"
                                                            x-text="model.name"></div>
                                                        <div class="text-white/40 text-[10px]" x-text="model.desc">
                                                        </div>
                                                    </div>
                                                    <i x-show="selectedModel === model.id"
                                                        class="fa-solid fa-check text-purple-400 text-xs ml-auto"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Model Bottom Sheet - Mobile --}}
                            <template x-teleport="body">
                                <div x-show="showModelDropdown" x-cloak
                                    class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                    @click.self="showModelDropdown = false" @click.stop>
                                    <div x-show="showModelDropdown"
                                        x-transition:enter="transition ease-out duration-300"
                                        x-transition:enter-start="translate-y-full"
                                        x-transition:enter-end="translate-y-0"
                                        class="w-full max-w-lg bg-[#0f0f18]/95 backdrop-blur-[24px] saturate-[180%] border-t border-white/[0.1] rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                        <div
                                            class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                            <span class="text-white font-semibold text-base">Chọn Model AI</span>
                                            <button type="button" @click="showModelDropdown = false"
                                                class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <div class="p-4 overflow-y-auto">
                                            <div class="space-y-1">
                                                <template x-for="model in models" :key="model.id">
                                                    <button type="button" @click="selectModel(model.id)"
                                                        class="w-full flex items-center gap-3 p-3 rounded-xl transition-all text-left"
                                                        :class="selectedModel === model.id ? 'bg-purple-500/30 border border-purple-500/50' : 'bg-white/5 active:bg-white/10 border border-transparent'">
                                                        <span class="text-2xl" x-text="model.icon"></span>
                                                        <div class="flex-1 min-w-0">
                                                            <div class="text-white font-semibold text-base"
                                                                x-text="model.name"></div>
                                                            <div class="text-white/50 text-sm mt-0.5"
                                                                x-text="model.desc"></div>
                                                        </div>
                                                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                                                            :class="selectedModel === model.id ? 'border-purple-500 bg-purple-500' : 'border-white/20'">
                                                            <i x-show="selectedModel === model.id"
                                                                class="fa-solid fa-check text-white text-xs"></i>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Batch Size Selector --}}
                            <div class="relative" x-data="{ showBatchDropdown: false }">
                                <button type="button"
                                    @click="showBatchDropdown = !showBatchDropdown; showRatioDropdown = false; showModelDropdown = false"
                                    class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showBatchDropdown }">
                                    <i class="fa-solid fa-layer-group text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                        x-text="'Số lượng: ' + $wire.batchSize"></span>
                                    <span class="text-white/70 text-xs font-medium sm:hidden"
                                        x-text="$wire.batchSize"></span>
                                </button>

                                <template x-teleport="body">
                                    <div x-show="showBatchDropdown" x-cloak @click.away="showBatchDropdown = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="fixed w-32 p-1.5 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[9999]"
                                        x-init="$watch('showBatchDropdown', value => {
                                            if (value) {
                                                const btn = $root.querySelector('button');
                                                if (btn) {
                                                    const rect = btn.getBoundingClientRect();
                                                    $el.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                                                    $el.style.left = rect.left + 'px';
                                                }
                                            }
                                         })">

                                        <div class="space-y-1">
                                            @foreach([1, 2, 3, 4] as $n)
                                                <button type="button"
                                                    wire:click="$set('batchSize', {{ $n }}); showBatchDropdown = false"
                                                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors"
                                                    :class="$wire.batchSize === {{ $n }} ? 'bg-purple-500/20 text-white' : 'text-white/70 hover:bg-white/5'">
                                                    <span>{{ $n }} ảnh</span>
                                                    <i x-show="$wire.batchSize === {{ $n }}"
                                                        class="fa-solid fa-check text-purple-400 text-xs"></i>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </template>
                            </div>




                        </div>

                        {{-- Generate Button --}}
                        @if($isGenerating)
                            <button type="button" wire:click="cancelGeneration"
                                class="shrink-0 flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl bg-red-500/80 hover:bg-red-500 text-white font-semibold text-sm shadow-lg shadow-red-500/25 active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-stop text-sm"></i>
                                <span>Hủy</span>
                            </button>
                        @else
                            <button type="button" wire:click="generate"
                                class="shrink-0 flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm shadow-lg shadow-purple-500/25 hover:scale-[1.02] hover:shadow-xl hover:shadow-purple-500/40 active:scale-[0.98] transition-all duration-200"
                                wire:loading.attr="disabled" wire:target="generate">
                                <span wire:loading.remove wire:target="generate"><i
                                        class="fa-solid fa-wand-magic-sparkles text-sm"></i></span>
                                <span wire:loading wire:target="generate"><i
                                        class="fa-solid fa-spinner fa-spin text-sm"></i></span>
                                <span>Tạo ảnh</span>
                                <span class="text-white/60 text-xs font-normal">· {{ $creditCost }} credits</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODALS --}}
    {{-- ============================================================ --}}
    @include('livewire.partials.image-picker-modal')
    @include('livewire.partials.image-preview-modal')

    <style>
        [x-cloak] {
            display: none !important;
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .safe-area-top {
            padding-top: env(safe-area-inset-top, 0px);
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite;
        }

        @keyframes progress-slide {

            0%,
            100% {
                opacity: 0.4;
                transform: translateX(-30%);
            }

            50% {
                opacity: 1;
                transform: translateX(0%);
            }
        }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('textToImage', () => ({
                // Toast
                showToast: false,
                toastMessage: '',
                toastType: 'success',
                toastTimer: null,

                // Image picker
                showImagePicker: false,
                selectedImages: [],
                recentImages: [],
                isLoadingPicker: false,
                maxImages: 4,
                activeTab: 'upload',
                isDragging: false,
                urlInput: '',

                // Preview
                showPreview: false,
                previewIndex: 0,
                previewImage: null,
                historyData: @js($historyData),

                // Loading
                loadingMessages: [
                    'Đang tạo ảnh...',
                    'AI đang sáng tạo...',
                    'Đang xử lý prompt...',
                    'Đang render chi tiết...',
                    'Sắp xong rồi...'
                ],
                currentLoadingMessage: 0,
                loadingInterval: null,

                // Input Bar & Settings
                showRatioDropdown: false,
                showModelDropdown: false,
                selectedRatio: @entangle('aspectRatio'),
                selectedModel: @entangle('modelId'),
                customWidth: 1024,
                customHeight: 1024,
                linkDimensions: true,
                ratios: [
                    { id: 'auto', label: 'Auto', icon: 'fa-expand' },
                    { id: '1:1', label: '1:1', icon: null },
                    { id: '16:9', label: '16:9', icon: null },
                    { id: '9:16', label: '9:16', icon: null },
                    { id: '4:3', label: '4:3', icon: null },
                    { id: '3:4', label: '3:4', icon: null },
                    { id: '3:2', label: '3:2', icon: null },
                    { id: '2:3', label: '2:3', icon: null },
                    { id: '21:9', label: '21:9', icon: null }
                ],
                models: @js(collect($availableModels)->values()->map(fn($m) => [
                    'id' => $m['id'],
                    'name' => $m['name'],
                    'desc' => $m['description'] ?? '',
                    'icon' => match (true) {
                        str_contains($m['id'], 'ultra') => '⚡',
                        str_contains($m['id'], 'pro') => '💎',
                        str_contains($m['id'], 'schnell') => '🚀',
                        default => '🛠️'
                    },
                ])),

                init() {
                    // Auto-scroll to bottom on mount (Removed as per user request)
                    // this.$nextTick(() => {
                    //     setTimeout(() => this.scrollToBottom(false), 200);
                    // });

                    // Scroll to bottom when new image generated
                    this.$wire.$on('imageGenerated', () => {
                        this.$nextTick(() => {
                            setTimeout(() => this.scrollToBottom(true), 300);
                        });
                    });

                    // Update historyData after Livewire re-renders
                    Livewire.hook('morph.updated', ({ el }) => {
                        // historyData will be re-injected on re-render
                    });
                },

                // ============================================================
                // Input Settings Methods
                // ============================================================
                selectRatio(id) {
                    this.selectedRatio = id;
                    if (id !== 'auto') {
                        const [w, h] = id.split(':').map(Number);
                        const baseSize = 1024;
                        this.customWidth = Math.round(baseSize * Math.sqrt(w / h) / 64) * 64;
                        this.customHeight = Math.round(baseSize * Math.sqrt(h / w) / 64) * 64;
                    }
                    if (window.innerWidth >= 640) {
                        this.showRatioDropdown = false;
                    }
                },
                selectModel(id) {
                    this.selectedModel = id;
                    this.showModelDropdown = false;
                },
                getSelectedModel() {
                    return this.models.find(m => m.id === this.selectedModel) || this.models[0];
                },
                updateWidth(newWidth) {
                    this.customWidth = newWidth;
                    if (this.linkDimensions && this.selectedRatio !== 'auto') {
                        const [w, h] = this.selectedRatio.split(':').map(Number);
                        this.customHeight = Math.round(newWidth * h / w / 64) * 64;
                    }
                },
                updateHeight(newHeight) {
                    this.customHeight = newHeight;
                    if (this.linkDimensions && this.selectedRatio !== 'auto') {
                        const [w, h] = this.selectedRatio.split(':').map(Number);
                        this.customWidth = Math.round(newHeight * w / h / 64) * 64;
                    }
                },

                // ============================================================
                // Chat-like scroll
                // ============================================================
                scrollToBottom(smooth = true) {
                    const el = document.documentElement;
                    el.scrollTo({
                        top: el.scrollHeight,
                        behavior: smooth ? 'smooth' : 'instant'
                    });
                },

                // ============================================================
                // Toast notifications
                // ============================================================
                notify(msg, type = 'success') {
                    this.toastMessage = msg;
                    this.toastType = type;
                    this.showToast = true;
                    clearTimeout(this.toastTimer);
                    this.toastTimer = setTimeout(() => this.showToast = false, 3000);
                },

                // ============================================================
                // Loading animation
                // ============================================================
                startLoading() {
                    this.currentLoadingMessage = 0;
                    this.loadingInterval = setInterval(() => {
                        this.currentLoadingMessage = (this.currentLoadingMessage + 1) % this.loadingMessages.length;
                    }, 3000);
                },
                stopLoading() {
                    clearInterval(this.loadingInterval);
                },

                // ============================================================
                // Preview modal
                // ============================================================
                openPreview(url, index) {
                    if (index !== null && index !== undefined && this.historyData[index]) {
                        this.previewIndex = index;
                        this.previewImage = this.historyData[index];
                    } else if (url) {
                        this.previewImage = { url: url, prompt: '' };
                        this.previewIndex = 0;
                    }
                    this.showPreview = true;
                    document.body.style.overflow = 'hidden';
                },
                closePreview() {
                    this.showPreview = false;
                    document.body.style.overflow = '';
                },
                nextImage() {
                    if (this.previewIndex < this.historyData.length - 1) {
                        this.previewIndex++;
                        this.previewImage = this.historyData[this.previewIndex];
                    }
                },
                prevImage() {
                    if (this.previewIndex > 0) {
                        this.previewIndex--;
                        this.previewImage = this.historyData[this.previewIndex];
                    }
                },

                // ============================================================
                // Keyboard
                // ============================================================
                handleKeydown(e) {
                    if (this.showPreview) {
                        if (e.key === 'ArrowLeft') this.prevImage();
                        if (e.key === 'ArrowRight') this.nextImage();
                        if (e.key === 'Escape') this.closePreview();
                    }
                },

                // ============================================================
                // Image picker
                // ============================================================
                async loadRecentImages() {
                    if (this.recentImages.length > 0) return;
                    this.isLoadingPicker = true;
                    try {
                        const res = await fetch('/api/user/recent-images');
                        if (res.ok) this.recentImages = await res.json();
                    } catch (e) { console.error(e); }
                    this.isLoadingPicker = false;
                },

                handleFileSelect(e) {
                    const files = Array.from(e.target.files);
                    this.processFiles(files);
                    e.target.value = '';
                },

                handleDrop(e) {
                    this.isDragging = false;
                    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                    this.processFiles(files);
                },

                // Direct upload from prompt bar
                handleDirectUpload(e) {
                    const files = Array.from(e.target.files);
                    this.processFiles(files);
                    e.target.value = '';
                    this.notify(files.length + ' ảnh đã thêm làm tham chiếu');
                },

                processFiles(files) {
                    const remaining = this.maxImages - this.selectedImages.length;
                    const toProcess = files.slice(0, remaining);
                    toProcess.forEach(file => {
                        if (file.size > 10 * 1024 * 1024) {
                            this.notify('Ảnh quá lớn (tối đa 10MB)', 'error');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.selectedImages.push({
                                id: Date.now() + Math.random(),
                                url: ev.target.result,
                                file: file
                            });
                            this.$wire.setReferenceImages(
                                this.selectedImages.map(img => ({ url: img.url }))
                            );
                        };
                        reader.readAsDataURL(file);
                    });
                },

                addFromUrl() {
                    const url = this.urlInput.trim();
                    if (!url) return;
                    if (this.selectedImages.length >= this.maxImages) {
                        this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                        return;
                    }
                    this.selectedImages.push({ id: Date.now(), url: url });
                    this.$wire.setReferenceImages(
                        this.selectedImages.map(img => ({ url: img.url }))
                    );
                    this.urlInput = '';
                },

                selectFromRecent(url) {
                    const idx = this.selectedImages.findIndex(i => i.url === url);
                    if (idx > -1) {
                        this.selectedImages.splice(idx, 1);
                    } else {
                        if (this.selectedImages.length >= this.maxImages) {
                            this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                            return;
                        }
                        this.selectedImages.push({ id: Date.now(), url: url });
                    }
                    this.$wire.setReferenceImages(
                        this.selectedImages.map(img => ({ url: img.url }))
                    );
                },

                isSelected(url) {
                    return this.selectedImages.some(i => i.url === url);
                },

                removeImage(id) {
                    this.selectedImages = this.selectedImages.filter(i => i.id !== id);
                    this.$wire.setReferenceImages(
                        this.selectedImages.map(img => ({ url: img.url }))
                    );
                },

                clearAll() {
                    this.selectedImages = [];
                    this.$wire.setReferenceImages([]);
                },

                confirmSelection() {
                    this.$wire.setReferenceImages(
                        this.selectedImages.map(img => ({ url: img.url }))
                    );
                    this.showImagePicker = false;
                    if (this.selectedImages.length > 0) {
                        this.notify(this.selectedImages.length + ' ảnh tham chiếu đã chọn');
                    }
                },
            }));
        });
    </script>
</div>