<div class="relative min-h-screen pb-40" x-data="{
    aspectRatios: @js($aspectRatios),
    models: @js($availableModels),
    showRatioDropdown: false,
    showModelDropdown: false,
    
    showImagePicker: false,
    selectedImages: [],
    maxImages: 4,
    recentImages: [],
    isLoadingPicker: false,
    urlInput: '',
    activeTab: 'upload',
    isDragging: false,
    
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
            alert('Tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (!file.type.startsWith('image/')) return;
        if (file.size > 10 * 1024 * 1024) {
            alert('Ảnh quá lớn (tối đa 10MB)');
            return;
        }
        const url = URL.createObjectURL(file);
        this.selectedImages.push({ type: 'file', file: file, url: url, id: Date.now() + Math.random() });
    },
    addFromUrl() {
        if (!this.urlInput.trim()) return;
        if (this.selectedImages.length >= this.maxImages) {
            alert('Tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (!this.urlInput.match(/^https?:\/\/.+/)) {
            alert('URL không hợp lệ');
            return;
        }
        this.selectedImages.push({ type: 'url', url: this.urlInput.trim(), id: Date.now() });
        this.urlInput = '';
    },
    selectFromRecent(imageUrl) {
        if (this.selectedImages.length >= this.maxImages) {
            alert('Tối đa ' + this.maxImages + ' ảnh');
            return;
        }
        if (this.selectedImages.find(img => img.url === imageUrl)) return;
        this.selectedImages.push({ type: 'url', url: imageUrl, id: Date.now() });
    },
    isSelected(imageUrl) {
        return this.selectedImages.find(img => img.url === imageUrl);
    },
    clearAll() {
        this.selectedImages = [];
    },
    confirmSelection() {
        this.showImagePicker = false;
    }
}" wire:poll.3s="pollImageStatus">

    {{-- Header Section --}}
    <div class="px-4 py-6">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <h1 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-500"></i>
                <span>Tạo ảnh AI</span>
            </h1>
            <div
                class="text-xs text-white/40 flex items-center gap-2 bg-white/5 px-3 py-1.5 rounded-full border border-white/10 uppercase tracking-widest font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                AI Studio
            </div>
        </div>
    </div>

    {{-- Gallery / Main Area --}}
    <div class="max-w-6xl mx-auto px-4">
        {{-- Status / Error --}}
        @if($errorMessage)
            <div
                class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $errorMessage }}
                <button @click="$wire.set('errorMessage', null)"
                    class="ml-auto opacity-50 hover:opacity-100 transition-opacity">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Loading State --}}
            @if($isGenerating && !$generatedImageUrl)
                <div
                    class="aspect-square rounded-2xl bg-[#1b1c21] border border-white/5 flex flex-col items-center justify-center gap-4 animate-pulse">
                    <div class="w-10 h-10 rounded-full border-2 border-purple-500/30 border-t-purple-500 animate-spin">
                    </div>
                    <span class="text-xs text-white/30 font-medium">Đang sáng tạo...</span>
                </div>
            @endif

            {{-- History Items --}}
            @forelse($history as $image)
                <div
                    class="group relative aspect-square rounded-2xl bg-[#1b1c21] border border-white/5 overflow-hidden transition-all duration-300 hover:border-purple-500/30 hover:shadow-2xl hover:shadow-purple-500/10">
                    <img src="{{ $image->image_url }}" alt="Created"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
                        loading="lazy">

                    {{-- Quick Action Overlay --}}
                    <div
                        class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-3 scale-95 group-hover:scale-100">
                        <button
                            class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white text-white hover:text-black flex items-center justify-center transition-all">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        <a href="{{ $image->image_url }}" download
                            class="w-10 h-10 rounded-xl bg-white/10 hover:bg-white text-white hover:text-black flex items-center justify-center transition-all">
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>

                    {{-- Prompt Info --}}
                    <div
                        class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/90 to-transparent pointer-events-none transform translate-y-1 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all delay-75">
                        <p class="text-[10px] text-white/90 line-clamp-1 italic font-light truncate">
                            "{{ $image->final_prompt }}"</p>
                    </div>
                </div>
            @empty
                @if(!$isGenerating)
                    <div class="col-span-full py-24 text-center">
                        <div
                            class="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center mx-auto mb-6 transform rotate-12 group-hover:rotate-0 transition-transform">
                            <i class="fa-solid fa-wand-magic-sparkles text-white/10 text-3xl"></i>
                        </div>
                        <h3 class="text-white/60 font-bold text-lg">Hệ thống sẵn sàng</h3>
                        <p class="text-white/30 text-sm mt-2 max-w-sm mx-auto">Nhập prompt bên dưới để bắt đầu hành trình sáng
                            tạo của bạn</p>
                    </div>
                @endif
            @endforelse
        </div>

        {{-- Load More --}}
        @if(method_exists($history, 'hasMorePages') && $history->hasMorePages())
            <div class="mt-12 text-center">
                <button wire:click="loadMore"
                    class="px-8 py-3 rounded-xl bg-white/5 border border-white/10 text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all font-medium">
                    Tải thêm lịch sử
                </button>
            </div>
        @endif
    </div>

    {{-- ========== FIXED PROMPT BAR (Exact Copy from Home) ========== --}}
    <div class="fixed bottom-20 md:bottom-6 left-0 right-0 md:left-[72px] z-40 px-4 safe-area-bottom">
        <div class="max-w-3xl mx-auto group/form">
            <div class="relative">
                {{-- Glow effect --}}
                <div
                    class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-20 blur-md sm:blur-lg group-hover/form:opacity-35 transition-opacity duration-500">
                </div>

                {{-- Input container --}}
                <div
                    class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/50 backdrop-blur-2xl border border-white/15 shadow-2xl">

                    {{-- Textarea --}}
                    <textarea wire:model="prompt" rows="3" placeholder="Mô tả ý tưởng của bạn..."
                        class="w-full h-20 bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/40 text-sm sm:text-base resize-none focus:placeholder-white/60 transition-all overflow-y-auto"
                        {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    {{-- Bottom row: icons + button --}}
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2" x-data="{ 
                            showLocalRatioDropdown: false,
                            selectedRatio: '{{ $aspectRatio }}',
                            customWidth: 1024,
                            customHeight: 1024,
                            linkDimensions: true,
                            ratios: [
                                { id: 'auto', label: 'Auto', icon: 'fa-expand' },
                                { id: '21:9', label: '21:9', icon: null },
                                { id: '16:9', label: '16:9', icon: null },
                                { id: '3:2', label: '3:2', icon: null },
                                { id: '4:3', label: '4:3', icon: null },
                                { id: '1:1', label: '1:1', icon: null },
                                { id: '3:4', label: '3:4', icon: null },
                                { id: '2:3', label: '2:3', icon: null },
                                { id: '9:16', label: '9:16', icon: null }
                            ],
                            selectRatio(id) {
                                this.selectedRatio = id;
                                $wire.set('aspectRatio', id);
                                if (id !== 'auto') {
                                    const [w, h] = id.split(':').map(Number);
                                    const baseSize = 1024;
                                    this.customWidth = Math.round(baseSize * Math.sqrt(w / h) / 64) * 64;
                                    this.customHeight = Math.round(baseSize * Math.sqrt(h / w) / 64) * 64;
                                }
                                if (window.innerWidth >= 640) {
                                    this.showLocalRatioDropdown = false;
                                }
                            }
                        }" @click.away="showLocalRatioDropdown = false">
                            {{-- Image Reference Picker --}}
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

                                {{-- Clear all button --}}
                                <button x-show="selectedImages.length > 0" @click.stop="clearAll()"
                                    class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            {{-- Aspect Ratio Button --}}
                            <div class="relative">
                                <button type="button" @click="showLocalRatioDropdown = !showLocalRatioDropdown"
                                    class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showLocalRatioDropdown }">
                                    <i class="fa-solid fa-crop text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium"
                                        x-text="selectedRatio === 'auto' ? 'Tỉ lệ' : selectedRatio"></span>
                                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                                        :class="{ 'rotate-180': showLocalRatioDropdown }"></i>
                                </button>

                                {{-- Dropdown Panel --}}
                                <div x-show="showLocalRatioDropdown" x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute bottom-full left-0 mb-2 w-72 p-3 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                    @click.stop>
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
                            </div>

                            {{-- Model Selector --}}
                            <div class="relative" x-data="{
                                showLocalModelDropdown: false,
                                selectedModel: '{{ $modelId }}',
                                models: @js($availableModels),
                                getSelectedModel() {
                                    return this.models.find(m => m.id === this.selectedModel) || this.models[0];
                                }
                            }" @click.away="showLocalModelDropdown = false">
                                <button type="button" @click="showLocalModelDropdown = !showLocalModelDropdown"
                                    class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
                                    :class="{ 'bg-purple-500/20 border-purple-500/40': showLocalModelDropdown }">
                                    <i class="fa-solid fa-microchip text-white/50 text-sm"></i>
                                    <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                        x-text="getSelectedModel()?.name || 'Model'"></span>
                                    <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                                        :class="{ 'rotate-180': showLocalModelDropdown }"></i>
                                </button>

                                {{-- Model Dropdown --}}
                                <div x-show="showLocalModelDropdown" x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute bottom-full left-0 mb-2 w-64 p-2 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                    @click.stop>
                                    <div class="text-white/50 text-xs font-medium mb-2 px-2">Chọn Model AI</div>
                                    <template x-for="model in models" :key="model.id">
                                        <button type="button"
                                            @click="selectedModel = model.id; $wire.set('modelId', model.id); showLocalModelDropdown = false"
                                            class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all text-left"
                                            :class="selectedModel === model.id ? 'bg-purple-500/30 border border-purple-500/50' : 'hover:bg-white/5 border border-transparent'">
                                            <span class="text-lg" x-text="model.icon || '🤖'"></span>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-white text-sm font-medium" x-text="model.name"></div>
                                                <div class="text-white/40 text-xs" x-text="model.desc || ''"></div>
                                            </div>
                                            <i x-show="selectedModel === model.id"
                                                class="fa-solid fa-check text-purple-400 text-sm"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Generate Button --}}
                        <button type="button" wire:click="generate" {{ $isGenerating ? 'disabled' : '' }}
                            class="flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/30 active:scale-[0.98] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                            @if($isGenerating)
                                <i class="fa-solid fa-spinner fa-spin text-sm"></i>
                                <span class="hidden sm:inline">Đang tạo...</span>
                            @else
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                                <span>Tạo ảnh</span>
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>
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
            })" class="hidden sm:flex fixed inset-0 z-[100] items-center justify-center backdrop-blur-sm"
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
                    <div>
                        <h3 class="text-white font-semibold text-lg">📸 Chọn ảnh mẫu</h3>
                        <p class="text-white/50 text-sm mt-0.5">Chọn tối đa <span x-text="maxImages"></span> ảnh làm
                            tham chiếu</p>
                    </div>
                    <button type="button" @click="showImagePicker = false"
                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 text-white/60 hover:bg-white/10 transition-colors">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-white/5 px-5 shrink-0">
                    <button type="button" @click="activeTab = 'upload'"
                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'upload' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                        <i class="fa-solid fa-upload mr-2"></i> Upload
                        <div x-show="activeTab === 'upload'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500"></div>
                    </button>
                    <button type="button" @click="activeTab = 'url'"
                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                        :class="activeTab === 'url' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                        <i class="fa-solid fa-link mr-2"></i> Dán URL
                        <div x-show="activeTab === 'url'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                        </div>
                    </button>
                    <button type="button" @click="activeTab = 'recent'"
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
            class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center backdrop-blur-sm"
            @click.self="showImagePicker = false">
            <div x-show="showImagePicker" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
                x-transition:leave-end="translate-y-full"
                class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]"
                @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                    <div>
                        <span class="text-white font-semibold text-base">📸 Chọn ảnh mẫu</span>
                        <span class="text-white/40 text-xs ml-2"
                            x-text="selectedImages.length + '/' + maxImages"></span>
                    </div>
                    <button type="button" @click="showImagePicker = false"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Mobile Tabs --}}
                <div class="flex border-b border-white/5 shrink-0">
                    <button type="button" @click="activeTab = 'upload'"
                        class="flex-1 py-3 text-sm font-medium transition-colors"
                        :class="activeTab === 'upload' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/50'">
                        Upload
                    </button>
                    <button type="button" @click="activeTab = 'url'"
                        class="flex-1 py-3 text-sm font-medium transition-colors"
                        :class="activeTab === 'url' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/50'">
                        URL
                    </button>
                    <button type="button" @click="activeTab = 'recent'"
                        class="flex-1 py-3 text-sm font-medium transition-colors"
                        :class="activeTab === 'recent' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/50'">
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
                        <template x-if="recentImages.length > 0">
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="img in recentImages" :key="img.id">
                                    <button type="button" @click="selectFromRecent(img.image_url || img.url)"
                                        class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative"
                                        :class="isSelected(img.image_url || img.url) ? 'border-purple-500' : 'border-transparent'">
                                        <img :src="img.image_url || img.url" class="w-full h-full object-cover">
                                        <div x-show="isSelected(img.image_url || img.url)"
                                            class="absolute inset-0 bg-purple-500/40 flex items-center justify-center">
                                            <i class="fa-solid fa-check text-white text-xl"></i>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </template>
                        <template x-if="recentImages.length === 0 && !isLoadingPicker">
                            <div class="text-center py-8 text-white/40">
                                <i class="fa-regular fa-image text-3xl mb-2"></i>
                                <p>Chưa có ảnh nào</p>
                            </div>
                        </template>
                    </div>

                    {{-- Selected Preview Mobile --}}
                    <template x-if="selectedImages.length > 0">
                        <div class="mt-4 pt-4 border-t border-white/5">
                            <div class="text-white/40 text-xs font-medium mb-2">Đã chọn:</div>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="img in selectedImages" :key="img.id">
                                    <div class="relative">
                                        <img :src="img.url"
                                            class="w-14 h-14 rounded-lg object-cover border border-white/20">
                                        <button type="button" @click="removeImage(img.id)"
                                            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Footer Mobile --}}
                <div class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
                    <button type="button" @click="confirmSelection()"
                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-center active:scale-[0.98] transition-transform"
                        :disabled="selectedImages.length === 0"
                        :class="selectedImages.length === 0 ? 'opacity-50' : ''">
                        Xác nhận <span
                            x-text="selectedImages.length > 0 ? '(' + selectedImages.length + ' ảnh)' : ''"></span>
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
    </style>
</div>