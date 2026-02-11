<div class="relative min-h-screen pb-48 md:pb-32" x-data="{
    selectedRatio: @entangle('aspectRatio'),
    selectedModel: @entangle('modelId'),
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    historyData: @js($historyData),

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
                        class="shrink-0 px-3 py-1 rounded-lg bg-white/10 hover:bg-white/15 text-xs font-medium transition-colors">
                        <i class="fa-solid fa-xmark mr-1"></i>Đóng
                    </button>
                </div>
            @endif

            {{-- Gallery Feed --}}
            <div class="space-y-8 pb-32" id="gallery-feed">

                {{-- Loading Skeleton --}}
                @if($isGenerating && !$generatedImageUrl)
                    <div x-init="startLoading(); $nextTick(() => document.getElementById('gallery-scroll')?.scrollTo({top:0,behavior:'smooth'}))"
                        x-effect="if (!@js($isGenerating)) stopLoading()"
                        class="bg-[#131419] rounded-2xl border border-white/5 overflow-hidden animate-pulse">
                        <div class="p-4 border-b border-white/5 space-y-3">
                            <div class="h-4 bg-white/10 rounded w-3/4"></div>
                            <div class="flex gap-2">
                                <div class="h-6 w-20 bg-white/5 rounded"></div>
                                <div class="h-6 w-20 bg-white/5 rounded"></div>
                            </div>
                        </div>
                        <div class="aspect-square bg-white/5 relative flex items-center justify-center">
                            <div class="text-center">
                                <div class="inline-block w-8 h-8 border-2 border-purple-500 border-t-transparent rounded-full animate-spin mb-2"></div>
                                <p class="text-white/40 text-xs" x-text="loadingMessages[currentLoadingMessage]"></p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Gallery Items --}}
                @forelse($history as $index => $image)
                    <div class="group relative bg-[#131419] rounded-2xl border border-white/5 overflow-hidden hover:border-white/10 transition-colors">
                        {{-- 1. Header: Prompt + Meta --}}
                        <div class="p-4 border-b border-white/5 bg-[#16171c]/50">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-white text-sm sm:text-base leading-relaxed break-words font-medium text-white/90">
                                        {{ $image->final_prompt }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 mt-3">
                                        {{-- Model Badge --}}
                                        @php
                                            $modelId = $image->generation_params['model_id'] ?? null;
                                            $modelName = $modelId;
                                            if ($modelId) {
                                                $model = collect($availableModels)->firstWhere('id', $modelId);
                                                $modelName = $model['name'] ?? $modelId;
                                            }
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 text-[10px] sm:text-xs text-white/50 border border-white/5 font-medium">
                                            <i class="fa-solid fa-microchip text-[9px]"></i>
                                            {{ $modelName }}
                                        </span>
                                        
                                        {{-- Ratio Badge --}}
                                        @if(isset($image->generation_params['aspect_ratio']))
                                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-white/5 text-[10px] sm:text-xs text-white/50 border border-white/5 font-medium">
                                                <i class="fa-solid fa-crop text-[9px]"></i>
                                                {{ $image->generation_params['aspect_ratio'] }}
                                            </span>
                                        @endif

                                        {{-- Time --}}
                                        <span class="text-[10px] text-white/30 ml-auto flex items-center gap-1">
                                            <i class="fa-regular fa-clock"></i>
                                            {{ $image->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- Simple Actions Dropdown --}}
                                <div class="relative shrink-0" x-data="{ open: false }">
                                    <button @click="open = !open" @click.away="open = false" 
                                        class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center text-white/40 hover:text-white transition-colors">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>
                                    <div x-show="open" x-cloak 
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="absolute right-0 top-full mt-1 w-48 bg-[#1a1b20] border border-white/10 rounded-xl shadow-xl z-10 py-1">
                                        <button wire:click="deleteImage({{ $image->id }})" 
                                            class="w-full text-left px-4 py-2.5 text-xs text-red-400 hover:bg-white/5 flex items-center gap-2">
                                            <i class="fa-solid fa-trash"></i> Xóa ảnh
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Image Area --}}
                        <div class="relative bg-black/40 min-h-[200px] flex items-center justify-center p-0.5 sm:p-2">
                            <div class="relative group/image max-w-full overflow-hidden rounded-lg cursor-zoom-in"
                                @click="openPreview(historyData[{{ $index }}], {{ $index }})">
                                <img src="{{ $image->image_url }}" 
                                    alt="{{ Str::limit($image->final_prompt, 50) }}"
                                    class="max-w-full max-h-[600px] object-contain shadow-lg"
                                    loading="lazy">
                                    
                                {{-- Hover Actions Overlay --}}
                                <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-black/80 to-transparent opacity-0 group-hover/image:opacity-100 transition-opacity flex justify-end gap-2">
                                    <a href="{{ $image->image_url }}" download @click.stop 
                                        class="w-9 h-9 rounded-lg bg-white/10 backdrop-blur hover:bg-white/20 text-white flex items-center justify-center transition-all border border-white/10"
                                        title="Tải về">
                                        <i class="fa-solid fa-download text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Footer Action Bar --}}
                        <div class="px-4 py-3 bg-[#16171c]/50 border-t border-white/5 flex items-center gap-2 sm:gap-3 overflow-x-auto no-scrollbar">
                            <button wire:click="copyPrompt({{ $image->id }})"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 hover:border-white/10 text-xs text-white/70 transition-all whitespace-nowrap">
                                <i class="fa-regular fa-copy"></i>
                                <span>Copy Prompt</span>
                            </button>
                            
                            <button wire:click="reusePrompt({{ $image->id }})"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/5 hover:border-white/10 text-xs text-white/70 transition-all whitespace-nowrap">
                                <i class="fa-solid fa-sliders"></i>
                                <span>Dùng lại Settings</span>
                            </button>
                        </div>
                    </div>
                @empty
                    @if(!$isGenerating)
                        <div class="col-span-full py-16 sm:py-24 text-center"
                            x-data="{ prompts: ['Một chú mèo dễ thương ngủ trên mây', 'Phong cảnh núi tuyết hoàng hôn', 'Logo công nghệ gradient xanh'] }">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-white/5 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-image text-3xl text-white/20"></i>
                            </div>
                            <h3 class="text-white font-medium text-lg mb-2">Chưa có hình ảnh nào</h3>
                            <p class="text-white/40 text-sm max-w-sm mx-auto mb-6">
                                Hãy thử tạo một hình ảnh mới bằng cách nhập mô tả vào khung chat bên dưới.
                            </p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <template x-for="p in prompts">
                                    <button @click="$wire.set('prompt', p)" 
                                        class="px-3 py-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-xs text-white/60 hover:text-white transition-all border border-white/5 hover:border-white/10">
                                        <span x-text="p"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endforelse
                
                {{-- Pagination --}}
                @if($history->hasMorePages())
                    <div class="pt-4 text-center">
                         <button wire:click="loadMore" class="text-xs text-white/40 hover:text-white transition-colors">
                             Xem thêm cũ hơn
                         </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECTION 2: FIXED BOTTOM PROMPT BAR (Home page style) --}}
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
    </style>
</div>