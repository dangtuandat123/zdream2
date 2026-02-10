<div class="relative min-h-screen" x-data="{
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    showRatioDropdown: false,
    showModelDropdown: false,
    
    // Image picker state
    showImagePicker: false,
    selectedImages: [],
    maxImages: 4,
    recentImages: [],
    isLoadingPicker: false,
    urlInput: '',
    activeTab: 'upload',
    isDragging: false,
    
    // Image preview modal state
    showPreview: false,
    previewImage: null,
    previewIndex: 0,
    historyData: @js($historyData),
    
    // Toast notification
    toastMessage: '',
    toastType: 'success',
    showToast: false,
    
    // Loading messages
    loadingMessages: ['Đang sáng tạo...', 'Chút nữa thôi...', 'Sắp xong rồi...', 'AI đang vẽ...'],
    currentLoadingMessage: 0,
    loadingInterval: null,
    
    // Touch tracking for swipe
    touchStartX: 0,
    touchStartY: 0,
    
    showNotification(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 2500);
    },
    showError(msg) {
        this.showNotification(msg, 'error');
    },
    showWarning(msg) {
        this.showNotification(msg, 'warning');
    },
    
    startLoadingMessages() {
        this.currentLoadingMessage = 0;
        this.loadingInterval = setInterval(() => {
            this.currentLoadingMessage = (this.currentLoadingMessage + 1) % this.loadingMessages.length;
        }, 2000);
    },
    stopLoadingMessages() {
        if (this.loadingInterval) {
            clearInterval(this.loadingInterval);
            this.loadingInterval = null;
        }
    },
    
    // Preview modal methods
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
    goToImage(index) {
        if (index >= 0 && index < this.historyData.length) {
            this.previewIndex = index;
            this.previewImage = this.historyData[index];
        }
    },
    handleTouchStart(e) {
        this.touchStartX = e.touches[0].clientX;
        this.touchStartY = e.touches[0].clientY;
    },
    handleTouchEnd(e) {
        const deltaX = e.changedTouches[0].clientX - this.touchStartX;
        const deltaY = e.changedTouches[0].clientY - this.touchStartY;
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
            if (deltaX > 0) this.prevImage();
            else this.nextImage();
        } else if (deltaY > 100) {
            this.closePreview();
        }
    },
    useAsReference() {
        if (this.previewImage && this.selectedImages.length < this.maxImages) {
            if (!this.selectedImages.find(img => img.url === this.previewImage.url)) {
                this.selectedImages.push({ 
                    type: 'url', 
                    url: this.previewImage.url, 
                    id: Date.now() 
                });
            }
            this.showNotification('Đã thêm vào ảnh mẫu');
            this.closePreview();
        }
    },
    copyPrompt() {
        if (this.previewImage && this.previewImage.prompt) {
            $wire.set('prompt', this.previewImage.prompt);
            this.showNotification('Đã copy prompt');
            this.closePreview();
        }
    },
    async shareImage() {
        if (navigator.share && this.previewImage) {
            try {
                await navigator.share({ title: 'ZDream AI Image', url: this.previewImage.url });
            } catch (e) { console.log(e); }
        } else {
            await navigator.clipboard.writeText(this.previewImage.url);
            this.showNotification('Đã copy link ảnh');
        }
    },
    
    async loadRecentImages() {
        if (this.recentImages.length > 0) return;
        this.isLoadingPicker = true;
        try {
            const response = await fetch('/api/user/recent-images');
            if (response.ok) {
                const data = await response.json();
                this.recentImages = data.images || [];
            }
        } catch (e) { console.log(e); }
        this.isLoadingPicker = false;
    },
    removeImage(id) {
        this.selectedImages = this.selectedImages.filter(img => img.id !== id);
    },
    handleFileSelect(event) {
        const files = Array.from(event.target.files);
        files.forEach(file => this.processFile(file));
        event.target.value = '';
    },
    handleDrop(event) {
        this.isDragging = false;
        const files = Array.from(event.dataTransfer.files);
        files.forEach(file => this.processFile(file));
    },
    processFile(file) {
        if (this.selectedImages.length >= this.maxImages) {
            this.showWarning('Tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (!file.type.startsWith('image/')) {
            this.showError('Chỉ chấp nhận file ảnh');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            this.showError('Ảnh quá lớn (tối đa 10MB)');
            return;
        }
        const url = URL.createObjectURL(file);
        this.selectedImages.push({ type: 'file', file: file, url: url, id: Date.now() + Math.random() });
    },
    addFromUrl() {
        if (!this.urlInput.trim()) return;
        if (this.selectedImages.length >= this.maxImages) {
            this.showWarning('Tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (!this.urlInput.match(/^https?:\/\/.+\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i)) {
            this.showError('URL ảnh không hợp lệ');
            return;
        }
        this.selectedImages.push({ type: 'url', url: this.urlInput.trim(), id: Date.now() });
        this.urlInput = '';
        this.showNotification('Đã thêm ảnh từ URL');
    },
    selectFromRecent(imageUrl) {
        if (this.selectedImages.length >= this.maxImages) {
            this.showWarning('Đã chọn tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (this.selectedImages.find(img => img.url === imageUrl)) {
            // Toggle off - remove if already selected
            this.selectedImages = this.selectedImages.filter(img => img.url !== imageUrl);
            return;
        }
        this.selectedImages.push({ type: 'url', url: imageUrl, id: Date.now() });
    },
    isSelected(imageUrl) {
        return this.selectedImages.find(img => img.url === imageUrl);
    },
    clearAll() {
        this.selectedImages = [];
    },
    confirmSelection() {
        // Send selected images to backend
        const imageUrls = this.selectedImages.map(img => img.url);
        $wire.setReferenceImages(imageUrls);
        this.showImagePicker = false;
        this.showNotification('Đã chọn ' + this.selectedImages.length + ' ảnh mẫu');
    },
    
    // Keyboard navigation for preview
    handleKeydown(e) {
        if (!this.showPreview) return;
        if (e.key === 'ArrowLeft') this.prevImage();
        else if (e.key === 'ArrowRight') this.nextImage();
        else if (e.key === 'Escape') this.closePreview();
    },
    
    // Init method for setup
    init() {
        // Refresh historyData from server on events
        const refreshHistory = async () => {
            const data = await $wire.getHistoryData();
            if (data) this.historyData = data;
        };
        $wire.on('historyUpdated', refreshHistory);
        $wire.on('imageGenerated', refreshHistory);
        Livewire.hook('morph.updated', refreshHistory);
    }
}" @keydown.window="handleKeydown($event)" @if($isGenerating) wire:poll.3s="pollImageStatus" @endif>

    {{-- Toast Notification --}}
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-24 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl flex items-center gap-2"
        :class="{
            'bg-green-500/95 shadow-green-500/20': toastType === 'success',
            'bg-red-500/95 shadow-red-500/20': toastType === 'error',
            'bg-yellow-500/95 shadow-yellow-500/20 text-black': toastType === 'warning'
        }">
        <i class="text-base" :class="{
            'fa-solid fa-check-circle': toastType === 'success',
            'fa-solid fa-exclamation-circle': toastType === 'error',
            'fa-solid fa-exclamation-triangle': toastType === 'warning'
        }"></i>
        <span x-text="toastMessage"></span>
    </div>

    {{-- ========== MAIN CONTENT AREA ========== --}}
    <div class="max-w-4xl mx-auto px-4 py-6 sm:py-8 pb-8">
        
        {{-- ===== HEADER SECTION ===== --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2.5">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-wand-magic-sparkles text-white text-sm sm:text-base"></i>
                    </div>
                    <div>
                        <span>Tạo ảnh AI</span>
                        <p class="text-white/40 text-xs font-normal mt-0.5">Biến ý tưởng thành hình ảnh</p>
                    </div>
                </h1>
            </div>
            @auth
                <a href="{{ route('credits.index') }}"
                    class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors group">
                    <i class="fa-solid fa-coins text-yellow-400 text-sm"></i>
                    <span class="text-white font-bold text-sm">{{ number_format(auth()->user()->credits ?? 0, 0, ',', '.') }}</span>
                    <i class="fa-solid fa-plus text-white/30 text-[10px] group-hover:text-purple-400 transition-colors"></i>
                </a>
            @endauth
        </div>

        {{-- ===== ERROR MESSAGE ===== --}}
        @if($errorMessage)
            <div x-data="{ show: true }" x-show="show" x-cloak 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" 
                x-transition:enter-end="opacity-100 translate-y-0"
                class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3"
                role="alert">
                <i class="fa-solid fa-circle-exclamation shrink-0 text-lg" aria-hidden="true"></i>
                <span class="flex-1">{{ $errorMessage }}</span>
                @if($lastPrompt)
                    <button wire:click="retry"
                        class="shrink-0 px-3 py-1.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 text-xs font-medium transition-colors">
                        <i class="fa-solid fa-redo mr-1" aria-hidden="true"></i>Thử lại
                    </button>
                @endif
                <button @click="show = false; setTimeout(() => $wire.set('errorMessage', null), 200)"
                    class="shrink-0 w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center transition-colors"
                    aria-label="Đóng">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        {{-- ===== PROMPT CARD (PRIMARY FOCUS) ===== --}}
        <div class="mb-4 group/prompt">
            <div class="relative">
                {{-- Glow effect on focus --}}
                <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-0 blur-lg transition-opacity duration-500 group-focus-within/prompt:opacity-25 pointer-events-none"></div>
                
                <div class="relative rounded-2xl bg-[#1b1c21] border border-white/10 group-focus-within/prompt:border-purple-500/30 transition-colors overflow-hidden">
                    {{-- Textarea --}}
                    <div class="relative p-4 sm:p-5" x-data="{ charCount: {{ strlen($prompt) }} }">
                        <textarea wire:model.live="prompt" rows="3"
                            placeholder="Mô tả ý tưởng của bạn... Ví dụ: Một chú mèo dễ thương đang ngủ trên đám mây tím, phong cách anime"
                            aria-label="Prompt input"
                            class="w-full min-h-[80px] sm:min-h-[100px] bg-transparent border-none outline-none ring-0 focus:ring-0 text-white placeholder-white/30 text-sm sm:text-base resize-y"
                            @keydown.ctrl.enter.prevent="$wire.generate()"
                            @keydown.meta.enter.prevent="$wire.generate()"
                            @input="charCount = $event.target.value.length"
                            {{ $isGenerating ? 'disabled' : '' }}></textarea>
                        
                        {{-- Character counter --}}
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-white/20 text-[10px]">Ctrl+Enter để tạo ảnh</span>
                            <span class="text-xs font-medium transition-colors"
                                :class="charCount > 1800 ? 'text-red-400' : charCount > 1500 ? 'text-yellow-400' : 'text-white/25'">
                                <span x-text="charCount"></span>/2000
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== OPTIONS BAR ===== --}}
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4" x-data="{ 
            showRatioMenu: false,
            showModelMenu: false,
            selectedRatio: '{{ $aspectRatio }}',
            selectedModel: '{{ $modelId }}',
            ratios: [
                { id: 'auto', label: 'Tự động', w: 16, h: 16 },
                { id: '1:1', label: 'Vuông', w: 14, h: 14 },
                { id: '16:9', label: 'Ngang', w: 18, h: 10 },
                { id: '9:16', label: 'Dọc', w: 10, h: 18 },
                { id: '4:3', label: '4:3', w: 16, h: 12 },
                { id: '3:4', label: '3:4', w: 12, h: 16 },
                { id: '3:2', label: 'Photo', w: 18, h: 12 },
                { id: '21:9', label: 'Cinema', w: 21, h: 9 }
            ],
            getModelName() {
                const m = Object.values(@js($availableModels)).find(m => m.id === this.selectedModel);
                return m ? m.name : 'Model';
            },
            getShortModelName() {
                const name = this.getModelName();
                return name.length > 12 ? name.substring(0, 10) + '…' : name;
            }
        }" @click.away="showRatioMenu = false; showModelMenu = false">
            
            {{-- Aspect Ratio Selector --}}
            <div class="relative">
                <button type="button" @click="showRatioMenu = !showRatioMenu; showModelMenu = false"
                    class="flex items-center gap-2 h-9 sm:h-10 px-3 sm:px-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all text-sm"
                    :class="{ 'bg-purple-500/20 border-purple-500/40': showRatioMenu }">
                    <i class="fa-solid fa-crop text-purple-400 text-xs sm:text-sm"></i>
                    <span class="text-white font-medium" x-text="selectedRatio === 'auto' ? 'Tự động' : selectedRatio"></span>
                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                        :class="{ 'rotate-180': showRatioMenu }"></i>
                </button>
                
                {{-- Ratio Dropdown --}}
                <div x-show="showRatioMenu" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute top-full left-0 mt-2 p-2.5 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-[280px] max-h-[60vh] overflow-y-auto">
                    <div class="text-white/40 text-[10px] font-medium uppercase tracking-wider mb-2 px-1">Tỉ lệ khung hình</div>
                    <div class="grid grid-cols-4 gap-1.5">
                        <template x-for="ratio in ratios" :key="ratio.id">
                            <button type="button" 
                                @click="selectedRatio = ratio.id; $wire.set('aspectRatio', ratio.id); showRatioMenu = false"
                                class="flex flex-col items-center gap-1.5 p-2 rounded-lg transition-all text-center"
                                :class="selectedRatio === ratio.id ? 'bg-purple-500/30 border border-purple-500/50' : 'hover:bg-white/5 border border-transparent'">
                                <div class="w-6 h-6 flex items-center justify-center">
                                    <template x-if="ratio.id === 'auto'">
                                        <i class="fa-solid fa-expand text-white/50 text-sm"></i>
                                    </template>
                                    <template x-if="ratio.id !== 'auto'">
                                        <div class="border border-white/40 rounded-sm" :style="{
                                            width: ratio.w + 'px',
                                            height: ratio.h + 'px'
                                        }"></div>
                                    </template>
                                </div>
                                <span class="text-white/60 text-[10px] font-medium" x-text="ratio.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Model Selector --}}
            <div class="relative">
                <button type="button" @click="showModelMenu = !showModelMenu; showRatioMenu = false"
                    class="flex items-center gap-2 h-9 sm:h-10 px-3 sm:px-4 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all text-sm"
                    :class="{ 'bg-purple-500/20 border-purple-500/40': showModelMenu }">
                    <i class="fa-solid fa-microchip text-purple-400 text-xs sm:text-sm"></i>
                    <span class="text-white font-medium sm:hidden" x-text="getShortModelName()"></span>
                    <span class="text-white font-medium hidden sm:inline" x-text="getModelName()"></span>
                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                        :class="{ 'rotate-180': showModelMenu }"></i>
                </button>
                
                {{-- Model Dropdown --}}
                <div x-show="showModelMenu" x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    class="absolute top-full left-0 mt-2 p-2 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-50 w-72 max-h-[60vh] overflow-y-auto"
                    style="right: auto; left: 0;">
                    <div class="text-white/40 text-[10px] font-medium uppercase tracking-wider mb-2 px-2">Chọn Model AI</div>
                    <template x-for="model in Object.values(@js($availableModels))" :key="model.id">
                        <button type="button"
                            @click="selectedModel = model.id; $wire.set('modelId', model.id); showModelMenu = false"
                            class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all text-left"
                            :class="selectedModel === model.id ? 'bg-purple-500/30' : 'hover:bg-white/5'">
                            <i class="fa-solid fa-microchip text-purple-400/60"></i>
                            <div class="flex-1 min-w-0">
                                <div class="text-white text-sm font-medium truncate" x-text="model.name"></div>
                            </div>
                            <i x-show="selectedModel === model.id" class="fa-solid fa-check text-purple-400 text-sm"></i>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Reference Image Button --}}
            <button type="button" 
                @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                class="flex items-center gap-2 h-9 sm:h-10 px-3 sm:px-4 rounded-xl transition-all text-sm"
                :class="selectedImages.length > 0 
                    ? 'bg-purple-500/20 border border-purple-500/40' 
                    : 'bg-white/5 hover:bg-white/10 border border-white/10'">
                <template x-if="selectedImages.length > 0">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1.5">
                            <template x-for="(img, idx) in selectedImages.slice(0, 3)" :key="img.id">
                                <img :src="img.url" class="w-5 h-5 sm:w-6 sm:h-6 rounded border border-purple-500/50 object-cover">
                            </template>
                        </div>
                        <span class="text-purple-300 text-xs sm:text-sm font-medium" x-text="selectedImages.length + ' ảnh'"></span>
                    </div>
                </template>
                <template x-if="selectedImages.length === 0">
                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-image text-purple-400 text-xs sm:text-sm"></i>
                        <span class="text-white/70 text-xs sm:text-sm">Ảnh mẫu</span>
                    </div>
                </template>
            </button>

            {{-- Clear images button --}}
            <button x-show="selectedImages.length > 0" @click="clearAll()"
                class="h-9 sm:h-10 px-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-400 text-sm transition-all"
                aria-label="Xóa ảnh mẫu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- ===== GENERATE BUTTON (CTA) ===== --}}
        <div class="mb-8" id="generate-section">
            @if($isGenerating)
                <button type="button" wire:click="cancelGeneration"
                    class="w-full flex items-center justify-center gap-2.5 py-3 sm:py-3.5 rounded-xl bg-red-500/15 hover:bg-red-500/25 border border-red-500/25 text-red-400 font-semibold text-sm sm:text-base transition-all">
                    <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                    <span>Đang tạo ảnh...</span>
                    <span class="text-red-400/50 text-xs">(Nhấn để hủy)</span>
                </button>
            @else
                <button type="button" wire:click="generate"
                    class="w-full flex items-center justify-center gap-2.5 py-3 sm:py-3.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-bold text-base shadow-lg shadow-purple-500/20 hover:shadow-purple-500/35 hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-60"
                    wire:loading.attr="disabled"
                    wire:target="generate">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm" wire:loading.remove wire:target="generate"></i>
                    <i class="fa-solid fa-spinner fa-spin text-sm" wire:loading wire:target="generate"></i>
                    <span wire:loading.remove wire:target="generate">Tạo ảnh</span>
                    <span wire:loading wire:target="generate">Đang xử lý...</span>
                    <span class="px-2 py-0.5 rounded-full bg-white/20 text-xs font-medium">-{{ number_format($creditCost, 0) }} cr</span>
                </button>
            @endif
        </div>

        {{-- ===== GALLERY SECTION ===== --}}
        <div class="border-t border-white/10 pt-6" id="gallery-section">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-images text-purple-400 text-sm"></i>
                    Ảnh đã tạo
                </h2>
                <span class="text-white/40 text-xs sm:text-sm">{{ $history->total() ?? 0 }} ảnh</span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 sm:gap-3">
            {{-- Loading Skeleton --}}
            @if($isGenerating && !$generatedImageUrl)
                <div x-init="startLoadingMessages(); $nextTick(() => document.getElementById('gallery-section')?.scrollIntoView({behavior: 'smooth', block: 'center'}))" x-effect="if (!@js($isGenerating)) stopLoadingMessages()"
                    class="col-span-2 sm:col-span-1 aspect-square rounded-xl sm:rounded-2xl bg-[#1b1c21] border border-purple-500/30 overflow-hidden relative">
                    {{-- Shimmer Effect --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-purple-500/10 to-transparent animate-shimmer">
                    </div>

                    {{-- Glow Pulse --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-purple-600/20 via-transparent to-pink-600/20 animate-pulse">
                    </div>

                    {{-- Content --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-4">
                        {{-- Animated Spinner --}}
                        <div class="relative">
                            <div class="w-14 h-14 rounded-full border-[3px] border-purple-500/20"></div>
                            <div
                                class="absolute inset-0 w-14 h-14 rounded-full border-[3px] border-transparent border-t-purple-500 border-r-pink-500 animate-spin">
                            </div>
                            <div class="absolute inset-2 w-10 h-10 rounded-full border-2 border-transparent border-b-purple-400 animate-spin"
                                style="animation-direction: reverse; animation-duration: 1.5s;"></div>
                        </div>

                        {{-- Rotating Message --}}
                        <span class="text-sm text-white/50 font-medium transition-all duration-300"
                            x-text="loadingMessages[currentLoadingMessage]"></span>

                        {{-- Progress Dots --}}
                        <div class="flex gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-bounce"
                                style="animation-delay: 0ms;"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-bounce"
                                style="animation-delay: 150ms;"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-pink-500 animate-bounce"
                                style="animation-delay: 300ms;"></span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- History Items --}}
            @forelse($history as $index => $image)
                <div @click="openPreview(historyData[{{ $index }}], {{ $index }})"
                    class="group relative aspect-square rounded-xl sm:rounded-2xl bg-[#1b1c21] border border-white/5 overflow-hidden transition-all duration-300 hover:border-purple-500/30 hover:shadow-lg hover:shadow-purple-500/10 cursor-pointer active:scale-[0.98]">
                    <img src="{{ $image->image_url }}" alt="{{ Str::limit($image->final_prompt ?? 'AI Generated Image', 60) }}"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                        loading="lazy">

                    {{-- Desktop Hover Overlay --}}
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 hidden sm:flex items-center justify-center gap-2">
                        <button @click.stop="openPreview(historyData[{{ $index }}], {{ $index }})"
                            class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white text-white hover:text-black flex items-center justify-center transition-all hover:scale-110"
                            aria-label="Xem ảnh">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <a href="{{ $image->image_url }}" download @click.stop
                            class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm hover:bg-white text-white hover:text-black flex items-center justify-center transition-all hover:scale-110"
                            aria-label="Tải xuống">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>

                    {{-- Mobile Touch Indicator --}}
                    <div
                        class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-black/40 backdrop-blur-sm flex items-center justify-center sm:hidden">
                        <i class="fa-solid fa-expand text-white/60 text-[10px]"></i>
                    </div>

                    {{-- Prompt Info (Desktop only) --}}
                    <div
                        class="absolute inset-x-0 bottom-0 p-2.5 bg-gradient-to-t from-black/90 to-transparent pointer-events-none transform translate-y-1 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all delay-75 hidden sm:block">
                        <p class="text-[10px] text-white/80 line-clamp-1 italic font-light truncate">
                            "{{ $image->final_prompt }}"</p>
                    </div>
                </div>
            @empty
                @if(!$isGenerating)
                    <div class="col-span-full py-10 sm:py-16 text-center"
                        x-data="{ prompts: ['Một chú mèo dễ thương đang ngủ trên đám mây', 'Phong cảnh núi tuyết lúc hoàng hôn', 'Logo công nghệ với màu xanh gradient'] }">
                        {{-- Icon with glow --}}
                        <div class="relative w-16 h-16 sm:w-20 sm:h-20 mx-auto mb-5">
                            <div
                                class="absolute inset-0 rounded-2xl bg-gradient-to-br from-purple-500/25 to-pink-500/25 blur-xl animate-pulse">
                            </div>
                            <div
                                class="relative w-full h-full rounded-2xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-white/10 flex items-center justify-center">
                                <i class="fa-solid fa-wand-magic-sparkles text-purple-400 text-xl sm:text-2xl"></i>
                            </div>
                        </div>

                        <h3 class="text-white font-bold text-base sm:text-lg mb-1.5">Bắt đầu sáng tạo!</h3>
                        <p class="text-white/40 text-sm mb-5 max-w-sm mx-auto px-4">
                            Nhập mô tả ở ô prompt phía trên hoặc thử gợi ý:
                        </p>

                        {{-- Sample Prompts --}}
                        <div class="flex flex-wrap justify-center gap-2 max-w-lg mx-auto px-4 mb-5">
                            <template x-for="(prompt, i) in prompts" :key="i">
                                <button @click="$wire.set('prompt', prompt); document.querySelector('textarea').focus()"
                                    class="group px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-white/5 border border-white/10 text-white/50 text-xs hover:text-white hover:bg-purple-500/20 hover:border-purple-500/30 transition-all active:scale-95">
                                    <i class="fa-solid fa-sparkles text-purple-400/40 mr-1 text-[10px]"
                                        aria-hidden="true"></i>
                                    <span x-text="prompt.length > 35 ? prompt.substring(0, 32) + '...' : prompt"></span>
                                </button>
                            </template>
                        </div>

                        {{-- CTA Button --}}
                        <button type="button" onclick="document.querySelector('textarea').focus()"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm hover:shadow-lg hover:shadow-purple-500/30 active:scale-95 transition-all">
                            <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
                            Viết prompt của bạn
                        </button>
                    </div>
                @endif
            @endforelse
            </div>
        </div>

        {{-- Load More --}}
        @if(method_exists($history, 'hasMorePages') && $history->hasMorePages())
            <div class="mt-8 text-center">
                <button wire:click="loadMore"
                    class="px-8 py-3 rounded-xl bg-white/5 border border-white/10 text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all font-medium disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="loadMore">Tải thêm</span>
                    <span wire:loading wire:target="loadMore" class="flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin"></i> Đang tải...
                    </span>
                </button>
            </div>
        @endif
    </div>

    {{-- ========== IMAGE PICKER MODAL (Teleported - Desktop) ========== --}}
    <template x-teleport="body">
        <div x-show="showImagePicker" x-cloak x-init="$watch('showImagePicker', value => {
                if (value) {
                    document.documentElement.style.setProperty('overflow', 'hidden', 'important');
                    document.body.style.setProperty('overflow', 'hidden', 'important');
                } else {
                    document.documentElement.style.removeProperty('overflow');
                    document.body.style.removeProperty('overflow');
                }
            })" class="hidden sm:flex fixed inset-0 z-[100] items-center justify-center bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click.self="showImagePicker = false">

            <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                class="w-full max-w-4xl max-h-[90vh] mx-4 rounded-2xl bg-[#15161A] border border-white/10 shadow-2xl overflow-hidden flex flex-col"
                @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between p-5 border-b border-white/5 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                            <i class="fa-solid fa-images text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold text-lg">Chọn ảnh mẫu</h3>
                            <p class="text-white/50 text-sm">Tối đa <span x-text="maxImages"></span> ảnh tham chiếu</p>
                        </div>
                    </div>
                    <button type="button" @click="showImagePicker = false"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 text-white/60 hover:bg-white/10 hover:text-white transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Tabs with hover states --}}
                <div class="flex border-b border-white/5 px-5 shrink-0">
                    <button type="button" @click="activeTab = 'upload'"
                        class="py-3 px-4 text-sm font-medium transition-all duration-200 relative rounded-t-lg"
                        :class="activeTab === 'upload' ? 'text-purple-400 bg-purple-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5'">
                        <i class="fa-solid fa-upload mr-2" aria-hidden="true"></i>Upload
                        <div x-show="activeTab === 'upload'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></div>
                    </button>
                    <button type="button" @click="activeTab = 'url'"
                        class="py-3 px-4 text-sm font-medium transition-all duration-200 relative rounded-t-lg"
                        :class="activeTab === 'url' ? 'text-purple-400 bg-purple-500/10' : 'text-white/50 hover:text-white/70 hover:bg-white/5'">
                        <i class="fa-solid fa-link mr-2" aria-hidden="true"></i>Dán URL
                        <div x-show="activeTab === 'url'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                        </div>
                    </button>
                    <button type="button" @click="activeTab = 'recent'; loadRecentImages()"
                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'recent' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Thư viện
                        <div x-show="activeTab === 'recent'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></div>
                    </button>
                </div>

                {{-- Content Area --}}
                <div class="flex-1 flex flex-col overflow-hidden">
                    <div class="flex-1 p-4 overflow-y-auto">

                        {{-- Upload Tab --}}
                        <div x-show="activeTab === 'upload'" class="h-full flex flex-col">
                            <label
                                class="shrink-0 flex items-center gap-4 p-4 rounded-xl border border-dashed cursor-pointer transition-all group"
                                :class="isDragging ? 'border-purple-500 bg-purple-500/10' : 'border-white/20 hover:border-purple-500/50 bg-white/[0.02]'"
                                @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                                @drop.prevent="handleDrop($event)">
                                <input type="file" accept="image/*" multiple class="hidden"
                                    @change="handleFileSelect($event)">
                                <div
                                    class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-cloud-arrow-up text-xl text-purple-400"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-white font-medium text-sm">Kéo thả hoặc <span
                                            class="text-purple-400">chọn ảnh</span></p>
                                    <p class="text-white/40 text-xs">PNG, JPG, WebP • Tối đa 10MB • Chọn tối đa <span
                                            x-text="maxImages"></span> ảnh</p>
                                </div>
                            </label>

                            {{-- Selected Images Grid --}}
                            <div x-show="selectedImages.length > 0" class="mt-4 flex-1">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-white/60 text-sm">
                                        <i class="fa-solid fa-images text-purple-400 mr-1.5"></i>
                                        Đã chọn <span class="text-white font-medium"
                                            x-text="selectedImages.length"></span>/<span x-text="maxImages"></span>
                                    </span>
                                    <button type="button" @click="clearAll()"
                                        class="text-red-400/60 text-xs hover:text-red-400 transition-colors">
                                        Xóa tất cả
                                    </button>
                                </div>
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="(img, index) in selectedImages" :key="img.id">
                                        <div
                                            class="relative group rounded-xl overflow-hidden bg-black/40 border border-white/10 aspect-square">
                                            <img :src="img.url" class="w-full h-full object-contain">
                                            <div
                                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                <button type="button" @click="removeImage(img.id)"
                                                    class="w-9 h-9 rounded-full bg-red-500/80 hover:bg-red-500 text-white flex items-center justify-center transition-colors">
                                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                                </button>
                                            </div>
                                            <div class="absolute top-2 left-2 w-5 h-5 rounded-full bg-purple-500 text-white text-[10px] font-bold flex items-center justify-center"
                                                x-text="index + 1"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- URL Tab --}}
                        <div x-show="activeTab === 'url'" class="h-full flex flex-col">
                            <div class="flex gap-2">
                                <input type="text" x-model="urlInput" placeholder="Dán URL ảnh vào đây..."
                                    class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-purple-500/50">
                                <button type="button" @click="addFromUrl()"
                                    class="px-5 py-3 rounded-xl bg-purple-500 hover:bg-purple-600 text-white font-medium transition-colors">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            {{-- Selected from URLs --}}
                            <div x-show="selectedImages.length > 0" class="mt-4 flex-1">
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="(img, index) in selectedImages" :key="img.id">
                                        <div
                                            class="relative group rounded-xl overflow-hidden bg-black/40 border border-white/10 aspect-square">
                                            <img :src="img.url" class="w-full h-full object-contain">
                                            <button type="button" @click="removeImage(img.id)"
                                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Tab --}}
                        <div x-show="activeTab === 'recent'" class="h-full">
                            <template x-if="isLoadingPicker">
                                <div class="flex justify-center py-10">
                                    <i class="fa-solid fa-spinner fa-spin text-purple-400 text-2xl"></i>
                                </div>
                            </template>
                            <template x-if="!isLoadingPicker && recentImages.length > 0">
                                <div class="grid grid-cols-4 gap-2">
                                    <template x-for="img in recentImages" :key="img.id">
                                        <button type="button" @click="selectFromRecent(img.image_url || img.url)"
                                            class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative"
                                            :class="isSelected(img.image_url || img.url) ? 'border-purple-500' : 'border-transparent hover:border-white/20'">
                                            <img :src="img.image_url || img.url" class="w-full h-full object-cover">
                                            <div x-show="isSelected(img.image_url || img.url)"
                                                class="absolute inset-0 bg-purple-500/40 flex items-center justify-center">
                                                <i class="fa-solid fa-check text-white text-xl"></i>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!isLoadingPicker && recentImages.length === 0">
                                <div class="text-center py-10">
                                    <i class="fa-regular fa-image text-4xl text-white/10 mb-3"></i>
                                    <p class="text-white/40">Chưa có ảnh nào trong thư viện</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="p-4 border-t border-white/5 bg-[#15161A] flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-2 text-white/50 text-sm">
                            <template x-if="selectedImages.length > 0">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                                    <span x-text="selectedImages.length + ' ảnh đã chọn'"></span>
                                </span>
                            </template>
                            <template x-if="selectedImages.length === 0">
                                <span>Chưa chọn ảnh nào</span>
                            </template>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showImagePicker = false"
                                class="px-4 py-2 rounded-lg text-white/60 font-medium hover:bg-white/5 transition-colors text-sm">
                                Hủy
                            </button>
                            <button type="button" @click="confirmSelection()"
                                class="px-5 py-2 rounded-lg bg-purple-500 hover:bg-purple-600 text-white font-medium transition-colors text-sm disabled:opacity-40 disabled:cursor-not-allowed"
                                :disabled="selectedImages.length === 0">
                                <i class="fa-solid fa-check mr-1.5"></i>Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ========== IMAGE PICKER MODAL (Mobile Bottom Sheet) ========== --}}
    <template x-teleport="body">
        <div x-show="showImagePicker" x-cloak
            class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click.self="showImagePicker = false">
            <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]"
                @click.stop>

                {{-- Handle bar --}}
                <div class="flex justify-center pt-3 pb-1">
                    <div class="w-10 h-1 rounded-full bg-white/20"></div>
                </div>

                {{-- Header --}}
                <div class="flex items-center justify-between px-4 pb-3 border-b border-white/5 shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                            <i class="fa-solid fa-images text-purple-400 text-sm"></i>
                        </div>
                        <div>
                            <span class="text-white font-semibold text-base">Chọn ảnh mẫu</span>
                            <span class="text-white/40 text-xs ml-1"
                                x-text="'(' + selectedImages.length + '/' + maxImages + ')'"></span>
                        </div>
                    </div>
                    <button type="button" @click="showImagePicker = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 text-white/60 active:scale-95">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Mobile Tabs with Icons and background --}}
                <div class="flex border-b border-white/5 shrink-0">
                    <button type="button" @click="activeTab = 'upload'"
                        class="flex-1 py-3 text-sm font-medium transition-all duration-200 flex items-center justify-center gap-1.5"
                        :class="activeTab === 'upload' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                        <i class="fa-solid fa-upload text-xs" aria-hidden="true"></i>
                        Upload
                    </button>
                    <button type="button" @click="activeTab = 'url'"
                        class="flex-1 py-3 text-sm font-medium transition-all duration-200 flex items-center justify-center gap-1.5"
                        :class="activeTab === 'url' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                        <i class="fa-solid fa-link text-xs" aria-hidden="true"></i>
                        URL
                    </button>
                    <button type="button" @click="activeTab = 'recent'; loadRecentImages()"
                        class="flex-1 py-3 text-sm font-medium transition-all duration-200 flex items-center justify-center gap-1.5"
                        :class="activeTab === 'recent' ? 'text-purple-400 bg-purple-500/10 border-b-2 border-purple-500' : 'text-white/50 active:bg-white/5'">
                        <i class="fa-solid fa-clock-rotate-left text-xs" aria-hidden="true"></i>
                        Gần đây
                    </button>
                </div>

                {{-- Content --}}
                <div class="p-4 overflow-y-auto flex-1">
                    {{-- Upload Tab Mobile --}}
                    <div x-show="activeTab === 'upload'" class="grid grid-cols-2 gap-3">
                        <label
                            class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                            <input type="file" accept="image/*" multiple class="hidden"
                                @change="handleFileSelect($event)">
                            <i class="fa-solid fa-images text-3xl text-purple-400"></i>
                            <span class="text-white/70 text-sm font-medium">Thư viện</span>
                        </label>
                        <label
                            class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                            <input type="file" accept="image/*" capture="environment" class="hidden"
                                @change="handleFileSelect($event)">
                            <i class="fa-solid fa-camera text-3xl text-pink-400"></i>
                            <span class="text-white/70 text-sm font-medium">Camera</span>
                        </label>
                    </div>

                    {{-- URL Tab Mobile --}}
                    <div x-show="activeTab === 'url'">
                        <div class="flex gap-2">
                            <input type="text" x-model="urlInput" placeholder="Dán URL ảnh..."
                                class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-purple-500/50">
                            <button type="button" @click="addFromUrl()"
                                class="px-5 py-3 rounded-xl bg-purple-500 text-white font-medium active:scale-95 transition-transform">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Recent Tab Mobile --}}
                    <div x-show="activeTab === 'recent'">
                        {{-- Loading Spinner --}}
                        <template x-if="isLoadingPicker">
                            <div class="flex flex-col items-center justify-center py-10 gap-3">
                                <i class="fa-solid fa-spinner fa-spin text-purple-400 text-2xl"></i>
                                <span class="text-white/40 text-sm">Đang tải ảnh...</span>
                            </div>
                        </template>
                        <template x-if="!isLoadingPicker && recentImages.length > 0">
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="img in recentImages" :key="img.id">
                                    <button type="button" @click="selectFromRecent(img.image_url || img.url)"
                                        class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative active:scale-95"
                                        :class="isSelected(img.image_url || img.url) ? 'border-purple-500 ring-2 ring-purple-500/30' : 'border-transparent'">
                                        <img :src="img.image_url || img.url" class="w-full h-full object-cover">
                                        <div x-show="isSelected(img.image_url || img.url)"
                                            class="absolute inset-0 bg-purple-500/40 flex items-center justify-center">
                                            <i class="fa-solid fa-check text-white text-xl"></i>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="!isLoadingPicker && recentImages.length === 0">
                            <div class="text-center py-10 text-white/40">
                                <i class="fa-regular fa-image text-4xl mb-3 block"></i>
                                <p class="text-sm">Chưa có ảnh nào trong thư viện</p>
                            </div>
                        </template>
                    </div>

                    {{-- Selected Preview Mobile - Larger size --}}
                    <template x-if="selectedImages.length > 0">
                        <div class="mt-4 pt-4 border-t border-white/5">
                            <div class="text-white/50 text-xs font-medium mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-check-circle text-green-400" aria-hidden="true"></i>
                                Đã chọn <span class="text-white" x-text="selectedImages.length"></span>/<span
                                    x-text="maxImages"></span>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="(img, idx) in selectedImages" :key="img.id">
                                    <div class="relative group">
                                        <img :src="img.url"
                                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl object-cover border-2 border-purple-500/30">
                                        <div class="absolute top-1 left-1 w-5 h-5 rounded-full bg-purple-500 text-white text-[10px] font-bold flex items-center justify-center"
                                            x-text="idx + 1"></div>
                                        <button type="button" @click="removeImage(img.id)"
                                            class="absolute -top-1.5 -right-1.5 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center shadow-lg active:scale-90 transition-transform"
                                            aria-label="Xóa ảnh này">
                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer Mobile with better feedback --}}
                <div class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
                    <button type="button" @click="confirmSelection()"
                        class="w-full py-3.5 rounded-xl text-white font-bold text-center active:scale-[0.98] transition-all"
                        :disabled="selectedImages.length === 0" :class="selectedImages.length === 0 
                            ? 'bg-white/10 text-white/40 cursor-not-allowed' 
                            : 'bg-gradient-to-r from-purple-600 to-pink-600 shadow-lg shadow-purple-500/20'">
                        <template x-if="selectedImages.length === 0">
                            <span class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                Chọn ít nhất 1 ảnh
                            </span>
                        </template>
                        <template x-if="selectedImages.length > 0">
                            <span class="flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check" aria-hidden="true"></i>
                                Xác nhận (<span x-text="selectedImages.length"></span> ảnh)
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </template>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        /* Shimmer animation */
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

    {{-- ========== IMAGE PREVIEW MODAL ========== --}}
    <template x-teleport="body">
        {{-- Desktop Modal --}}
        <div x-show="showPreview" x-cloak
            class="hidden sm:flex fixed inset-0 z-[200] items-center justify-center bg-black/95 backdrop-blur-md"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="closePreview()"
            @keydown.escape.window="closePreview()">

            <div x-show="showPreview" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="relative max-w-4xl w-full mx-4" @click.stop>

                {{-- Close Button --}}
                <button @click="closePreview()"
                    class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/10 text-white/70 hover:text-white hover:bg-white/20 flex items-center justify-center transition-all">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>

                {{-- Navigation Arrows - Inside modal for visibility --}}
                <button x-show="previewIndex > 0" @click="prevImage()"
                    class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/60 hover:bg-black/80 text-white/80 hover:text-white flex items-center justify-center transition-all z-10 backdrop-blur-sm"
                    aria-label="Ảnh trước">
                    <i class="fa-solid fa-chevron-left text-lg" aria-hidden="true"></i>
                </button>
                <button x-show="previewIndex < historyData.length - 1" @click="nextImage()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/60 hover:bg-black/80 text-white/80 hover:text-white flex items-center justify-center transition-all z-10 backdrop-blur-sm"
                    aria-label="Ảnh sau">
                    <i class="fa-solid fa-chevron-right text-lg" aria-hidden="true"></i>
                </button>

                {{-- Image --}}
                <div class="rounded-2xl overflow-hidden bg-[#15161A] border border-white/10">
                    <img :src="previewImage?.url" alt="Preview" class="w-full max-h-[70vh] object-contain">

                    {{-- Info & Actions --}}
                    <div class="p-5 border-t border-white/5">
                        {{-- Expandable Prompt --}}
                        <div x-data="{ expanded: false }" class="mb-3">
                            <div class="flex items-start gap-2">
                                <i class="fa-solid fa-quote-left text-purple-400/50 text-sm mt-0.5 shrink-0"
                                    aria-hidden="true"></i>
                                <p class="text-white/70 text-sm italic flex-1" :class="expanded ? '' : 'line-clamp-2'"
                                    x-text="previewImage?.prompt || ''"></p>
                            </div>
                            <button x-show="(previewImage?.prompt || '').length > 150" @click="expanded = !expanded"
                                class="mt-2 text-purple-400 text-xs font-medium hover:text-purple-300 transition-colors flex items-center gap-1">
                                <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
                                    :class="expanded && 'rotate-180'" aria-hidden="true"></i>
                            </button>
                        </div>

                        {{-- Metadata --}}
                        <div class="flex flex-wrap items-center gap-3 mb-4 text-xs text-white/40">
                            <span x-show="previewImage?.model" class="flex items-center gap-1">
                                <i class="fa-solid fa-microchip" aria-hidden="true"></i>
                                <span x-text="previewImage?.model"></span>
                            </span>
                            <span x-show="previewImage?.ratio" class="flex items-center gap-1">
                                <i class="fa-solid fa-crop" aria-hidden="true"></i>
                                <span x-text="previewImage?.ratio"></span>
                            </span>
                            <span x-show="previewImage?.created_at" class="flex items-center gap-1">
                                <i class="fa-regular fa-clock" aria-hidden="true"></i>
                                <span x-text="previewImage?.created_at"></span>
                            </span>
                            <span class="flex items-center gap-1">
                                <i class="fa-solid fa-image" aria-hidden="true"></i>
                                <span x-text="(previewIndex + 1) + '/' + historyData.length"></span>
                            </span>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex flex-wrap gap-3">
                            <a :href="previewImage?.url" download
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-white/30"
                                aria-label="Tải ảnh xuống">
                                <i class="fa-solid fa-download" aria-hidden="true"></i>
                                Tải xuống
                            </a>
                            <button @click="shareImage()"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-white/30"
                                aria-label="Chia sẻ ảnh">
                                <i class="fa-solid fa-share-nodes" aria-hidden="true"></i>
                                Chia sẻ
                            </button>
                            <button @click="useAsReference()"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 hover:text-purple-200 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-purple-500/50"
                                aria-label="Dùng ảnh này làm mẫu">
                                <i class="fa-solid fa-images" aria-hidden="true"></i>
                                Dùng làm mẫu
                            </button>
                            <button @click="copyPrompt()"
                                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-white/30"
                                aria-label="Sao chép prompt">
                                <i class="fa-solid fa-copy" aria-hidden="true"></i>
                                Copy prompt
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Bottom Sheet --}}
        <div x-show="showPreview" x-cloak class="sm:hidden fixed inset-0 z-[200] flex flex-col bg-black/95"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            {{-- Header --}}
            <div
                class="shrink-0 flex items-center justify-between px-4 py-3 bg-[#0a0a0f]/80 backdrop-blur-sm border-b border-white/5 safe-area-top">
                <div class="flex items-center gap-2">
                    <span class="text-white font-semibold">Xem ảnh</span>
                    <span class="text-white/40 text-xs" x-text="(previewIndex + 1) + '/' + historyData.length"></span>
                </div>
                <button @click="closePreview()"
                    class="w-9 h-9 rounded-full bg-white/10 text-white/70 flex items-center justify-center active:scale-95 transition-transform">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Image Container with Swipe --}}
            <div class="flex-1 flex flex-col items-center justify-center p-4 overflow-hidden relative"
                @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">
                {{-- Navigation Buttons --}}
                <button x-show="previewIndex > 0" @click="prevImage()"
                    class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 text-white/80 flex items-center justify-center active:scale-95 z-10 backdrop-blur-sm"
                    aria-label="Ảnh trước">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <img :src="previewImage?.url" alt="Preview" class="max-w-full max-h-full object-contain rounded-xl">
                <button x-show="previewIndex < historyData.length - 1" @click="nextImage()"
                    class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 text-white/80 flex items-center justify-center active:scale-95 z-10 backdrop-blur-sm"
                    aria-label="Ảnh sau">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>

                {{-- Swipe Dot Indicators --}}
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-sm"
                    x-show="historyData.length > 1 && historyData.length <= 10">
                    <template x-for="(_, i) in historyData.slice(0, 10)" :key="i">
                        <button @click="goToImage(i)" class="w-2 h-2 rounded-full transition-all"
                            :class="previewIndex === i ? 'bg-white scale-125' : 'bg-white/40'"
                            :aria-label="'Chuyển đến ảnh ' + (i + 1)"></button>
                    </template>
                </div>
                {{-- Counter for many images --}}
                <div class="absolute bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-black/40 backdrop-blur-sm text-white/70 text-xs"
                    x-show="historyData.length > 10" x-text="(previewIndex + 1) + ' / ' + historyData.length">
                </div>
            </div>

            {{-- Expandable Prompt --}}
            <div class="px-4 py-3 bg-white/5" x-data="{ expanded: false }">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-quote-left text-purple-400/50 text-[10px] mt-1 shrink-0"
                        aria-hidden="true"></i>
                    <p class="text-white/60 text-xs italic flex-1" :class="expanded ? '' : 'line-clamp-2'"
                        x-text="previewImage?.prompt || ''"></p>
                </div>
                <button x-show="(previewImage?.prompt || '').length > 100" @click="expanded = !expanded"
                    class="mt-1 text-purple-400 text-[10px] font-medium hover:text-purple-300 transition-colors flex items-center gap-1">
                    <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                    <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                        :class="expanded && 'rotate-180'" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Action Buttons --}}
            <div class="shrink-0 grid grid-cols-4 gap-2 p-4 bg-[#0a0a0f] border-t border-white/5 safe-area-bottom">
                <a :href="previewImage?.url" download
                    class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                    <i class="fa-solid fa-download text-white/70"></i>
                    <span class="text-white/60 text-[10px] font-medium">Tải</span>
                </a>
                <button @click="shareImage()"
                    class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                    <i class="fa-solid fa-share-nodes text-white/70"></i>
                    <span class="text-white/60 text-[10px] font-medium">Chia sẻ</span>
                </button>
                <button @click="useAsReference()"
                    class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-purple-500/20 active:bg-purple-500/30 transition-colors">
                    <i class="fa-solid fa-images text-purple-400"></i>
                    <span class="text-purple-300 text-[10px] font-medium">Mẫu</span>
                </button>
                <button @click="copyPrompt()"
                    class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                    <i class="fa-solid fa-copy text-white/70"></i>
                    <span class="text-white/60 text-[10px] font-medium">Copy</span>
                </button>
            </div>
        </div>
    </template>
</div>