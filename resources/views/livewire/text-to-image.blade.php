<div class="relative min-h-screen pb-48 md:pb-32" x-data="{
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    historyData: @js($historyData),
    showRatioMenu: false,
    showModelMenu: false,

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
        this.previewImage = image; this.previewIndex = index; this.showPreview = true;
        document.body.style.overflow = 'hidden';
    },
    closePreview() {
        this.showPreview = false; this.previewImage = null; document.body.style.overflow = '';
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
    goToImage(i) {
        if (i >= 0 && i < this.historyData.length) {
            this.previewIndex = i; this.previewImage = this.historyData[i];
        }
    },
    handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; this.touchStartY = e.touches[0].clientY; },
    handleTouchEnd(e) {
        const dx = e.changedTouches[0].clientX - this.touchStartX;
        const dy = e.changedTouches[0].clientY - this.touchStartY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) { dx > 0 ? this.prevImage() : this.nextImage(); }
        else if (dy > 100) { this.closePreview(); }
    },
    useAsReference() {
        if (this.previewImage && this.selectedImages.length < this.maxImages) {
            if (!this.selectedImages.find(img => img.url === this.previewImage.url)) {
                this.selectedImages.push({ type: 'url', url: this.previewImage.url, id: Date.now() });
            }
            this.notify('Đã thêm vào ảnh mẫu'); this.closePreview();
        }
    },
    copyPrompt() {
        if (this.previewImage?.prompt) {
            $wire.set('prompt', this.previewImage.prompt);
            this.notify('Đã copy prompt'); this.closePreview();
        }
    },
    async shareImage() {
        if (navigator.share && this.previewImage) {
            try { await navigator.share({ title: 'ZDream AI', url: this.previewImage.url }); } catch(e) {}
        } else if (this.previewImage) {
            await navigator.clipboard.writeText(this.previewImage.url);
            this.notify('Đã copy link ảnh');
        }
    },

    // Image picker
    async loadRecentImages() {
        if (this.recentImages.length > 0) return;
        this.isLoadingPicker = true;
        try {
            const res = await fetch('/api/user/recent-images');
            if (res.ok) { const d = await res.json(); this.recentImages = d.images || []; }
        } catch(e) {}
        this.isLoadingPicker = false;
    },
    removeImage(id) { this.selectedImages = this.selectedImages.filter(i => i.id !== id); },
    handleFileSelect(e) { Array.from(e.target.files).forEach(f => this.processFile(f)); e.target.value = ''; },
    handleDrop(e) { this.isDragging = false; Array.from(e.dataTransfer.files).forEach(f => this.processFile(f)); },
    processFile(file) {
        if (this.selectedImages.length >= this.maxImages) { this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning'); return; }
        if (!file.type.startsWith('image/')) { this.notify('Chỉ chấp nhận ảnh', 'error'); return; }
        if (file.size > 10*1024*1024) { this.notify('Ảnh quá lớn (max 10MB)', 'error'); return; }
        this.selectedImages.push({ type: 'file', file, url: URL.createObjectURL(file), id: Date.now()+Math.random() });
    },
    addFromUrl() {
        if (!this.urlInput.trim()) return;
        if (this.selectedImages.length >= this.maxImages) { this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning'); return; }
        if (!this.urlInput.match(/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i)) { this.notify('URL không hợp lệ', 'error'); return; }
        this.selectedImages.push({ type: 'url', url: this.urlInput.trim(), id: Date.now() });
        this.urlInput = ''; this.notify('Đã thêm ảnh');
    },
    selectFromRecent(url) {
        if (this.selectedImages.find(i => i.url === url)) { this.selectedImages = this.selectedImages.filter(i => i.url !== url); return; }
        if (this.selectedImages.length >= this.maxImages) { this.notify('Đã chọn tối đa', 'warning'); return; }
        this.selectedImages.push({ type: 'url', url, id: Date.now() });
    },
    isSelected(url) { return !!this.selectedImages.find(i => i.url === url); },
    clearAll() { this.selectedImages = []; },
    confirmSelection() {
        $wire.setReferenceImages(this.selectedImages.map(i => i.url));
        this.showImagePicker = false;
        this.notify('Đã chọn ' + this.selectedImages.length + ' ảnh mẫu');
    },
    handleKeydown(e) {
        if (!this.showPreview) return;
        if (e.key === 'ArrowLeft') this.prevImage();
        else if (e.key === 'ArrowRight') this.nextImage();
        else if (e.key === 'Escape') this.closePreview();
    },
    init() {
        const refresh = async () => { const d = await $wire.getHistoryData(); if (d) this.historyData = d; };
        $wire.on('historyUpdated', refresh);
        $wire.on('imageGenerated', refresh);
        Livewire.hook('morph.updated', refresh);
    }
}" @keydown.window="handleKeydown($event)" @if($isGenerating) wire:poll.3s="pollImageStatus" @endif>

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

    {{-- ============================================================ --}}
    {{-- SECTION 1: SCROLLABLE GALLERY AREA --}}
    {{-- ============================================================ --}}
    <div id="gallery-scroll">
        <div class="max-w-5xl mx-auto px-4 pt-4 sm:pt-6 pb-6">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5 sm:mb-6">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shrink-0 shadow-lg shadow-purple-500/20">
                        <i class="fa-solid fa-wand-magic-sparkles text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-white leading-tight">Tạo ảnh AI</h1>
                        <p class="text-white/30 text-[11px] sm:text-xs">Text to Image</p>
                    </div>
                </div>
                @auth
                    <div
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/[0.06] border border-white/[0.08]">
                        <i class="fa-solid fa-coins text-yellow-400 text-xs"></i>
                        <span
                            class="text-white font-bold text-sm">{{ number_format(auth()->user()->credits ?? 0, 0, ',', '.') }}</span>
                        <span class="text-white/30 text-[10px]">cr</span>
                    </div>
                @endauth
            </div>

            {{-- Error --}}
            @if($errorMessage)
                <div x-data="{ show: true }" x-show="show" x-cloak
                    class="mb-4 p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3"
                    role="alert">
                    <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                    <span class="flex-1">{{ $errorMessage }}</span>
                    @if($lastPrompt)
                        <button wire:click="retry"
                            class="shrink-0 px-3 py-1 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-medium transition-colors">
                            <i class="fa-solid fa-redo mr-1"></i>Thử lại
                        </button>
                    @endif
                    <button @click="show = false"
                        class="shrink-0 w-7 h-7 rounded-lg hover:bg-white/10 flex items-center justify-center"><i
                            class="fa-solid fa-xmark text-xs"></i></button>
                </div>
            @endif

            {{-- Gallery --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3" id="gallery-grid">

                {{-- Loading Skeleton --}}
                @if($isGenerating && !$generatedImageUrl)
                    <div x-init="startLoading(); $nextTick(() => document.getElementById('gallery-scroll')?.scrollTo({top:0,behavior:'smooth'}))"
                        x-effect="if (!@js($isGenerating)) stopLoading()"
                        class="col-span-2 sm:col-span-1 aspect-[4/5] rounded-xl bg-[#16171c] border border-purple-500/30 overflow-hidden relative">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-purple-500/10 to-transparent animate-shimmer">
                        </div>
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-600/15 via-transparent to-pink-600/15 animate-pulse">
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3">
                            <div class="relative">
                                <div class="w-12 h-12 rounded-full border-[3px] border-purple-500/20"></div>
                                <div
                                    class="absolute inset-0 w-12 h-12 rounded-full border-[3px] border-transparent border-t-purple-500 border-r-pink-500 animate-spin">
                                </div>
                            </div>
                            <span class="text-sm text-white/60 font-medium"
                                x-text="loadingMessages[currentLoadingMessage]"></span>
                            <div class="flex gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-bounce"
                                    style="animation-delay:0ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-bounce"
                                    style="animation-delay:150ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-bounce"
                                    style="animation-delay:300ms"></span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Gallery Items --}}
                @forelse($history as $index => $image)
                    <div @click="openPreview(historyData[{{ $index }}], {{ $index }})"
                        class="group relative aspect-[4/5] rounded-xl bg-[#16171c] border border-white/[0.06] overflow-hidden cursor-pointer transition-all duration-300 hover:border-white/15 hover:shadow-lg hover:shadow-black/30 active:scale-[0.98]">
                        <img src="{{ $image->image_url }}" alt="{{ Str::limit($image->final_prompt ?? 'AI Image', 50) }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.03]"
                            loading="lazy">

                        {{-- Hover overlay desktop --}}
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 hidden sm:flex flex-col justify-end p-3">
                            <p class="text-[10px] text-white/70 line-clamp-2 italic mb-2">
                                "{{ Str::limit($image->final_prompt, 80) }}"</p>
                            <div class="flex items-center gap-1.5">
                                <button @click.stop="openPreview(historyData[{{ $index }}], {{ $index }})"
                                    class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/40 text-white flex items-center justify-center transition-all text-xs"><i
                                        class="fa-solid fa-expand"></i></button>
                                <a href="{{ $image->image_url }}" download @click.stop
                                    class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/40 text-white flex items-center justify-center transition-all text-xs"><i
                                        class="fa-solid fa-download"></i></a>
                                <button
                                    @click.stop="if(!selectedImages.find(i=>i.url==='{{ $image->image_url }}')){ selectedImages.push({type:'url',url:'{{ $image->image_url }}',id:Date.now()}); notify('Đã thêm mẫu'); }"
                                    class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm hover:bg-white/40 text-white flex items-center justify-center transition-all text-xs"><i
                                        class="fa-solid fa-images"></i></button>
                            </div>
                        </div>

                        {{-- Mobile tap hint --}}
                        <div
                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/30 backdrop-blur-sm flex items-center justify-center sm:hidden">
                            <i class="fa-solid fa-expand text-white/50 text-[9px]"></i>
                        </div>

                        {{-- Metadata badge --}}
                        <div class="absolute bottom-0 inset-x-0 p-2 sm:group-hover:opacity-0 transition-opacity">
                            <div class="flex items-center gap-1 text-[9px] text-white/40">
                                @if(isset($image->generation_params['model_id']))
                                    <span
                                        class="px-1.5 py-0.5 rounded bg-black/40 backdrop-blur-sm">{{ Str::limit($image->generation_params['model_id'], 15) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    @if(!$isGenerating)
                        <div class="col-span-full py-16 sm:py-24 text-center"
                            x-data="{ prompts: ['Một chú mèo dễ thương ngủ trên mây', 'Phong cảnh núi tuyết hoàng hôn', 'Logo công nghệ gradient xanh'] }">
                            <div
                                class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-white/[0.06] flex items-center justify-center">
                                <i class="fa-solid fa-wand-magic-sparkles text-purple-400/50 text-xl"></i>
                            </div>
                            <h3 class="text-white/70 font-semibold text-base mb-1.5">Bắt đầu sáng tạo</h3>
                            <p class="text-white/25 text-sm mb-6 max-w-xs mx-auto">Nhập mô tả ở ô phía dưới và nhấn nút gửi</p>
                            <div class="flex flex-wrap justify-center gap-2 max-w-md mx-auto">
                                <template x-for="(p, i) in prompts" :key="i">
                                    <button @click="$wire.set('prompt', p); $nextTick(() => $refs.promptInput?.focus())"
                                        class="px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-white/35 text-xs hover:text-white/70 hover:bg-purple-500/10 hover:border-purple-500/20 transition-all active:scale-95">
                                        <i class="fa-solid fa-sparkles text-purple-400/30 mr-1 text-[9px]"></i>
                                        <span x-text="p"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endforelse
            </div>

            {{-- Load More --}}
            @if(method_exists($history, 'hasMorePages') && $history->hasMorePages())
                <div class="mt-6 text-center">
                    <button wire:click="loadMore"
                        class="px-6 py-2.5 rounded-xl bg-white/[0.04] border border-white/[0.08] text-sm text-white/40 hover:text-white/70 hover:bg-white/[0.08] transition-all font-medium disabled:opacity-50"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="loadMore">Tải thêm</span>
                        <span wire:loading wire:target="loadMore"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang
                            tải...</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 2: FIXED BOTTOM PROMPT BAR --}}
    {{-- ============================================================ --}}
    <div class="fixed bottom-[60px] md:bottom-0 left-0 right-0 md:left-[72px] z-[60] border-t border-white/[0.08] bg-[#0d0e12]/95 backdrop-blur-xl"
        @click.away="showRatioMenu = false; showModelMenu = false" x-data="{
            selectedRatio: '{{ $aspectRatio }}',
            selectedModel: '{{ $modelId }}',
            ratios: [
                { id: 'auto', label: 'Tự động', icon: 'fa-expand' },
                { id: '1:1', label: '1:1', w: 12, h: 12 },
                { id: '16:9', label: '16:9', w: 16, h: 9 },
                { id: '9:16', label: '9:16', w: 9, h: 16 },
                { id: '4:3', label: '4:3', w: 14, h: 10 },
                { id: '3:4', label: '3:4', w: 10, h: 14 },
                { id: '3:2', label: '3:2', w: 15, h: 10 },
                { id: '21:9', label: '21:9', w: 18, h: 8 }
            ],
            getRatioLabel() {
                const r = this.ratios.find(r => r.id === this.selectedRatio);
                return r ? r.label : this.selectedRatio;
            },
            getModelShort() {
                const m = Object.values(@js($availableModels)).find(m => m.id === this.selectedModel);
                if (!m) return 'Model';
                return m.name.length > 12 ? m.name.substring(0,11)+'…' : m.name;
            },
            getModelFull() {
                const m = Object.values(@js($availableModels)).find(m => m.id === this.selectedModel);
                return m ? m.name : 'Model';
            }
        }">

        {{-- Prompt Row --}}
        <div class="max-w-5xl mx-auto px-3 sm:px-4 pt-3 pb-2">
            <div class="flex items-end gap-2">

                {{-- Reference image button --}}
                <button type="button"
                    @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                    class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-all mb-0.5"
                    :class="selectedImages.length > 0 ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 'bg-white/[0.06] text-white/40 hover:text-white/60 hover:bg-white/[0.1] border border-white/[0.08]'">
                    <template x-if="selectedImages.length > 0">
                        <span class="relative">
                            <i class="fa-solid fa-images text-sm"></i>
                            <span
                                class="absolute -top-1 -right-1.5 w-3.5 h-3.5 rounded-full bg-purple-500 text-white text-[8px] font-bold flex items-center justify-center"
                                x-text="selectedImages.length"></span>
                        </span>
                    </template>
                    <template x-if="selectedImages.length === 0">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </template>
                </button>

                {{-- Textarea --}}
                <div class="flex-1 relative">
                    <textarea x-ref="promptInput" wire:model.live="prompt" rows="1"
                        placeholder="Mô tả ảnh bạn muốn tạo..."
                        class="w-full min-h-[42px] max-h-[120px] px-4 py-2.5 pr-12 bg-white/[0.06] border border-white/[0.1] rounded-xl text-white text-sm placeholder-white/25 resize-none outline-none focus:border-purple-500/40 transition-colors leading-relaxed"
                        style="field-sizing: content;" @keydown.ctrl.enter.prevent="$wire.generate()"
                        @keydown.meta.enter.prevent="$wire.generate()" {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    {{-- Send button --}}
                    @if($isGenerating)
                        <button type="button" wire:click="cancelGeneration"
                            class="absolute right-1.5 bottom-1.5 w-8 h-8 rounded-lg bg-red-500/80 hover:bg-red-500 text-white flex items-center justify-center transition-all"
                            title="Hủy tạo ảnh">
                            <i class="fa-solid fa-stop text-xs"></i>
                        </button>
                    @else
                        <button type="button" wire:click="generate"
                            class="absolute right-1.5 bottom-1.5 w-8 h-8 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white flex items-center justify-center transition-all hover:shadow-lg hover:shadow-purple-500/30 active:scale-95 disabled:opacity-40"
                            wire:loading.attr="disabled" wire:target="generate" title="Tạo ảnh (Ctrl+Enter)">
                            <i class="fa-solid fa-arrow-up text-xs" wire:loading.remove wire:target="generate"></i>
                            <i class="fa-solid fa-spinner fa-spin text-xs" wire:loading wire:target="generate"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Options Row --}}
        <div class="max-w-5xl mx-auto px-3 sm:px-4 pb-3 sm:pb-4">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">

                {{-- Type badge --}}
                <div
                    class="flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-purple-500/15 text-purple-400 text-xs font-medium">
                    <i class="fa-solid fa-image text-[10px]"></i>
                    <span>Image</span>
                </div>

                {{-- Model --}}
                <div class="relative">
                    <button type="button" @click="showModelMenu = !showModelMenu; showRatioMenu = false"
                        class="flex items-center gap-1.5 h-7 px-2.5 rounded-full text-xs transition-all"
                        :class="showModelMenu ? 'bg-white/15 text-white' : 'bg-white/[0.06] text-white/50 hover:text-white/70 hover:bg-white/[0.1]'">
                        <i class="fa-solid fa-microchip text-[10px]"></i>
                        <span class="sm:hidden" x-text="getModelShort()"></span>
                        <span class="hidden sm:inline" x-text="getModelFull()"></span>
                        <i class="fa-solid fa-chevron-down text-[7px] ml-0.5"></i>
                    </button>
                    <div x-show="showModelMenu" x-cloak @click.away="showModelMenu = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute bottom-full left-0 mb-2 p-1.5 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-[calc(100vw-2rem)] sm:w-64">
                        <template x-for="model in Object.values(@js($availableModels))" :key="model.id">
                            <button type="button"
                                @click="selectedModel = model.id; $wire.set('modelId', model.id); showModelMenu = false"
                                class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all text-left text-sm"
                                :class="selectedModel === model.id ? 'bg-purple-500/20 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white'">
                                <i class="fa-solid fa-microchip text-purple-400/40 text-xs"></i>
                                <span class="flex-1 truncate" x-text="model.name"></span>
                                <i x-show="selectedModel === model.id"
                                    class="fa-solid fa-check text-purple-400 text-[10px]"></i>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Ratio --}}
                <div class="relative">
                    <button type="button" @click="showRatioMenu = !showRatioMenu; showModelMenu = false"
                        class="flex items-center gap-1.5 h-7 px-2.5 rounded-full text-xs transition-all"
                        :class="showRatioMenu ? 'bg-white/15 text-white' : 'bg-white/[0.06] text-white/50 hover:text-white/70 hover:bg-white/[0.1]'">
                        <i class="fa-solid fa-crop text-[10px]"></i>
                        <span x-text="getRatioLabel()"></span>
                        <i class="fa-solid fa-chevron-down text-[7px] ml-0.5"></i>
                    </button>
                    <div x-show="showRatioMenu" x-cloak @click.away="showRatioMenu = false"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute bottom-full left-0 mb-2 p-2.5 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-[calc(100vw-2rem)] sm:w-[240px]">
                        <div class="grid grid-cols-4 gap-1">
                            <template x-for="r in ratios" :key="r.id">
                                <button type="button"
                                    @click="selectedRatio = r.id; $wire.set('aspectRatio', r.id); showRatioMenu = false"
                                    class="flex flex-col items-center gap-1 p-2 rounded-lg transition-all"
                                    :class="selectedRatio === r.id ? 'bg-purple-500/25 ring-1 ring-purple-500/40' : 'hover:bg-white/5'">
                                    <div class="w-5 h-5 flex items-center justify-center">
                                        <template x-if="r.icon"><i :class="'fa-solid '+r.icon"
                                                class="text-white/40 text-xs"></i></template>
                                        <template x-if="!r.icon">
                                            <div class="border border-white/30 rounded-[2px]"
                                                :style="{width:r.w+'px',height:r.h+'px'}"></div>
                                        </template>
                                    </div>
                                    <span class="text-white/40 text-[9px]" x-text="r.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Credits cost --}}
                <div
                    class="flex items-center gap-1.5 h-7 px-2.5 rounded-full bg-white/[0.04] text-white/30 text-xs ml-auto">
                    <i class="fa-solid fa-bolt text-yellow-400/60 text-[10px]"></i>
                    <span>{{ number_format($creditCost, 0) }}</span>
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
    </style>
</div>