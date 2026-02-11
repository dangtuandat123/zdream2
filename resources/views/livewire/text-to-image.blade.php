<div class="relative min-h-screen pb-48 md:pb-32" @if($isGenerating) wire:poll.2s="pollImageStatus" @endif x-data="{
    selectedRatio: @entangle('aspectRatio'),
    selectedModel: @entangle('modelId'),
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    // History data sync
    historyData: @js($flatHistoryForJs ?? []),

    // Image picker
    showImagePicker: false,
    selectedImages: [],
    maxImages: 4,
    recentImages: [],
    isLoadingPicker: false,
    urlInput: '',
    activeTab: 'upload',
    isDragging: false,

    // Preview
    showPreview: false,
    previewImage: null,
    previewIndex: 0,

    // Toast
    toastMessage: '',
    toastType: 'success',
    showToast: false,

    // Loading
    loadingMessages: ['Đang sáng tạo...', 'Chút nữa thôi...', 'Sắp xong rồi...', 'AI đang vẽ...'],
    currentLoadingMessage: 0,
    loadingInterval: null,

    // Touch
    touchStartX: 0,
    touchStartY: 0,

    // Methods
    notify(msg, type = 'success') {
        this.toastMessage = msg; this.toastType = type; this.showToast = true;
        setTimeout(() => this.showToast = false, 2500);
    },
    getModelName() {
        const m = Object.values(this.models).find(m => m.id === '{{ $modelId }}');
        return m ? m.name : 'Model';
    },
    startLoading() {
        this.currentLoadingMessage = 0;
        this.loadingInterval = setInterval(() => {
            this.currentLoadingMessage = (this.currentLoadingMessage + 1) % this.loadingMessages.length;
        }, 2000);
    },
    stopLoading() {
        if (this.loadingInterval) { clearInterval(this.loadingInterval); this.loadingInterval = null; }
    },

    // Preview
    openPreview(image, index) {
        this.previewIndex = index;
        this.previewImage = this.historyData[index];
        this.showPreview = true;
        document.body.style.overflow = 'hidden';
    },
    closePreview() {
        this.showPreview = false; document.body.style.overflow = '';
        setTimeout(() => this.previewImage = null, 300);
    },
    nextImage() {
        if (this.previewIndex < this.historyData.length - 1) {
            this.previewIndex++; this.previewImage = this.historyData[this.previewIndex];
        }
    },
    prevImage() {
        if (this.previewIndex > 0) {
            this.previewIndex--; this.previewImage = this.historyData[this.previewIndex];
        }
    },
    goToImage(index) {
        this.previewIndex = index; this.previewImage = this.historyData[index];
    },
    shareImage() {
        if (navigator.share && this.previewImage) {
            navigator.share({ title: 'AI Generated Image', text: this.previewImage.prompt, url: this.previewImage.url });
        } else {
            this.notify('Trình duyệt không hỗ trợ chia sẻ', 'error');
        }
    },
    useAsReference() {
        if(this.previewImage) {
            this.selectedImages = [{ id: this.previewImage.id, url: this.previewImage.url }];
            this.closePreview();
            this.showImagePicker = false;
        }
    },
    copyPrompt(text = null) {
        const prompt = text || (this.previewImage ? this.previewImage.prompt : '');
        if(prompt) {
            navigator.clipboard.writeText(prompt);
            this.notify('Đã copy prompt');
        }
    },

    // Handlers
    handleKeydown(e) {
        if (!this.showPreview) return;
        if (e.key === 'ArrowLeft') this.prevImage();
        else if (e.key === 'ArrowRight') this.nextImage();
        else if (e.key === 'Escape') this.closePreview();
    },
    _initialLoad: true,
    init() {
        if (this._initialLoad) {
            this._initialLoad = false;
            this.$nextTick(() => {
                setTimeout(() => {
                    document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'instant' });
                }, 100);
            });
        }
    }
}" @keydown.window="handleKeydown($event)" @if($isGenerating) wire:poll.3s="pollImageStatus" @endif>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl flex items-center gap-2"
        :class="{ 'bg-green-500/95': toastType==='success', 'bg-red-500/95': toastType==='error', 'bg-yellow-500/95 text-black': toastType==='warning' }">
        <i :class="{ 'fa-solid fa-check-circle': toastType==='success', 'fa-solid fa-exclamation-circle': toastType==='error', 'fa-solid fa-triangle-exclamation': toastType==='warning' }"></i>
        <span x-text="toastMessage"></span>
    </div>

    @php
        // 1. Grouping Logic
        $historyCollection = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? $history->getCollection()
            : collect($history);
        $groupedHistory = $historyCollection->groupBy(function($item) {
            return $item->final_prompt . '|' . 
                   ($item->generation_params['model_id'] ?? '') . '|' . 
                   ($item->generation_params['aspect_ratio'] ?? '');
        });

        // 2. Flatten for JS
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
    <div class="fixed top-14 md:top-0 left-0 right-0 md:left-[72px] z-[55]"
        x-data="{ openFilter: null }">
        <div class="bg-[#0a0a0f]/80 backdrop-blur-[20px] saturate-[180%] border-b border-white/[0.08]">
            <div class="max-w-5xl mx-auto px-4 py-2.5">
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Date Filter --}}
                    <div class="relative">
                        <button @click="openFilter = openFilter === 'date' ? null : 'date'"
                            class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
                                {{ $filterDate !== 'all'
                                    ? 'bg-purple-500/20 border border-purple-500/40 text-purple-300'
                                    : 'bg-white/[0.05] border border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:text-white/90' }}">
                            <i class="fa-regular fa-calendar text-xs"></i>
                            <span>{{ $filterDate === 'all' ? 'Theo ngày' : ['week'=>'Tuần qua','month'=>'Tháng qua','3months'=>'3 tháng'][$filterDate] ?? 'Theo ngày' }}</span>
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
                        <button @click="openFilter = openFilter === 'model' ? null : 'model'"
                            class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
                                {{ $filterModel !== 'all'
                                    ? 'bg-purple-500/20 border border-purple-500/40 text-purple-300'
                                    : 'bg-white/[0.05] border border-white/[0.08] text-white/70 hover:bg-white/[0.08] hover:text-white/90' }}">
                            <i class="fa-solid fa-microchip text-xs"></i>
                            <span class="hidden sm:inline">{{ $filterModel === 'all' ? 'Theo model' : (collect($availableModels)->firstWhere('id', $filterModel)['name'] ?? $filterModel) }}</span>
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
                        <button @click="openFilter = openFilter === 'ratio' ? null : 'ratio'"
                            class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg text-sm font-medium transition-all duration-200 active:scale-[0.98]
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
            <div class="space-y-5" id="gallery-feed">

                @php $absoluteIndex = 0; @endphp

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

                        $ratioValue = '1/1';
                        if ($ratio !== 'Auto' && strpos($ratio, ':') !== false) {
                            $ratioValue = str_replace(':', '/', $ratio);
                        }
                    @endphp

                    {{-- Batch Glass Card --}}
                    <div class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl overflow-hidden hover:border-white/[0.12] transition-all duration-200 group/batch shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]"
                        x-data="{ expanded: false }">

                        {{-- Image Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-white/[0.04]">
                            @foreach($groupItems as $image)
                                <div class="relative group overflow-hidden bg-[#0f0f18] cursor-zoom-in"
                                     style="aspect-ratio: {{ $ratioValue }};"
                                     x-data="{ loaded: false }"
                                     @click="openPreview(null, {{ $absoluteIndex }})">
                                    {{-- Shimmer Skeleton --}}
                                    <div x-show="!loaded" class="absolute inset-0 bg-white/[0.04]">
                                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.06] to-transparent animate-shimmer"></div>
                                    </div>
                                    {{-- Image --}}
                                    <img src="{{ $image->image_url }}" alt="Generated"
                                         class="w-full h-full object-cover transition-all duration-500 group-hover:scale-105"
                                         :class="loaded ? 'opacity-100' : 'opacity-0'"
                                         loading="lazy"
                                         @load="loaded = true">
                                    {{-- Hover Overlay --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <div class="absolute bottom-2 right-2 flex gap-1.5">
                                            <a href="{{ $image->image_url }}" download @click.stop
                                                class="h-8 w-8 rounded-lg bg-black/50 hover:bg-white/20 backdrop-blur-sm text-white flex items-center justify-center transition-all border border-white/[0.1] active:scale-[0.95]"
                                                title="Tải xuống">
                                                <i class="fa-solid fa-download text-[11px]"></i>
                                            </a>
                                            <button wire:click="deleteImage({{ $image->id }})" @click.stop
                                                wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                class="h-8 w-8 rounded-lg bg-black/50 hover:bg-red-500/80 backdrop-blur-sm text-white flex items-center justify-center transition-all border border-white/[0.1] active:scale-[0.95]"
                                                title="Xóa">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @php $absoluteIndex++; @endphp
                            @endforeach
                        </div>

                        {{-- Info + Actions Footer --}}
                        <div class="px-3.5 py-2.5">
                            {{-- Prompt --}}
                            <p class="text-white/90 text-[13px] leading-relaxed cursor-pointer select-text"
                                :class="expanded ? '' : 'line-clamp-1'"
                                @click="expanded = !expanded"
                                title="Nhấn để mở rộng">
                                {{ $firstItem->final_prompt }}
                            </p>
                            
                            {{-- Meta + Actions Row --}}
                            <div class="flex items-center justify-between gap-3 mt-1.5">
                                {{-- Metadata --}}
                                <div class="flex items-center gap-1.5 text-[11px] text-white/40 min-w-0 overflow-hidden">
                                    <span class="text-purple-300/70 shrink-0">{{ $modelName }}</span>
                                    <span class="shrink-0">•</span>
                                    <span class="shrink-0">{{ $ratio }}</span>
                                    <span class="shrink-0">•</span>
                                    <span class="shrink-0">{{ $groupItems->count() }} ảnh</span>
                                    <span class="shrink-0 hidden sm:inline">•</span>
                                    <span class="shrink-0 hidden sm:inline">{{ $firstItem->created_at->diffForHumans() }}</span>
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex items-center gap-1 shrink-0">
                                    <button @click="navigator.clipboard.writeText(@js($firstItem->final_prompt)); notify('Đã copy prompt')"
                                        class="h-7 w-7 rounded-md bg-white/[0.04] hover:bg-white/[0.08] text-white/50 hover:text-white/90 flex items-center justify-center transition-all active:scale-[0.95]"
                                        title="Copy prompt">
                                        <i class="fa-regular fa-copy text-[11px]"></i>
                                    </button>
                                    <button wire:click="reusePrompt({{ $firstItem->id }})"
                                        class="h-7 px-2 rounded-md bg-white/[0.04] hover:bg-purple-500/20 text-white/50 hover:text-purple-300 flex items-center gap-1 transition-all active:scale-[0.95] text-[11px]"
                                        title="Dùng lại prompt + cài đặt">
                                        <i class="fa-solid fa-arrow-rotate-left text-[10px]"></i>
                                        <span class="hidden sm:inline">Reuse</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                @empty
                    @if(!$isGenerating)
                        <div class="py-16 sm:py-24 text-center"
                            x-data="{ prompts: ['Một chú mèo dễ thương ngủ trên mây', 'Phong cảnh núi tuyết hoàng hôn', 'Logo công nghệ gradient xanh'] }">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
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

                {{-- Loading Skeleton (bottom, like chatbot) --}}
                @if($isGenerating && !$generatedImageUrl)
                    <div x-data="{ elapsed: 0, timer: null }"
                         x-init="
                            startLoading();
                            timer = setInterval(() => elapsed++, 1000);
                            $nextTick(() => setTimeout(() => document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }), 100));
                         "
                         x-effect="if (!@js($isGenerating)) { stopLoading(); clearInterval(timer); }"
                         x-on:remove="clearInterval(timer)">
                        <div class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl overflow-hidden shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                            {{-- Progress bar --}}
                            <div class="h-0.5 bg-white/[0.03] overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-purple-500 animate-pulse" style="width: 100%; animation: progress-slide 2s ease-in-out infinite;"></div>
                            </div>
                            <div class="p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                        <div class="w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-white/90 text-sm font-medium" x-text="loadingMessages[currentLoadingMessage]">Đang tạo ảnh...</p>
                                        <p class="text-white/40 text-xs mt-0.5">
                                            <span x-text="Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : ''"></span>
                                            <span x-text="(elapsed % 60) + ' giây'"></span>
                                        </p>
                                    </div>
                                    <button wire:click="cancelGeneration" class="h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-red-500/20 border border-white/[0.08] text-xs text-white/50 hover:text-red-400 transition-all active:scale-[0.95]">
                                        <i class="fa-solid fa-xmark mr-1"></i>Hủy
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-px bg-white/[0.04] rounded-lg overflow-hidden">
                                    <div class="aspect-square bg-white/[0.03] flex items-center justify-center">
                                        <div class="w-6 h-6 border-2 border-purple-500/40 border-t-transparent rounded-full animate-spin"></div>
                                    </div>
                                    <div class="aspect-square bg-white/[0.02] hidden sm:block"></div>
                                    <div class="aspect-square bg-white/[0.02] hidden sm:block"></div>
                                    <div class="aspect-square bg-white/[0.02] hidden sm:block"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    <div class="fixed bottom-[60px] md:bottom-0 left-0 right-0 md:left-[72px] z-[60]"
        x-data="{
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
            models: [
                { id: 'flux-pro-1.1-ultra', name: 'FLUX 1.1 Ultra', desc: 'Siêu nhanh & Chân thực', icon: '⚡' },
                { id: 'flux-pro-1.1', name: 'FLUX 1.1 Pro', desc: 'Chất lượng cao', icon: '💎' },
                { id: 'flux-dev', name: 'FLUX Dev', desc: 'Dành cho Developer', icon: '🛠️' },
                { id: 'flux-schnell', name: 'FLUX Schnell', desc: 'Tốc độ cao', icon: '🚀' }
            ],
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
            }
        }">

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
                        @keydown.ctrl.enter.prevent="$wire.generate()"
                        @keydown.meta.enter.prevent="$wire.generate()"
                        {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    {{-- Bottom row: icons + button --}}
                    <div class="flex items-center justify-between gap-2 sm:gap-3">
                        <div class="flex items-center gap-2"
                            @click.away="showRatioDropdown = false; showModelDropdown = false">

                            {{-- Image Picker Trigger --}}
                            <div class="relative">
                                <button type="button"
                                    @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                                    class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg transition-all cursor-pointer"
                                    :class="selectedImages.length > 0
                                        ? 'bg-purple-500/30 border border-purple-500/50'
                                        : 'bg-gradient-to-br from-purple-500/20 to-pink-500/20 hover:from-purple-500/30 hover:to-pink-500/30 border border-purple-500/30'">
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
                                <button x-show="selectedImages.length > 0" @click.stop="selectedImages = []"
                                    class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Aspect Ratio Button --}}
                            <div class="relative">
                                <button type="button" @click="showRatioDropdown = !showRatioDropdown; showModelDropdown = false"
                                    class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
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
                                        class="hidden sm:block fixed w-80 p-3 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                        x-init="$watch('showRatioDropdown', value => {
                                            if (value) {
                                                const btn = $root.querySelector('button');
                                                const rect = btn.getBoundingClientRect();
                                                $el.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                                                $el.style.left = rect.left + 'px';
                                            }
                                        })" @click.stop>
                                        <div class="text-white/50 text-xs font-medium mb-2">Tỉ lệ khung hình</div>
                                        <div class="grid grid-cols-5 gap-1.5">
                                            <template x-for="ratio in ratios" :key="ratio.id">
                                                <button type="button" @click="selectRatio(ratio.id)"
                                                    class="flex flex-col items-center gap-1 p-2 rounded-lg transition-all"
                                                    :class="selectedRatio === ratio.id ? 'bg-purple-500/30 border border-purple-500/50' : 'bg-white/5 hover:bg-white/10 border border-transparent'">
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
                                            class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                            <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                                <span class="text-white font-semibold text-base">Tùy chỉnh khung hình</span>
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
                                                                    <i :class="'fa-solid ' + ratio.icon" class="text-white/60 text-lg"></i>
                                                                </template>
                                                                <template x-if="!ratio.icon">
                                                                    <div class="border-2 border-white/40 rounded-sm"
                                                                        :style="{
                                                                            width: ratio.id.split(':')[0] > ratio.id.split(':')[1] ? '28px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '24px' : '16px'),
                                                                            height: ratio.id.split(':')[1] > ratio.id.split(':')[0] ? '28px' : (ratio.id.split(':')[0] == ratio.id.split(':')[1] ? '24px' : '16px')
                                                                        }"></div>
                                                                </template>
                                                            </div>
                                                            <span class="text-white/70 text-xs font-medium" x-text="ratio.label"></span>
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
                                <button type="button" @click="showModelDropdown = !showModelDropdown; showRatioDropdown = false"
                                    class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showModelDropdown }">
                                    <i class="fa-solid fa-microchip text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                        x-text="getSelectedModel().name"></span>
                                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform hidden sm:inline"
                                        :class="{ 'rotate-180': showModelDropdown }"></i>
                                </button>

                                {{-- Model Dropdown - Desktop --}}
                                <template x-teleport="body">
                                    <div x-show="showModelDropdown" x-cloak
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-2"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-2"
                                        class="hidden sm:block fixed w-64 p-2 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                        x-init="$watch('showModelDropdown', value => {
                                            if (value) {
                                                const btn = $root.querySelector('button');
                                                const rect = btn.getBoundingClientRect();
                                                $el.style.bottom = (window.innerHeight - rect.top + 8) + 'px';
                                                $el.style.left = rect.left + 'px';
                                            }
                                        })" @click.stop>
                                        <div class="text-white/50 text-xs font-medium mb-2 px-2">Chọn Model AI</div>
                                        <template x-for="model in models" :key="model.id">
                                            <button type="button"
                                                @click="selectModel(model.id)"
                                                class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all text-left"
                                                :class="selectedModel === model.id ? 'bg-purple-500/30 border border-purple-500/50' : 'hover:bg-white/5 border border-transparent'">
                                                <span class="text-lg" x-text="model.icon"></span>
                                                <div class="flex-1 min-w-0">
                                                    <div class="text-white text-sm font-medium" x-text="model.name"></div>
                                                    <div class="text-white/40 text-xs" x-text="model.desc"></div>
                                                </div>
                                                <i x-show="selectedModel === model.id"
                                                    class="fa-solid fa-check text-purple-400 text-sm"></i>
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                {{-- Model Bottom Sheet - Mobile --}}
                                <template x-teleport="body">
                                    <div x-show="showModelDropdown" x-cloak
                                        class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                        @click.self="showModelDropdown = false" @click.stop>
                                        <div x-show="showModelDropdown"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="translate-y-full"
                                            x-transition:enter-end="translate-y-0"
                                            class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                            <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
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
                                                                <div class="text-white font-semibold text-base" x-text="model.name"></div>
                                                                <div class="text-white/50 text-sm mt-0.5" x-text="model.desc"></div>
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
                            </div>
                        </div>

                        {{-- Generate Button --}}
                        @if($isGenerating)
                            <button type="button" wire:click="cancelGeneration"
                                class="shrink-0 flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl bg-red-500/80 hover:bg-red-500 text-white font-semibold text-sm active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-stop text-sm"></i>
                                <span>Hủy</span>
                            </button>
                        @else
                            <button type="button" wire:click="generate"
                                class="shrink-0 flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/30 active:scale-[0.98] transition-all duration-200"
                                wire:loading.attr="disabled" wire:target="generate">
                                <span wire:loading.remove wire:target="generate"><i class="fa-solid fa-wand-magic-sparkles text-sm"></i></span>
                                <span wire:loading wire:target="generate"><i class="fa-solid fa-spinner fa-spin text-sm"></i></span>
                                <span>Tạo ảnh</span>
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
            0%, 100% { opacity: 0.4; transform: translateX(-30%); }
            50% { opacity: 1; transform: translateX(0%); }
        }
    </style>
</div>