<div class="relative min-h-screen" x-data="{
    // Data from server
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    historyData: @js($historyData),

    // Dropdowns
    showRatioMenu: false,
    showModelMenu: false,
    selectedRatio: '{{ $aspectRatio }}',
    selectedModel: '{{ $modelId }}',

    // Ratio options
    ratios: [
        { id: 'auto', label: 'Tự động', icon: 'fa-expand' },
        { id: '1:1', label: '1:1', w: 14, h: 14 },
        { id: '16:9', label: '16:9', w: 18, h: 10 },
        { id: '9:16', label: '9:16', w: 10, h: 18 },
        { id: '4:3', label: '4:3', w: 16, h: 12 },
        { id: '3:4', label: '3:4', w: 12, h: 16 },
        { id: '3:2', label: '3:2', w: 18, h: 12 },
        { id: '21:9', label: '21:9', w: 21, h: 9 }
    ],

    // Image picker
    showImagePicker: false,
    selectedImages: [],
    maxImages: 4,
    recentImages: [],
    isLoadingPicker: false,
    urlInput: '',
    activeTab: 'upload',
    isDragging: false,

    // Preview modal
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

    // Character count
    charCount: {{ strlen($prompt) }},

    // --- Methods ---
    getModelName() {
        const m = Object.values(this.models).find(m => m.id === this.selectedModel);
        return m ? m.name : 'Model';
    },
    getShortName() {
        const n = this.getModelName();
        return n.length > 10 ? n.substring(0, 9) + '…' : n;
    },
    getRatioLabel() {
        const r = this.ratios.find(r => r.id === this.selectedRatio);
        return r ? r.label : this.selectedRatio;
    },

    // Notifications
    notify(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showToast = true;
        setTimeout(() => this.showToast = false, 2500);
    },

    // Loading messages
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
        this.previewImage = image;
        this.previewIndex = index;
        this.showPreview = true;
        document.body.style.overflow = 'hidden';
    },
    closePreview() {
        this.showPreview = false;
        this.previewImage = null;
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
    goToImage(i) {
        if (i >= 0 && i < this.historyData.length) {
            this.previewIndex = i;
            this.previewImage = this.historyData[i];
        }
    },
    handleTouchStart(e) {
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
    },
    handleTouchEnd(e) {
        const dx = e.changedTouches[0].clientX - this.touchStartX;
        const dy = e.changedTouches[0].clientY - this.touchStartY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 50) {
            dx > 0 ? this.prevImage() : this.nextImage();
        } else if (dy > 100) {
            this.closePreview();
        }
    },
    useAsReference() {
        if (this.previewImage && this.selectedImages.length < this.maxImages) {
            if (!this.selectedImages.find(img => img.url === this.previewImage.url)) {
                this.selectedImages.push({ type: 'url', url: this.previewImage.url, id: Date.now() });
            }
            this.notify('Đã thêm vào ảnh mẫu');
            this.closePreview();
        }
    },
    copyPrompt() {
        if (this.previewImage?.prompt) {
            $wire.set('prompt', this.previewImage.prompt);
            this.notify('Đã copy prompt');
            this.closePreview();
        }
    },
    async shareImage() {
        if (navigator.share && this.previewImage) {
            try { await navigator.share({ title: 'ZDream AI', url: this.previewImage.url }); }
            catch(e) {}
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
    handleFileSelect(e) {
        Array.from(e.target.files).forEach(f => this.processFile(f));
        e.target.value = '';
    },
    handleDrop(e) {
        this.isDragging = false;
        Array.from(e.dataTransfer.files).forEach(f => this.processFile(f));
    },
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
        this.urlInput = '';
        this.notify('Đã thêm ảnh');
    },
    selectFromRecent(url) {
        if (this.selectedImages.find(i => i.url === url)) {
            this.selectedImages = this.selectedImages.filter(i => i.url !== url); return;
        }
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
        const refresh = async () => {
            const d = await $wire.getHistoryData();
            if (d) this.historyData = d;
        };
        $wire.on('historyUpdated', refresh);
        $wire.on('imageGenerated', refresh);
        Livewire.hook('morph.updated', refresh);
    }
}" @keydown.window="handleKeydown($event)" @if($isGenerating) wire:poll.3s="pollImageStatus" @endif>

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl flex items-center gap-2"
        :class="{
            'bg-green-500/95': toastType === 'success',
            'bg-red-500/95': toastType === 'error',
            'bg-yellow-500/95 text-black': toastType === 'warning'
        }">
        <i :class="{
            'fa-solid fa-check-circle': toastType === 'success',
            'fa-solid fa-exclamation-circle': toastType === 'error',
            'fa-solid fa-exclamation-triangle': toastType === 'warning'
        }"></i>
        <span x-text="toastMessage"></span>
    </div>

    {{-- ==================== MAIN CONTENT ==================== --}}
    <div class="max-w-4xl mx-auto px-4 pt-6 sm:pt-8 pb-24 md:pb-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-wand-magic-sparkles text-white text-base"></i>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white leading-tight">Tạo ảnh AI</h1>
                    <p class="text-white/40 text-xs mt-0.5">Biến ý tưởng thành hình ảnh</p>
                </div>
            </div>
            @auth
                <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/10">
                    <i class="fa-solid fa-coins text-yellow-400 text-sm"></i>
                    <span
                        class="text-white font-bold text-sm">{{ number_format(auth()->user()->credits ?? 0, 0, ',', '.') }}</span>
                    <span class="text-white/40 text-xs">cr</span>
                </div>
            @endauth
        </div>

        {{-- Error --}}
        @if($errorMessage)
            <div x-data="{ show: true }" x-show="show" x-cloak x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-5 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3"
                role="alert">
                <i class="fa-solid fa-circle-exclamation shrink-0 text-lg"></i>
                <span class="flex-1">{{ $errorMessage }}</span>
                @if($lastPrompt)
                    <button wire:click="retry"
                        class="shrink-0 px-3 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 text-xs font-medium transition-colors">
                        <i class="fa-solid fa-redo mr-1"></i>Thử lại
                    </button>
                @endif
                <button @click="show = false; setTimeout(() => $wire.set('errorMessage', null), 200)"
                    class="shrink-0 w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- ===== UNIFIED PROMPT CARD ===== --}}
        <div class="mb-6 group/prompt" @click.away="showRatioMenu = false; showModelMenu = false">
            <div class="relative">
                {{-- Glow --}}
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-0 blur-xl transition-opacity duration-500 group-focus-within/prompt:opacity-20 pointer-events-none">
                </div>

                <div
                    class="relative rounded-2xl bg-[#16171c] border border-white/[0.08] group-focus-within/prompt:border-purple-500/30 transition-colors overflow-hidden shadow-xl shadow-black/20">

                    {{-- Section 1: Textarea --}}
                    <div class="p-4 sm:p-5">
                        <label
                            class="flex items-center gap-1.5 text-white/40 text-[11px] font-medium uppercase tracking-wider mb-3">
                            <i class="fa-solid fa-sparkles text-purple-400/60 text-[9px]"></i>
                            Mô tả ý tưởng
                        </label>
                        <textarea wire:model.live="prompt" rows="3"
                            placeholder="Ví dụ: Một chú mèo dễ thương đang ngủ trên đám mây tím, phong cách anime..."
                            class="w-full min-h-[100px] sm:min-h-[120px] bg-transparent border-none outline-none ring-0 focus:ring-0 text-white placeholder-white/20 text-sm sm:text-base resize-y leading-relaxed"
                            @keydown.ctrl.enter.prevent="$wire.generate()"
                            @keydown.meta.enter.prevent="$wire.generate()"
                            @input="charCount = $event.target.value.length" {{ $isGenerating ? 'disabled' : '' }}></textarea>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-white/15 text-[10px] hidden sm:inline">Ctrl+Enter để tạo</span>
                            <span class="text-[11px] font-medium"
                                :class="charCount > 1800 ? 'text-red-400' : charCount > 1500 ? 'text-yellow-400' : 'text-white/20'">
                                <span x-text="charCount"></span>/2000
                            </span>
                        </div>
                    </div>

                    {{-- Section 2: Options --}}
                    <div
                        class="flex flex-wrap items-center gap-1.5 sm:gap-2 px-4 sm:px-5 py-2.5 border-t border-white/[0.05] bg-white/[0.015]">

                        {{-- Ratio --}}
                        <div class="relative">
                            <button type="button" @click="showRatioMenu = !showRatioMenu; showModelMenu = false"
                                class="flex items-center gap-1.5 h-8 px-2.5 sm:px-3 rounded-lg text-xs sm:text-sm transition-all"
                                :class="showRatioMenu ? 'bg-purple-500/15 text-purple-300' : 'bg-white/[0.04] hover:bg-white/[0.08] text-white/60 hover:text-white/80'">
                                <i class="fa-solid fa-crop text-[10px]"></i>
                                <span x-text="getRatioLabel()"></span>
                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                                    :class="showRatioMenu && 'rotate-180'"></i>
                            </button>
                            <div x-show="showRatioMenu" x-cloak @click.away="showRatioMenu = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute top-full left-0 mt-2 p-2.5 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-[calc(100vw-2rem)] sm:w-[260px]">
                                <div class="text-white/30 text-[10px] font-medium uppercase tracking-wider mb-2 px-1">Tỉ
                                    lệ</div>
                                <div class="grid grid-cols-4 gap-1">
                                    <template x-for="r in ratios" :key="r.id">
                                        <button type="button"
                                            @click="selectedRatio = r.id; $wire.set('aspectRatio', r.id); showRatioMenu = false"
                                            class="flex flex-col items-center gap-1 p-2 rounded-lg transition-all text-center"
                                            :class="selectedRatio === r.id ? 'bg-purple-500/25 ring-1 ring-purple-500/40' : 'hover:bg-white/5'">
                                            <div class="w-5 h-5 flex items-center justify-center">
                                                <template x-if="r.icon"><i :class="'fa-solid ' + r.icon"
                                                        class="text-white/40 text-xs"></i></template>
                                                <template x-if="!r.icon">
                                                    <div class="border border-white/30 rounded-[2px]"
                                                        :style="{ width: r.w+'px', height: r.h+'px' }"></div>
                                                </template>
                                            </div>
                                            <span class="text-white/50 text-[10px]" x-text="r.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Model --}}
                        <div class="relative">
                            <button type="button" @click="showModelMenu = !showModelMenu; showRatioMenu = false"
                                class="flex items-center gap-1.5 h-8 px-2.5 sm:px-3 rounded-lg text-xs sm:text-sm transition-all"
                                :class="showModelMenu ? 'bg-purple-500/15 text-purple-300' : 'bg-white/[0.04] hover:bg-white/[0.08] text-white/60 hover:text-white/80'">
                                <i class="fa-solid fa-microchip text-[10px]"></i>
                                <span class="sm:hidden" x-text="getShortName()"></span>
                                <span class="hidden sm:inline" x-text="getModelName()"></span>
                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                                    :class="showModelMenu && 'rotate-180'"></i>
                            </button>
                            <div x-show="showModelMenu" x-cloak @click.away="showModelMenu = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="absolute top-full left-0 mt-2 p-2 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-[calc(100vw-2rem)] sm:w-72">
                                <div class="text-white/30 text-[10px] font-medium uppercase tracking-wider mb-2 px-2">
                                    Model AI</div>
                                <template x-for="model in Object.values(models)" :key="model.id">
                                    <button type="button"
                                        @click="selectedModel = model.id; $wire.set('modelId', model.id); showModelMenu = false"
                                        class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all text-left"
                                        :class="selectedModel === model.id ? 'bg-purple-500/20' : 'hover:bg-white/5'">
                                        <i class="fa-solid fa-microchip text-purple-400/50 text-sm"></i>
                                        <span class="flex-1 text-white text-sm truncate" x-text="model.name"></span>
                                        <i x-show="selectedModel === model.id"
                                            class="fa-solid fa-check text-purple-400 text-xs"></i>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Reference Images --}}
                        <button type="button"
                            @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                            class="flex items-center gap-1.5 h-8 px-2.5 sm:px-3 rounded-lg text-xs sm:text-sm transition-all"
                            :class="selectedImages.length > 0 ? 'bg-purple-500/15 text-purple-300' : 'bg-white/[0.04] hover:bg-white/[0.08] text-white/60 hover:text-white/80'">
                            <template x-if="selectedImages.length > 0">
                                <span class="flex items-center gap-1.5">
                                    <span class="flex -space-x-1">
                                        <template x-for="img in selectedImages.slice(0,3)" :key="img.id">
                                            <img :src="img.url"
                                                class="w-5 h-5 rounded border border-purple-500/50 object-cover">
                                        </template>
                                    </span>
                                    <span x-text="selectedImages.length + ' ảnh'"></span>
                                </span>
                            </template>
                            <template x-if="selectedImages.length === 0">
                                <span class="flex items-center gap-1"><i class="fa-solid fa-image text-[10px]"></i> Ảnh
                                    mẫu</span>
                            </template>
                        </button>

                        <button x-show="selectedImages.length > 0" x-cloak @click="clearAll()"
                            class="h-8 w-8 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs flex items-center justify-center transition-all">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    {{-- Section 3: Generate Button --}}
                    <div class="px-3 sm:px-4 py-3 border-t border-white/[0.05]" id="generate-section">
                        @if($isGenerating)
                            <button type="button" wire:click="cancelGeneration"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 font-semibold text-sm transition-all">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                                <span>Đang tạo ảnh...</span>
                                <span class="text-red-400/50 text-xs">(Nhấn để hủy)</span>
                            </button>
                        @else
                            <button type="button" wire:click="generate"
                                class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-gradient-to-r from-purple-600 via-fuchsia-500 to-pink-500 text-white font-bold text-sm sm:text-base shadow-lg shadow-purple-500/25 hover:shadow-purple-500/40 hover:brightness-110 active:scale-[0.99] transition-all disabled:opacity-50"
                                wire:loading.attr="disabled" wire:target="generate">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm" wire:loading.remove
                                    wire:target="generate"></i>
                                <i class="fa-solid fa-spinner fa-spin text-sm" wire:loading wire:target="generate"></i>
                                <span wire:loading.remove wire:target="generate">Tạo ảnh</span>
                                <span wire:loading wire:target="generate">Đang xử lý...</span>
                                <span
                                    class="ml-1 px-2 py-0.5 rounded-full bg-white/20 text-white/90 text-xs">-{{ number_format($creditCost, 0) }}
                                    cr</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== GALLERY ===== --}}
        <div class="pt-6 border-t border-white/[0.06]" id="gallery-section">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm sm:text-base font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-images text-purple-400/70 text-xs"></i>
                    Ảnh đã tạo
                </h2>
                <span class="text-white/30 text-xs">{{ $history->total() ?? 0 }} ảnh</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">

                {{-- Loading Skeleton --}}
                @if($isGenerating && !$generatedImageUrl)
                    <div x-init="startLoading(); $nextTick(() => document.getElementById('gallery-section')?.scrollIntoView({behavior:'smooth',block:'start'}))"
                        x-effect="if (!@js($isGenerating)) stopLoading()"
                        class="col-span-2 sm:col-span-1 aspect-[4/5] rounded-xl bg-[#16171c] border border-purple-500/20 overflow-hidden relative">
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
                        class="group relative aspect-[4/5] rounded-xl bg-[#16171c] border border-white/[0.06] overflow-hidden transition-all duration-300 hover:border-purple-500/25 hover:shadow-lg hover:shadow-purple-500/10 cursor-pointer active:scale-[0.98]">
                        <img src="{{ $image->image_url }}"
                            alt="{{ Str::limit($image->final_prompt ?? 'AI Generated Image', 60) }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                            loading="lazy">
                        {{-- Desktop overlay --}}
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 hidden sm:flex items-end p-3">
                            <p class="text-[10px] text-white/70 line-clamp-2 italic">
                                "{{ Str::limit($image->final_prompt, 80) }}"</p>
                        </div>
                        {{-- Mobile tap indicator --}}
                        <div
                            class="absolute top-2 right-2 w-6 h-6 rounded-full bg-black/30 backdrop-blur-sm flex items-center justify-center sm:hidden">
                            <i class="fa-solid fa-expand text-white/50 text-[9px]"></i>
                        </div>
                    </div>
                @empty
                    @if(!$isGenerating)
                        <div class="col-span-full py-10 sm:py-14 text-center"
                            x-data="{ prompts: ['Một chú mèo đáng yêu ngủ trên đám mây', 'Phong cảnh núi tuyết hoàng hôn', 'Logo công nghệ gradient xanh'] }">
                            <div
                                class="w-14 h-14 mx-auto mb-4 rounded-xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-white/[0.06] flex items-center justify-center">
                                <i class="fa-solid fa-wand-magic-sparkles text-purple-400/60 text-lg"></i>
                            </div>
                            <h3 class="text-white/80 font-semibold text-sm mb-1">Chưa có ảnh nào</h3>
                            <p class="text-white/30 text-xs mb-4">Nhập mô tả phía trên và nhấn Tạo ảnh</p>
                            <div class="flex flex-wrap justify-center gap-2 max-w-md mx-auto">
                                <template x-for="(p, i) in prompts" :key="i">
                                    <button @click="$wire.set('prompt', p); document.querySelector('textarea').focus()"
                                        class="px-3 py-1.5 rounded-lg bg-white/[0.04] border border-white/[0.06] text-white/40 text-xs hover:text-white/70 hover:bg-purple-500/10 hover:border-purple-500/20 transition-all active:scale-95">
                                        <i class="fa-solid fa-sparkles text-purple-400/40 mr-1 text-[9px]"></i>
                                        <span x-text="p.length > 30 ? p.substring(0,28)+'...' : p"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        {{-- Load More --}}
        @if(method_exists($history, 'hasMorePages') && $history->hasMorePages())
            <div class="mt-8 text-center">
                <button wire:click="loadMore"
                    class="px-8 py-3 rounded-xl bg-white/5 border border-white/[0.08] text-sm text-white/50 hover:text-white hover:bg-white/10 transition-all font-medium disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="loadMore">Tải thêm</span>
                    <span wire:loading wire:target="loadMore"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Đang
                        tải...</span>
                </button>
            </div>
        @endif
    </div>

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