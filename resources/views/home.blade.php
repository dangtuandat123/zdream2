<x-app-layout>
    <x-slot name="title">ZDream - Biến Ảnh Thường Thành Tác Phẩm AI</x-slot>

    <style>
        .home-hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: radial-gradient(120% 140% at 0% 0%, rgba(216, 180, 254, 0.2) 0%, rgba(10, 10, 15, 0.92) 55%, rgba(10, 10, 15, 1) 100%);
            box-shadow: 0 26px 60px rgba(0, 0, 0, 0.45), inset 0 0 0 1px rgba(255, 255, 255, 0.08);
            -webkit-mask-image: linear-gradient(to bottom, black 0%, black 70%, transparent 100%);
            mask-image: linear-gradient(to bottom, black 0%, black 70%, transparent 100%);
        }

        .home-hero-grid {
            position: absolute;
            inset: -30%;
            background-image: url('/images/hero/home-grid.png');
            background-size: 1200px auto;
            background-repeat: repeat;
            opacity: 0.45;
            filter: saturate(1) contrast(1.02);
            animation: home-grid-scroll 120s linear infinite;
            will-change: background-position;
            pointer-events: none;
        }

        .home-hero-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            background:
                radial-gradient(65% 60% at 20% 15%, rgba(244, 114, 182, 0.18), transparent 60%),
                radial-gradient(60% 60% at 85% 20%, rgba(168, 85, 247, 0.16), transparent 60%),
                linear-gradient(180deg, rgba(10, 10, 15, 0.55) 0%, rgba(10, 10, 15, 0.3) 50%, transparent 100%);
            pointer-events: none;
        }

        .home-hero-panel {
            background: linear-gradient(180deg, rgba(10, 10, 15, 0.34), rgba(10, 10, 15, 0.16));
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(12px);
        }

        .hero-deck {
            position: relative;
            width: min(360px, 100%);
            height: 420px;
            margin-left: auto;
        }

        .hero-card {
            position: absolute;
            inset: 0;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            transform-origin: bottom right;
            animation: hero-card-float 8s ease-in-out infinite;
        }

        .hero-card:nth-child(1) {
            transform: translate(0, 0) rotate(-4deg) scale(0.98);
            animation-delay: 0s;
        }

        .hero-card:nth-child(2) {
            transform: translate(18px, -12px) rotate(1deg) scale(1);
            animation-delay: 1.2s;
        }

        .hero-card:nth-child(3) {
            transform: translate(38px, -28px) rotate(6deg) scale(1.02);
            animation-delay: 2.1s;
        }

        .hero-card:hover {
            transform: translate(38px, -32px) rotate(6deg) scale(1.04);
        }

        @keyframes hero-card-float {

            0%,
            100% {
                transform: translate(var(--x, 0), var(--y, 0)) rotate(var(--r, 0deg)) scale(var(--s, 1));
            }

            50% {
                transform: translate(calc(var(--x, 0) + 6px), calc(var(--y, 0) - 6px)) rotate(calc(var(--r, 0deg) + 1deg)) scale(var(--s, 1));
            }
        }

        .home-hero-content {
            position: relative;
            z-index: 2;
        }

        @keyframes home-grid-scroll {
            from {
                background-position: 0 0;
            }

            to {
                background-position: 1200px 600px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-hero-grid {
                animation: none;
            }
        }

        body.is-scrolling .home-hero-grid {
            animation-play-state: paused;
        }

        @media (max-width: 768px) {
            .home-hero-grid {
                animation-duration: 160s;
            }

            .home-hero-overlay {
                background:
                    radial-gradient(65% 60% at 20% 15%, rgba(244, 114, 182, 0.18), transparent 60%),
                    radial-gradient(60% 60% at 85% 20%, rgba(168, 85, 247, 0.16), transparent 60%),
                    linear-gradient(180deg, rgba(10, 10, 15, 0.55) 0%, rgba(10, 10, 15, 0.3) 50%, transparent 100%);
            }

            .home-hero-panel {
                background: linear-gradient(180deg, rgba(10, 10, 15, 0.15), rgba(10, 10, 15, 0.08));
                border: 1px solid rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(8px);
            }
        }

        /* Hide scrollbar */
        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }
    </style>

    <!-- ========== HERO SECTION ========== -->
    <section class="home-hero">
        <div class="home-hero-grid"></div>
        <div class="home-hero-overlay"></div>

        <!-- Hero Content -->
        <div
            class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 pt-10 pb-20 sm:pt-16 sm:pb-28 lg:pt-24 lg:pb-36 text-center">
            <!-- Title - ZDream Logo -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black mb-6 sm:mb-10 flex items-center justify-center gap-2 sm:gap-3 tracking-tight"
                style="font-family: 'Inter', sans-serif; letter-spacing: -0.02em;">
                <i
                    class="fa-solid fa-wand-magic-sparkles text-3xl sm:text-5xl lg:text-6xl text-purple-400 drop-shadow-[0_0_15px_rgba(192,132,252,0.4)]"></i>
                <span
                    class="bg-clip-text text-transparent bg-gradient-to-r from-white via-purple-300 to-pink-300 animate-text-shimmer bg-[length:200%_auto] drop-shadow-[0_0_10px_rgba(255,255,255,0.3)] pb-1">
                    ZDream
                </span>
            </h1>

            <!-- Prompt Input Bar - Enhanced -->
            <form action="{{ route('styles.index') }}" method="GET"
                class="w-full max-w-2xl mx-auto mb-6 sm:mb-8 group/form relative z-50" x-data="{
                    showImagePicker: false,
                    selectedImages: [],
                    maxImages: 4,
                    isDragging: false,
                    recentImages: [],
                    isLoading: false,
                    urlInput: '',
                    activeTab: 'upload',
                    
                    async loadRecentImages() {
                        if (this.recentImages.length > 0) return;
                        this.isLoading = true;
                        try {
                            const response = await fetch('/api/user/recent-images');
                            if (response.ok) {
                                const data = await response.json();
                                this.recentImages = data.images || [];
                            }
                        } catch (e) {
                            console.log('Could not load recent images');
                        }
                        this.isLoading = false;
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
                    
                    removeImage(id) {
                        this.selectedImages = this.selectedImages.filter(img => img.id !== id);
                    },
                    
                    clearAll() {
                        this.selectedImages = [];
                    },
                    
                    confirmSelection() {
                        this.showImagePicker = false;
                    }
                }">
                <div class="relative">
                    <!-- Glow effect -->
                    <div
                        class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-20 blur-md sm:blur-lg group-hover/form:opacity-35 transition-opacity duration-500">
                    </div>

                    <!-- Input container -->
                    <div
                        class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/50 backdrop-blur-2xl border border-white/15 shadow-2xl">

                        <!-- Selected Images Preview (shown above textarea) -->
                        <template x-if="selectedImages.length > 0">
                            <div class="flex flex-wrap gap-2 pb-2 border-b border-white/10">
                                <template x-for="(img, idx) in selectedImages" :key="img.id">
                                    <div class="relative group">
                                        <img :src="img.url"
                                            class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover border border-white/20 shadow-lg">
                                        <button type="button" @click="removeImage(img.id)"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-red-500/90 text-white text-[10px] flex items-center justify-center opacity-0 group-hover:opacity-100 sm:opacity-100 transition-opacity hover:bg-red-600 shadow-md">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                        <div class="absolute bottom-0.5 right-0.5 w-4 h-4 rounded-full bg-purple-500 text-white text-[9px] flex items-center justify-center font-bold"
                                            x-text="idx + 1"></div>
                                    </div>
                                </template>
                                <!-- Add more button if not at max -->
                                <template x-if="selectedImages.length < maxImages">
                                    <button type="button" @click="showImagePicker = true; loadRecentImages()"
                                        class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl border-2 border-dashed border-white/20 flex items-center justify-center text-white/40 hover:border-purple-500/50 hover:text-purple-400 hover:bg-purple-500/5 transition-all">
                                        <i class="fa-solid fa-plus text-sm"></i>
                                    </button>
                                </template>
                            </div>
                        </template>

                        <!-- Textarea - Fixed height with scroll -->
                        <textarea name="prompt" rows="3" placeholder="Mô tả ý tưởng của bạn..."
                            class="w-full h-20 bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/40 text-sm sm:text-base resize-none focus:placeholder-white/60 transition-all overflow-y-auto"></textarea>

                        <!-- Bottom row: icons + button -->
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2" x-data="{ 
                                showRatioDropdown: false,
                                selectedRatio: 'auto',
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
                                    if (id !== 'auto') {
                                        const [w, h] = id.split(':').map(Number);
                                        const baseSize = 1024;
                                        this.customWidth = Math.round(baseSize * Math.sqrt(w / h) / 64) * 64;
                                        this.customHeight = Math.round(baseSize * Math.sqrt(h / w) / 64) * 64;
                                    }
                                    if (window.innerWidth >= 640) { // On desktop, close immediately
                                        this.showRatioDropdown = false;
                                    }
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
                            }" @click.away="showRatioDropdown = false">
                                <!-- Image Reference Picker (Multi-Select + URL) -->
                                <div class="relative">
                                    <!-- Image Button with Count Badge -->
                                    <button type="button"
                                        @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                                        class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg transition-all cursor-pointer"
                                        :class="selectedImages.length > 0 
                                            ? 'bg-purple-500/30 border border-purple-500/50' 
                                            : 'bg-gradient-to-br from-purple-500/20 to-pink-500/20 hover:from-purple-500/30 hover:to-pink-500/30 border border-purple-500/30'">
                                        <!-- Show thumbnails if images selected -->
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

                                    <!-- Clear all button -->
                                    <button x-show="selectedImages.length > 0" @click.stop="clearAll()"
                                        class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>

                                    <!-- Desktop Full Modal (teleported) -->
                                    <template x-teleport="body">
                                        <div x-show="showImagePicker" x-cloak x-init="$watch('showImagePicker', value => {
                                                if (value) {
                                                    document.documentElement.style.setProperty('overflow', 'hidden', 'important');
                                                    document.body.style.setProperty('overflow', 'hidden', 'important');
                                                } else {
                                                    document.documentElement.style.removeProperty('overflow');
                                                    document.body.style.removeProperty('overflow');
                                                }
                                            })"
                                            class="hidden sm:flex fixed inset-0 z-[100] items-center justify-center backdrop-blur-sm"
                                            x-transition:enter="transition ease-out duration-300"
                                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition ease-in duration-200"
                                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                            @click.self="showImagePicker = false">

                                            <div x-show="showImagePicker"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                                                class="w-full max-w-4xl max-h-[90vh] mx-4 rounded-2xl bg-[#15161A] border border-white/10 shadow-2xl overflow-hidden flex flex-col"
                                                @click.stop>

                                                <!-- Header -->
                                                <div
                                                    class="flex items-center justify-between p-5 border-b border-white/5 shrink-0">
                                                    <div>
                                                        <h3 class="text-white font-semibold text-lg">📸 Chọn ảnh mẫu
                                                        </h3>
                                                        <p class="text-white/50 text-sm mt-0.5">Chọn tối đa <span
                                                                x-text="maxImages"></span> ảnh làm tham chiếu</p>
                                                    </div>
                                                    <button type="button" @click="showImagePicker = false"
                                                        class="w-10 h-10 flex items-center justify-center rounded-full bg-white/5 text-white/60 hover:bg-white/10 transition-colors">
                                                        <i class="fa-solid fa-xmark text-lg"></i>
                                                    </button>
                                                </div>

                                                <!-- Tabs -->
                                                <div class="flex border-b border-white/5 px-5 shrink-0">
                                                    <button type="button" @click="activeTab = 'upload'"
                                                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                                                        :class="activeTab === 'upload' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                                                        <i class="fa-solid fa-upload mr-2"></i> Upload
                                                        <div x-show="activeTab === 'upload'"
                                                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                                                        </div>
                                                    </button>
                                                    <button type="button" @click="activeTab = 'url'"
                                                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                                                        :class="activeTab === 'url' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                                                        <i class="fa-solid fa-link mr-2"></i> Dán URL
                                                        <div x-show="activeTab === 'url'"
                                                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                                                        </div>
                                                    </button>
                                                    <button type="button" @click="activeTab = 'recent'"
                                                        class="py-3 px-4 text-sm font-medium transition-colors relative"
                                                        :class="activeTab === 'recent' ? 'text-purple-400' : 'text-white/50 hover:text-white/70'">
                                                        <i class="fa-solid fa-clock-rotate-left mr-2"></i> Thư viện
                                                        <div x-show="activeTab === 'recent'"
                                                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-purple-500">
                                                        </div>
                                                    </button>
                                                </div>

                                                <!-- Content Area -->
                                                <div class="flex-1 flex flex-col overflow-hidden">

                                                    <!-- Tab Content -->
                                                    <div class="flex-1 p-4 overflow-y-auto">

                                                        <!-- Upload Tab -->
                                                        <div x-show="activeTab === 'upload'"
                                                            class="h-full flex flex-col">
                                                            <!-- Compact Upload Zone -->
                                                            <label
                                                                class="shrink-0 flex items-center gap-4 p-4 rounded-xl border border-dashed cursor-pointer transition-all group"
                                                                :class="isDragging ? 'border-purple-500 bg-purple-500/10' : 'border-white/20 hover:border-purple-500/50 bg-white/[0.02]'"
                                                                @dragover.prevent="isDragging = true"
                                                                @dragleave.prevent="isDragging = false"
                                                                @drop.prevent="handleDrop($event)">
                                                                <input type="file" accept="image/*" multiple
                                                                    class="hidden" @change="handleFileSelect($event)">
                                                                <div
                                                                    class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center shrink-0">
                                                                    <i
                                                                        class="fa-solid fa-cloud-arrow-up text-xl text-purple-400"></i>
                                                                </div>
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-white font-medium text-sm">Kéo thả
                                                                        hoặc <span class="text-purple-400">chọn
                                                                            ảnh</span></p>
                                                                    <p class="text-white/40 text-xs">PNG, JPG, WebP •
                                                                        Tối đa 10MB • Chọn tối đa <span
                                                                            x-text="maxImages"></span> ảnh</p>
                                                                </div>
                                                            </label>

                                                            <!-- Selected Images Grid (Dynamic) -->
                                                            <div x-show="selectedImages.length > 0" class="mt-4 flex-1">
                                                                <div class="flex items-center justify-between mb-3">
                                                                    <span class="text-white/60 text-sm">
                                                                        <i
                                                                            class="fa-solid fa-images text-purple-400 mr-1.5"></i>
                                                                        Đã chọn <span class="text-white font-medium"
                                                                            x-text="selectedImages.length"></span>/<span
                                                                            x-text="maxImages"></span>
                                                                    </span>
                                                                    <button type="button" @click="clearAll()"
                                                                        class="text-red-400/60 text-xs hover:text-red-400 transition-colors">
                                                                        Xóa tất cả
                                                                    </button>
                                                                </div>

                                                                <!-- 4 images in 1 row -->
                                                                <div class="grid grid-cols-4 gap-2">
                                                                    <template x-for="(img, index) in selectedImages"
                                                                        :key="img.id">
                                                                        <div
                                                                            class="relative group rounded-xl overflow-hidden bg-black/40 border border-white/10 aspect-square">
                                                                            <img :src="img.url"
                                                                                class="w-full h-full object-contain">
                                                                            <!-- Hover Actions -->
                                                                            <div
                                                                                class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                                                <button type="button"
                                                                                    @click="removeImage(img.id)"
                                                                                    class="w-9 h-9 rounded-full bg-red-500/80 hover:bg-red-500 text-white flex items-center justify-center transition-colors">
                                                                                    <i
                                                                                        class="fa-solid fa-trash-can text-sm"></i>
                                                                                </button>
                                                                            </div>
                                                                            <!-- Index -->
                                                                            <div class="absolute top-2 left-2 w-5 h-5 rounded-full bg-purple-500 text-white text-[10px] font-bold flex items-center justify-center"
                                                                                x-text="index + 1"></div>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>

                                                            <!-- Empty State -->
                                                            <div x-show="selectedImages.length === 0"
                                                                class="flex-1 flex items-center justify-center">
                                                                <div class="text-center py-8">
                                                                    <div
                                                                        class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-3">
                                                                        <i
                                                                            class="fa-regular fa-images text-2xl text-white/20"></i>
                                                                    </div>
                                                                    <p class="text-white/40 text-sm">Chưa có ảnh nào
                                                                        được chọn</p>
                                                                    <p class="text-white/25 text-xs mt-1">Tải ảnh lên
                                                                        hoặc chọn từ thư viện</p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- URL Tab -->
                                                        <div x-show="activeTab === 'url'" class="space-y-4">
                                                            <div class="flex gap-2">
                                                                <input type="text" x-model="urlInput"
                                                                    placeholder="Dán URL ảnh vào đây..."
                                                                    @keydown.enter.prevent="addFromUrl()"
                                                                    class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/30 focus:outline-none focus:border-purple-500/50 text-sm">
                                                                <button type="button" @click="addFromUrl()"
                                                                    class="px-5 py-3 rounded-xl bg-purple-500 text-white font-medium hover:bg-purple-600 transition-colors text-sm shrink-0">
                                                                    <i class="fa-solid fa-plus mr-1"></i> Thêm
                                                                </button>
                                                            </div>

                                                            <!-- Show selected images in URL tab too -->
                                                            <div x-show="selectedImages.length > 0"
                                                                class="grid grid-cols-4 gap-2 mt-4">
                                                                <template x-for="(img, index) in selectedImages"
                                                                    :key="img.id">
                                                                    <div
                                                                        class="relative group aspect-square rounded-lg overflow-hidden border border-white/10">
                                                                        <img :src="img.url"
                                                                            class="w-full h-full object-cover">
                                                                        <button type="button"
                                                                            @click="removeImage(img.id)"
                                                                            class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                                            <i class="fa-solid fa-xmark text-white"></i>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>

                                                        <!-- Library Tab -->
                                                        <div x-show="activeTab === 'recent'" class="h-full">
                                                            <template x-if="recentImages.length > 0">
                                                                <div class="grid grid-cols-4 gap-2">
                                                                    <template x-for="img in recentImages" :key="img.id">
                                                                        <button type="button"
                                                                            @click="selectFromRecent(img.url)"
                                                                            class="aspect-square rounded-lg overflow-hidden border-2 transition-all relative group"
                                                                            :class="isSelected(img.url) ? 'border-purple-500 ring-2 ring-purple-500/30' : 'border-transparent hover:border-white/20'">
                                                                            <img :src="img.url"
                                                                                class="w-full h-full object-cover">
                                                                            <div x-show="!isSelected(img.url)"
                                                                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                                                <i
                                                                                    class="fa-solid fa-plus text-white"></i>
                                                                            </div>
                                                                            <div x-show="isSelected(img.url)"
                                                                                class="absolute inset-0 bg-purple-500/30 flex items-center justify-center">
                                                                                <div
                                                                                    class="w-6 h-6 rounded-full bg-purple-500 flex items-center justify-center">
                                                                                    <i
                                                                                        class="fa-solid fa-check text-white text-xs"></i>
                                                                                </div>
                                                                            </div>
                                                                        </button>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                            <template x-if="recentImages.length === 0 && !isLoading">
                                                                <div class="h-full flex items-center justify-center">
                                                                    <div class="text-center py-8">
                                                                        <i
                                                                            class="fa-regular fa-image text-2xl text-white/20 mb-2"></i>
                                                                        <p class="text-white/40 text-sm">Chưa có ảnh
                                                                            trong thư viện</p>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                            <template x-if="isLoading">
                                                                <div class="h-full flex items-center justify-center">
                                                                    <i
                                                                        class="fa-solid fa-spinner fa-spin text-xl text-purple-400"></i>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Footer (Always visible, not overlapping) -->
                                                    <div
                                                        class="shrink-0 p-4 border-t border-white/5 bg-black/30 flex items-center justify-between gap-4">
                                                        <!-- Left: Selection info -->
                                                        <div class="flex items-center gap-2 text-white/50 text-sm">
                                                            <template x-if="selectedImages.length > 0">
                                                                <span class="flex items-center gap-1.5">
                                                                    <span
                                                                        class="w-2 h-2 rounded-full bg-green-400"></span>
                                                                    <span
                                                                        x-text="selectedImages.length + ' ảnh đã chọn'"></span>
                                                                </span>
                                                            </template>
                                                            <template x-if="selectedImages.length === 0">
                                                                <span>Chưa chọn ảnh nào</span>
                                                            </template>
                                                        </div>

                                                        <!-- Right: Actions -->
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
                                    </template>

                                    <!-- Mobile Bottom Sheet (teleported) -->
                                    <template x-teleport="body">
                                        <div x-show="showImagePicker" x-cloak
                                            class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center backdrop-blur-sm"
                                            @click.self="showImagePicker = false">
                                            <div x-show="showImagePicker"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="translate-y-full"
                                                x-transition:enter-end="translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="translate-y-0"
                                                x-transition:leave-end="translate-y-full"
                                                class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh]"
                                                @click.stop>

                                                <!-- Header -->
                                                <div
                                                    class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                                    <div>
                                                        <span class="text-white font-semibold text-base">📸 Chọn ảnh
                                                            mẫu</span>
                                                        <span class="text-white/40 text-xs ml-2"
                                                            x-text="selectedImages.length + '/' + maxImages"></span>
                                                    </div>
                                                    <button type="button" @click="showImagePicker = false"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>

                                                <!-- Mobile Tabs -->
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

                                                <!-- Content -->
                                                <div class="p-4 overflow-y-auto flex-1">
                                                    <!-- Upload Tab Mobile -->
                                                    <div x-show="activeTab === 'upload'" class="grid grid-cols-2 gap-3">
                                                        <label
                                                            class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                                                            <input type="file" accept="image/*" multiple class="hidden"
                                                                @change="handleFileSelect($event)">
                                                            <i class="fa-solid fa-images text-3xl text-purple-400"></i>
                                                            <span class="text-white/70 text-sm font-medium">Thư
                                                                viện</span>
                                                        </label>
                                                        <label
                                                            class="flex flex-col items-center gap-2 p-6 rounded-xl bg-white/5 border border-white/10 cursor-pointer active:scale-95 transition-transform">
                                                            <input type="file" accept="image/*" capture="environment"
                                                                class="hidden" @change="handleFileSelect($event)">
                                                            <i class="fa-solid fa-camera text-3xl text-pink-400"></i>
                                                            <span
                                                                class="text-white/70 text-sm font-medium">Camera</span>
                                                        </label>
                                                    </div>

                                                    <!-- URL Tab Mobile -->
                                                    <div x-show="activeTab === 'url'">
                                                        <div class="flex gap-2">
                                                            <input type="text" x-model="urlInput"
                                                                placeholder="Dán URL ảnh..."
                                                                class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm placeholder-white/30 focus:outline-none focus:border-purple-500/50">
                                                            <button type="button" @click="addFromUrl()"
                                                                class="px-5 py-3 rounded-xl bg-purple-500 text-white font-medium active:scale-95 transition-transform">
                                                                <i class="fa-solid fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <!-- Recent Tab Mobile -->
                                                    <div x-show="activeTab === 'recent'">
                                                        <template x-if="recentImages.length > 0">
                                                            <div class="grid grid-cols-3 gap-2">
                                                                <template x-for="img in recentImages" :key="img.id">
                                                                    <button type="button"
                                                                        @click="selectFromRecent(img.url)"
                                                                        class="aspect-square rounded-xl overflow-hidden border-2 transition-all relative"
                                                                        :class="isSelected(img.url) ? 'border-purple-500' : 'border-transparent'">
                                                                        <img :src="img.url"
                                                                            class="w-full h-full object-cover">
                                                                        <div x-show="isSelected(img.url)"
                                                                            class="absolute inset-0 bg-purple-500/40 flex items-center justify-center">
                                                                            <i
                                                                                class="fa-solid fa-check text-white text-xl"></i>
                                                                        </div>
                                                                    </button>
                                                                </template>
                                                            </div>
                                                        </template>
                                                        <template x-if="recentImages.length === 0 && !isLoading">
                                                            <div class="text-center py-8 text-white/40">
                                                                <i class="fa-regular fa-image text-3xl mb-2"></i>
                                                                <p>Chưa có ảnh nào</p>
                                                            </div>
                                                        </template>
                                                    </div>

                                                    <!-- Selected Preview Mobile -->
                                                    <template x-if="selectedImages.length > 0">
                                                        <div class="mt-4 pt-4 border-t border-white/5">
                                                            <div class="text-white/40 text-xs font-medium mb-2">Đã chọn:
                                                            </div>
                                                            <div class="flex flex-wrap gap-2">
                                                                <template x-for="img in selectedImages" :key="img.id">
                                                                    <div class="relative">
                                                                        <img :src="img.url"
                                                                            class="w-14 h-14 rounded-lg object-cover border border-white/20">
                                                                        <button type="button"
                                                                            @click="removeImage(img.id)"
                                                                            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center">
                                                                            <i class="fa-solid fa-xmark"></i>
                                                                        </button>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>

                                                <!-- Footer Mobile -->
                                                <div
                                                    class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
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

                                    <!-- Hidden inputs for form submission -->
                                    <template x-for="(img, index) in selectedImages" :key="img.id">
                                        <input type="hidden" :name="'reference_images[' + index + ']'" :value="img.url">
                                    </template>
                                </div>

                                <!-- Aspect Ratio Button -->
                                <div class="relative">
                                    <button type="button" @click="showRatioDropdown = !showRatioDropdown"
                                        class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
                                        :class="{ 'bg-purple-500/20 border-purple-500/40': showRatioDropdown }">
                                        <i class="fa-solid fa-crop text-white/50 text-sm"></i>
                                        <span class="text-white/70 text-xs font-medium"
                                            x-text="selectedRatio === 'auto' ? 'Tỉ lệ' : selectedRatio"></span>
                                        <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                                            :class="{ 'rotate-180': showRatioDropdown }"></i>
                                    </button>

                                    <!-- Dropdown Panel - Desktop (teleported to body to escape hero mask) -->
                                    <template x-teleport="body">
                                        <div x-show="showRatioDropdown" x-cloak
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 -translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 -translate-y-2"
                                            class="hidden sm:block fixed w-80 p-3 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                            x-init="$watch('showRatioDropdown', value => {
                                                if (value) {
                                                    const btn = $root.querySelector('button');
                                                    const rect = btn.getBoundingClientRect();
                                                    $el.style.top = (rect.bottom + 8) + 'px';
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

                                            <!-- Size Section -->
                                            <div class="mt-3 pt-3 border-t border-white/10">
                                                <div class="text-white/50 text-xs font-medium mb-2">Kích thước</div>
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="flex-1 flex items-center gap-1.5 px-2.5 py-2 rounded-lg bg-white/5 border border-white/10">
                                                        <span class="text-white/40 text-xs font-medium">W</span>
                                                        <input type="number" name="width" x-model="customWidth"
                                                            @input="updateWidth($event.target.value)"
                                                            class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white text-sm font-medium text-center"
                                                            placeholder="1024" min="512" max="4096" step="64">
                                                    </div>
                                                    <button type="button" @click="linkDimensions = !linkDimensions"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg transition-all"
                                                        :class="linkDimensions ? 'bg-purple-500/30 text-purple-400' : 'bg-white/5 text-white/40 hover:bg-white/10'">
                                                        <i class="fa-solid fa-link text-xs"></i>
                                                    </button>
                                                    <div
                                                        class="flex-1 flex items-center gap-1.5 px-2.5 py-2 rounded-lg bg-white/5 border border-white/10">
                                                        <span class="text-white/40 text-xs font-medium">H</span>
                                                        <input type="number" name="height" x-model="customHeight"
                                                            @input="updateHeight($event.target.value)"
                                                            class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white text-sm font-medium text-center"
                                                            placeholder="1024" min="512" max="4096" step="64">
                                                    </div>
                                                    <span class="text-white/40 text-xs font-medium">PX</span>
                                                </div>
                                            </div>

                                        </div>
                                    </template>

                                    <!-- Bottom Sheet - Mobile -->
                                    <template x-teleport="body">
                                        <div x-show="showRatioDropdown" x-cloak
                                            class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                            style="z-index: 9999;" @click.self="showRatioDropdown = false" @click.stop>
                                            <div x-show="showRatioDropdown"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="translate-y-full"
                                                x-transition:enter-end="translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="translate-y-0"
                                                x-transition:leave-end="translate-y-full"
                                                class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">

                                                <!-- Header -->
                                                <div
                                                    class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                                    <span class="text-white font-semibold text-base">Tùy chỉnh khung
                                                        hình</span>
                                                    <button type="button" @click="showRatioDropdown = false"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>

                                                <!-- Scrollable Content -->
                                                <div class="p-4 overflow-y-auto overscroll-contain">
                                                    <div class="text-white/50 text-sm font-medium mb-3">Tỉ lệ</div>
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

                                                    <!-- Size Section - Mobile -->
                                                    <div class="pt-4 border-t border-white/10">
                                                        <div class="text-white/50 text-sm font-medium mb-3">Kích thước
                                                            tùy
                                                            chỉnh (PX)</div>
                                                        <div class="flex items-center gap-3">
                                                            <div
                                                                class="flex-1 flex items-center gap-2 px-3 py-3 rounded-xl bg-white/5 border border-white/10 focus-within:border-purple-500/50 transition-colors">
                                                                <span
                                                                    class="text-white/40 text-sm font-semibold">W</span>
                                                                <input type="number" x-model="customWidth"
                                                                    @input="updateWidth($event.target.value)"
                                                                    class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white text-lg font-medium text-center"
                                                                    placeholder="1024" min="512" max="4096" step="64">
                                                            </div>
                                                            <button type="button"
                                                                @click="linkDimensions = !linkDimensions"
                                                                class="w-12 h-12 flex items-center justify-center rounded-xl transition-all shrink-0"
                                                                :class="linkDimensions ? 'bg-purple-500/30 text-purple-400' : 'bg-white/5 text-white/40'">
                                                                <i class="fa-solid fa-link text-lg"></i>
                                                            </button>
                                                            <div
                                                                class="flex-1 flex items-center gap-2 px-3 py-3 rounded-xl bg-white/5 border border-white/10 focus-within:border-purple-500/50 transition-colors">
                                                                <span
                                                                    class="text-white/40 text-sm font-semibold">H</span>
                                                                <input type="number" x-model="customHeight"
                                                                    @input="updateHeight($event.target.value)"
                                                                    class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white text-lg font-medium text-center"
                                                                    placeholder="1024" min="512" max="4096" step="64">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Footer Action -->
                                                <div
                                                    class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
                                                    <button type="button" @click="showRatioDropdown = false"
                                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-center active:scale-[0.98] transition-transform shadow-lg shadow-purple-900/20">
                                                        Áp dụng & Đóng
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>


                                <!-- Model Selector -->
                                <div class="relative" x-data="{
                                    showModelDropdown: false,
                                    selectedModel: 'flux-pro-1.1-ultra',
                                    models: [
                                        { id: 'flux-2-max', name: 'FLUX.2 Max', desc: 'Cao cấp nhất', icon: '👑' },
                                        { id: 'flux-2-pro', name: 'FLUX.2 Pro', desc: 'Chất lượng cao', icon: '⭐' },
                                        { id: 'flux-pro-1.1-ultra', name: 'FLUX 1.1 Ultra', desc: 'Siêu nhanh', icon: '⚡' },
                                        { id: 'flux-pro-1.1', name: 'FLUX 1.1 Pro', desc: 'Cân bằng', icon: '🎯' },
                                        { id: 'flux-dev', name: 'FLUX Dev', desc: 'Thử nghiệm', icon: '🔬' }
                                    ],
                                    getSelectedModel() {
                                        return this.models.find(m => m.id === this.selectedModel) || this.models[2];
                                    }
                                }" @click.away="showModelDropdown = false">
                                    <button type="button" @click="showModelDropdown = !showModelDropdown"
                                        class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer"
                                        :class="{ 'bg-purple-500/20 border-purple-500/40': showModelDropdown }">
                                        <i class="fa-solid fa-microchip text-white/50 text-sm"></i>
                                        <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                            x-text="getSelectedModel().name"></span>
                                        <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform"
                                            :class="{ 'rotate-180': showModelDropdown }"></i>
                                    </button>

                                    <!-- Model Dropdown - Desktop -->
                                    <template x-teleport="body">
                                        <div x-show="showModelDropdown" x-cloak
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 -translate-y-2"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-150"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 -translate-y-2"
                                            class="hidden sm:block fixed w-64 p-2 rounded-xl bg-[#1a1b20] border border-white/10 shadow-2xl z-[9999]"
                                            x-init="$watch('showModelDropdown', value => {
                                                if (value) {
                                                    const btn = $root.querySelector('button');
                                                    const rect = btn.getBoundingClientRect();
                                                    $el.style.top = (rect.bottom + 8) + 'px';
                                                    $el.style.left = rect.left + 'px';
                                                }
                                            })" @click.stop>
                                            <div class="text-white/50 text-xs font-medium mb-2 px-2">Chọn Model AI</div>
                                            <template x-for="model in models" :key="model.id">
                                                <button type="button"
                                                    @click="selectedModel = model.id; showModelDropdown = false"
                                                    class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all text-left"
                                                    :class="selectedModel === model.id ? 'bg-purple-500/30 border border-purple-500/50' : 'hover:bg-white/5 border border-transparent'">
                                                    <span class="text-lg" x-text="model.icon"></span>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-white text-sm font-medium" x-text="model.name">
                                                        </div>
                                                        <div class="text-white/40 text-xs" x-text="model.desc"></div>
                                                    </div>
                                                    <i x-show="selectedModel === model.id"
                                                        class="fa-solid fa-check text-purple-400 text-sm"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Model Dropdown - Mobile (Bottom Sheet) -->
                                    <template x-teleport="body">
                                        <div x-show="showModelDropdown" x-cloak
                                            class="sm:hidden fixed inset-0 z-[100] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                            style="z-index: 9999;" @click.self="showModelDropdown = false" @click.stop>
                                            <div x-show="showModelDropdown"
                                                x-transition:enter="transition ease-out duration-300"
                                                x-transition:enter-start="translate-y-full"
                                                x-transition:enter-end="translate-y-0"
                                                x-transition:leave="transition ease-in duration-200"
                                                x-transition:leave-start="translate-y-0"
                                                x-transition:leave-end="translate-y-full"
                                                class="w-full max-w-lg bg-[#1a1b20] border-t border-white/10 rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">

                                                <!-- Header -->
                                                <div
                                                    class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                                    <span class="text-white font-semibold text-base">Chọn Model
                                                        AI</span>
                                                    <button type="button" @click="showModelDropdown = false"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </div>

                                                <!-- Scrollable List -->
                                                <div class="p-4 overflow-y-auto overscroll-contain">
                                                    <div class="text-white/50 text-sm font-medium mb-3">Danh sách Model
                                                    </div>
                                                    <div class="space-y-1">
                                                        <template x-for="model in models" :key="model.id">
                                                            <button type="button" @click="selectedModel = model.id"
                                                                class="w-full flex items-center gap-3 p-3 rounded-xl transition-all text-left"
                                                                :class="selectedModel === model.id ? 'bg-purple-500/30 border border-purple-500/50' : 'bg-white/5 active:bg-white/10 border border-transparent'">
                                                                <span class="text-2xl" x-text="model.icon"></span>
                                                                <div class="flex-1 min-w-0">
                                                                    <div class="text-white font-semibold text-base"
                                                                        x-text="model.name">
                                                                    </div>
                                                                    <div class="text-white/50 text-sm mt-0.5"
                                                                        x-text="model.desc">
                                                                    </div>
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

                                                <!-- Footer Action -->
                                                <div
                                                    class="p-4 border-t border-white/5 bg-[#1a1b20] safe-area-bottom shrink-0">
                                                    <button type="button" @click="showModelDropdown = false"
                                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold text-center active:scale-[0.98] transition-transform shadow-lg shadow-purple-900/20">
                                                        Xác nhận lựa chọn
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <input type="hidden" name="model" :value="selectedModel">
                                </div>

                                <!-- Hidden inputs for form submission -->
                                <input type="hidden" name="aspect_ratio" :value="selectedRatio">
                                <input type="hidden" name="width" :value="customWidth">
                                <input type="hidden" name="height" :value="customHeight">
                            </div>

                            <!-- Generate Button -->
                            <button type="submit"
                                class="flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/30 active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                                <span>Tạo ảnh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tool Icons - Grid on mobile, inline on desktop -->
            <div
                class="grid grid-cols-4 gap-2 sm:inline-flex sm:items-center sm:gap-1 p-1.5 sm:p-2 rounded-2xl sm:rounded-full bg-black/30 backdrop-blur-xl border border-white/10 max-w-xs sm:max-w-none mx-auto">
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-image text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Image</span>
                </a>
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-palette text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Styles</span>
                </a>
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all relative">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">AI Art</span>
                    <span
                        class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2 h-2 sm:w-auto sm:h-auto sm:px-1 sm:py-0.5 rounded-full text-[8px] font-bold bg-gradient-to-r from-pink-500 to-purple-500 text-white sm:text-[9px]">
                        <span class="hidden sm:inline">NEW</span>
                    </span> </a>
                <a href="{{ route('history.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-images text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Gallery</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== STYLES CAROUSEL ========== -->
    <section class="py-4 sm:py-8" id="styles" x-data="{
        currentIndex: 0,
        itemCount: {{ $styles->count() }},
        autoScroll: null,
        paused: false,
        scrollContainer: null,

        init() {
            this.scrollContainer = this.$refs.carousel;
            this.startAutoScroll();
        },

        startAutoScroll() {
            this.autoScroll = setInterval(() => {
                if (!this.paused) {
                    this.next();
                }
            }, 3000);
        },

        stopAutoScroll() {
            if (this.autoScroll) {
                clearInterval(this.autoScroll);
                this.autoScroll = null;
            }
        },

        pauseAutoScroll() {
            this.paused = true;
            // Resume after 5 seconds of no interaction
            setTimeout(() => {
                this.paused = false;
            }, 5000);
        },

        next() {
            if (!this.scrollContainer) return;
            // Calculate true stride (width + gap)
            const style = window.getComputedStyle(this.scrollContainer);
            const gap = parseFloat(style.gap) || 12;
            const cardWidth = this.scrollContainer.querySelector('.style-card-wrapper')?.offsetWidth || 180;
            const stride = cardWidth + gap;
            
            // Always scroll 1 card at a time
            const scrollAmount = stride;

            const maxScroll = this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth;
            
            // If near end, loop back to start
            if (this.scrollContainer.scrollLeft >= maxScroll - 10) {
                this.scrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                this.scrollContainer.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            }
        },

        prev() {
            if (!this.scrollContainer) return;
            // Calculate true stride
            const style = window.getComputedStyle(this.scrollContainer);
            const gap = parseFloat(style.gap) || 12;
            const cardWidth = this.scrollContainer.querySelector('.style-card-wrapper')?.offsetWidth || 180;
            const stride = cardWidth + gap;
            
            // Always scroll 1 card at a time
            const scrollAmount = stride;
            
            // If at start, loop to end
            if (this.scrollContainer.scrollLeft <= 10) {
                const maxScroll = this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth;
                this.scrollContainer.scrollTo({ left: maxScroll, behavior: 'smooth' });
            } else {
                this.scrollContainer.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            }
        }
    }">
        <div class="px-2 sm:px-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#d3d6db]">Chọn Style yêu thích</h2>
                    <p class="text-white/40 text-xs sm:text-sm mt-1">Biến ảnh thường thành tác phẩm nghệ thuật</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Nav Buttons -->
                    <button @click="prev(); pauseAutoScroll();"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 text-white/60 hover:text-white transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-left text-sm"></i>
                    </button>
                    <button @click="next(); pauseAutoScroll();"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 text-white/60 hover:text-white transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-right text-sm"></i>
                    </button>
                    <!-- View All -->
                    <a href="{{ route('styles.index') }}"
                        class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium hover:from-purple-500 hover:to-pink-500 transition-all">
                        <span>Xem tất cả</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        @if($styles->isEmpty())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-2xl text-center py-16">
                    <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                    <p class="text-white/50 text-lg mb-2">Chưa có Style nào</p>
                    <p class="text-white/30 text-sm">Hãy quay lại sau nhé!</p>
                </div>
            </div>
        @else
            <!-- Carousel Container -->
            <div class="relative px-2 sm:px-4" @mouseenter="pauseAutoScroll()" @mouseleave="paused = false">
                <div x-ref="carousel"
                    class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth pt-4 pb-1 no-scrollbar snap-x snap-mandatory"
                    style="-webkit-overflow-scrolling: touch;">
                    @foreach($styles as $style)
                        <a href="{{ route('studio.show', $style->slug) }}"
                            class="style-card-wrapper flex-shrink-0 w-40 sm:w-44 lg:w-[calc((100%-5rem)/6)] group relative z-0 hover:z-20 snap-start">
                            <div
                                class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden transition-all duration-300 hover:border-purple-500/40">
                                <!-- Image -->
                                <div class="relative aspect-[3/4] overflow-hidden">
                                    <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}"
                                        class="w-full h-full object-cover transition-all duration-500 group-hover:scale-110 group-hover:blur-sm"
                                        loading="lazy" decoding="async">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent">
                                    </div>

                                    <!-- Tag -->
                                    @if($style->tag)
                                        <span
                                            class="absolute top-2 left-2 z-20 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-gradient-to-r from-{{ $style->tag->color_from }} to-{{ $style->tag->color_to }} text-white text-[8px] sm:text-[9px] font-bold shadow">
                                            <i class="fa-solid {{ $style->tag->icon }}"></i> {{ $style->tag->name }}
                                        </span>
                                    @endif

                                    <!-- Price -->
                                    <div
                                        class="absolute top-2 right-2 z-20 px-1.5 py-0.5 rounded-full bg-black/60 border border-white/10 text-white text-[9px] sm:text-[10px] font-bold flex items-center gap-0.5">
                                        <i class="fa-solid fa-star text-yellow-400"></i> {{ number_format($style->price, 0) }}
                                    </div>

                                    <!-- Hover Description Overlay -->
                                    <div
                                        class="absolute inset-0 flex flex-col items-center justify-center p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/40 backdrop-blur-[2px]">
                                        <p
                                            class="text-white/90 text-[10px] sm:text-xs text-center line-clamp-3 leading-relaxed mb-2">
                                            {{ $style->description ?? 'Khám phá phong cách độc đáo này!' }}
                                        </p>
                                        <span
                                            class="px-3 py-1.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white text-[10px] sm:text-xs font-semibold shadow-lg">
                                            Thử ngay
                                        </span>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="p-2 sm:p-2.5">
                                    <h3
                                        class="font-semibold text-white text-xs sm:text-sm line-clamp-1 group-hover:text-purple-300 transition-colors">
                                        {{ $style->name }}
                                    </h3>
                                    <div class="flex items-center gap-1 text-white/40 text-[9px] sm:text-[10px] mt-1">
                                        <i class="fa-solid fa-images"></i>
                                        {{ number_format($style->generated_images_count) }} lượt
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Mobile View All Button -->
        <div class="flex justify-center mt-4 sm:hidden px-4">
            <a href="{{ route('styles.index') }}"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold text-center">
                Xem tất cả Styles
            </a>
        </div>

        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    </section>

    <!-- ========== INSPIRATIONS GALLERY ========== -->
    <livewire:inspirations-gallery />


</x-app-layout>