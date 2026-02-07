<div class="min-h-[100dvh] flex flex-col bg-[#0a0a0f]" x-data="{
        aspectRatios: @js($aspectRatios),
        models: @js($availableModels),
        showRatioDropdown: false,
        showModelDropdown: false,
        
        {{-- Image Picker State (from Home) --}}
        showImagePicker: false,
        selectedImages: [],
        maxImages: 4,
        isDragging: false,
        recentImages: [],
        isLoadingPicker: false,
        urlInput: '',
        activeTab: 'upload',
        
        async loadRecentImages() {
            if (this.recentImages.length > 0) return;
            this.isLoadingPicker = true;
            try {
                const response = await fetch('/api/user/recent-images');
                if (response.ok) {
                    const data = await response.json();
                    this.recentImages = data.images || [];
                }
            } catch (e) {
                console.log('Could not load recent images');
            }
            this.isLoadingPicker = false;
        },
        
        handleFileSelect(event) {
            const files = Array.from(event.target.files);
            files.forEach(file => this.processFile(file));
            event.target.value = '';
        },
        
        processFile(file) {
            if (this.selectedImages.length >= this.maxImages) return;
            if (!file.type.startsWith('image/')) return;
            const url = URL.createObjectURL(file);
            this.selectedImages.push({ type: 'file', file: file, url: url, id: Date.now() + Math.random() });
        },
        
        selectFromRecent(imageUrl) {
            if (this.selectedImages.length >= this.maxImages) return;
            if (this.selectedImages.find(img => img.url === imageUrl)) return;
            this.selectedImages.push({ type: 'url', url: imageUrl, id: Date.now() });
        },
        
        isSelected(imageUrl) {
            return this.selectedImages.find(img => img.url === imageUrl);
        },
        
        removeImage(id) {
            this.selectedImages = this.selectedImages.filter(img => img.id !== id);
        }
     }" wire:poll.2s="pollImageStatus">

    {{-- Main Content - History Gallery --}}
    <div class="flex-1 overflow-y-auto overflow-x-hidden">
        <div class="max-w-6xl mx-auto px-4 py-6 pb-48">
            {{-- App Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-400"></i>
                        AI Studio
                    </h1>
                </div>

                {{-- Global Filters (App-like) --}}
                <div class="flex items-center gap-2 overflow-x-auto scrollbar-none pb-1">
                    <button
                        class="px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs text-white/60 hover:text-white transition-colors whitespace-nowrap">
                        Tất cả
                    </button>
                    <button
                        class="px-3 py-1.5 rounded-full bg-transparent border border-transparent text-xs text-white/40 hover:text-white transition-colors whitespace-nowrap">
                        Mới nhất
                    </button>
                    <button
                        class="px-3 py-1.5 rounded-full bg-transparent border border-transparent text-xs text-white/40 hover:text-white transition-colors whitespace-nowrap">
                        Đã lưu
                    </button>
                </div>
            </div>

            {{-- History Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                {{-- Generating Placeholder --}}
                @if($isGenerating && !$generatedImageUrl)
                    <div
                        class="aspect-[var(--aspect-ratio,1/1)] rounded-2xl bg-white/5 border border-white/10 overflow-hidden relative group animate-pulse">
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3">
                            <i class="fa-solid fa-spinner fa-spin text-purple-400 text-xl"></i>
                            <span class="text-xs text-white/40">Đang tạo...</span>
                        </div>
                    </div>
                @endif

                {{-- Actual History --}}
                @forelse($history as $image)
                    <div class="group relative rounded-2xl overflow-hidden border border-white/10 bg-black/30 aspect-[var(--aspect-ratio,1/1)] hover:border-purple-500/50 transition-all duration-300"
                        style="--aspect-ratio: {{ str_replace(':', '/', $image->generation_params['aspect_ratio'] ?? '1/1') }}">

                        <img src="{{ $image->image_url }}" alt="Generated"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            loading="lazy">

                        {{-- Overlay Info --}}
                        <div
                            class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                            <p class="text-[10px] text-white/90 line-clamp-2 leading-tight mb-2">
                                {{ $image->final_prompt }}
                            </p>
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-white/50">{{ $image->created_at->diffForHumans() }}</span>
                                <div class="flex items-center gap-2">
                                    <button class="text-white/70 hover:text-white"><i
                                            class="fa-solid fa-download text-[11px]"></i></button>
                                    <button class="text-white/70 hover:text-white"><i
                                            class="fa-solid fa-share text-[11px]"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @if(!$isGenerating)
                        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
                            <div class="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-4">
                                <i class="fa-solid fa-image text-white/20 text-2xl"></i>
                            </div>
                            <h3 class="text-white/60 font-medium">Chưa có tác phẩm nào</h3>
                            <p class="text-white/30 text-sm mt-1">Bắt đầu bằng cách nhập prompt bên dưới</p>
                        </div>
                    @endif
                @endforelse
            </div>

            {{-- Load More --}}
            @if(method_exists($history, 'hasMorePages') && $history->hasMorePages())
                <div class="mt-10 text-center">
                    <button wire:click="loadMore"
                        class="px-6 py-2 rounded-full bg-white/5 border border-white/10 text-sm text-white/60 hover:text-white hover:bg-white/10 transition-all">
                        Xem thêm
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Bottom Fixed Prompt Bar (Home-Style) --}}
    <div class="fixed bottom-0 left-0 right-0 z-[60] safe-area-bottom">
        {{-- Fade gradient --}}
        <div
            class="absolute inset-x-0 bottom-full h-24 bg-gradient-to-t from-[#0a0a0f] to-transparent pointer-events-none">
        </div>

        <div class="bg-[#0a0a0f]/80 backdrop-blur-xl border-t border-white/5 px-4 py-4 sm:py-6">
            <div class="max-w-3xl mx-auto">
                <div class="relative group/form">
                    {{-- Glow --}}
                    <div
                        class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-15 blur-lg group-hover/form:opacity-25 transition-opacity duration-500">
                    </div>

                    {{-- Input container --}}
                    <div
                        class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/60 backdrop-blur-3xl border border-white/10 shadow-2xl">

                        {{-- Image References (Thumbnails) --}}
                        <div x-show="selectedImages.length > 0" class="flex flex-wrap gap-2 pb-2" x-cloak>
                            <template x-for="img in selectedImages" :key="img.id">
                                <div class="relative group/img">
                                    <img :src="img.url"
                                        class="w-12 h-12 rounded-lg object-cover border border-white/10">
                                    <button @click="removeImage(img.id)"
                                        class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Textarea --}}
                        <textarea wire:model="prompt" rows="2" placeholder="Mô tả tác phẩm tiếp theo của bạn..."
                            class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/30 text-sm sm:text-base resize-none focus:placeholder-white/50 transition-all"
                            {{ $isGenerating ? 'disabled' : '' }} @keydown.meta.enter="$wire.generate()"
                            @keydown.ctrl.enter="$wire.generate()"></textarea>

                        {{-- Bottom Row --}}
                        <div class="flex items-center justify-between gap-3 pt-1 border-t border-white/5">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                {{-- Image Picker Toggle (Home Style) --}}
                                <button type="button" @click="showImagePicker = true; loadRecentImages()"
                                    class="w-9 h-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/10 text-white/50 hover:text-purple-400 hover:bg-purple-500/10 transition-all">
                                    <i class="fa-solid fa-image text-sm"></i>
                                </button>

                                {{-- Model Dropdown (Polished) --}}
                                <div class="relative" @click.away="showModelDropdown = false">
                                    <button type="button" @click="showModelDropdown = !showModelDropdown"
                                        class="h-9 px-3 flex items-center gap-2 rounded-lg bg-white/5 border border-white/10 text-xs sm:text-sm text-white/60 hover:text-white transition-all">
                                        <i class="fa-solid fa-microchip text-purple-400"></i>
                                        <span
                                            class="max-w-[80px] sm:max-w-none truncate">{{ collect($availableModels)->firstWhere('id', $modelId)['name'] ?? 'Model' }}</span>
                                        <i class="fa-solid fa-chevron-down text-[8px] opacity-40"></i>
                                    </button>

                                    <div x-show="showModelDropdown" x-cloak
                                        class="absolute bottom-full left-0 mb-3 w-48 py-2 rounded-xl bg-[#16171d] border border-white/10 shadow-2xl z-50">
                                        @foreach($availableModels as $model)
                                            <button @click="showModelDropdown = false"
                                                wire:click="$set('modelId', '{{ $model['id'] }}')"
                                                class="w-full px-4 py-2 text-left text-sm hover:bg-white/5 transition-colors flex items-center justify-between {{ $modelId === $model['id'] ? 'text-purple-400 bg-purple-500/5' : 'text-white/60' }}">
                                                <span>{{ $model['name'] ?? $model['id'] }}</span>
                                                @if($modelId === $model['id']) <i class="fa-solid fa-check text-[10px]"></i>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Aspect Ratio Dropdown --}}
                                <div class="relative" @click.away="showRatioDropdown = false">
                                    <button type="button" @click="showRatioDropdown = !showRatioDropdown"
                                        class="h-9 px-3 flex items-center gap-2 rounded-lg bg-white/5 border border-white/10 text-xs sm:text-sm text-white/60 hover:text-white transition-all">
                                        <i class="fa-solid fa-crop text-cyan-400"></i>
                                        <span>{{ $aspectRatio }}</span>
                                        <i class="fa-solid fa-chevron-down text-[8px] opacity-40"></i>
                                    </button>

                                    <div x-show="showRatioDropdown" x-cloak
                                        class="absolute bottom-full left-0 mb-3 w-36 py-2 rounded-xl bg-[#16171d] border border-white/10 shadow-2xl z-50 max-h-60 overflow-y-auto scrollbar-none">
                                        @foreach($aspectRatios as $ratio => $label)
                                            <button @click="showRatioDropdown = false"
                                                wire:click="$set('aspectRatio', '{{ $ratio }}')"
                                                class="w-full px-4 py-2 text-left text-sm hover:bg-white/5 transition-colors flex items-center justify-between {{ $aspectRatio === $ratio ? 'text-cyan-400 bg-cyan-500/5' : 'text-white/60' }}">
                                                <span>{{ $label }}</span>
                                                @if($aspectRatio === $ratio) <i class="fa-solid fa-check text-[10px]"></i>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Generate Button --}}
                            <div class="flex items-center gap-3">
                                <div
                                    class="hidden sm:flex items-center gap-1.5 px-2 py-1 rounded-lg bg-yellow-500/5 text-yellow-400/80 text-xs font-medium">
                                    <i class="fa-solid fa-coins"></i>
                                    <span>{{ number_format($creditCost, 0) }}</span>
                                </div>
                                <button wire:click="generate" wire:loading.attr="disabled"
                                    class="h-10 px-5 flex items-center gap-2 rounded-xl bg-white text-black font-bold text-sm hover:bg-white/90 active:scale-95 transition-all disabled:opacity-50">
                                    <i class="fa-solid fa-arrow-up" x-show="!$wire.isGenerating"></i>
                                    <i class="fa-solid fa-spinner fa-spin" x-show="$wire.isGenerating"></i>
                                    <span class="hidden sm:inline"
                                        x-text="$wire.isGenerating ? 'Đang tạo...' : 'Tạo ngay'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Teleported Image Picker (Exact same as home) --}}
    <template x-teleport="body">
        <div x-show="showImagePicker" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" @click="showImagePicker = false"></div>
            <div
                class="relative w-full max-w-2xl bg-[#0a0a0f] border border-white/10 rounded-2xl overflow-hidden shadow-2xl flex flex-col max-h-[80vh]">
                <div class="p-4 border-b border-white/5 flex items-center justify-between">
                    <h3 class="text-white font-semibold">Tải lên ảnh mẫu</h3>
                    <button @click="showImagePicker = false" class="text-white/40 hover:text-white"><i
                            class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 content-scrollbar">
                    {{-- Tabs --}}
                    <div class="flex gap-4 mb-4 border-b border-white/5">
                        <button @click="activeTab = 'upload'" class="pb-2 text-sm font-medium transition-colors"
                            :class="activeTab === 'upload' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/40'">Tải
                            lên</button>
                        <button @click="activeTab = 'recent'" class="pb-2 text-sm font-medium transition-colors"
                            :class="activeTab === 'recent' ? 'text-purple-400 border-b-2 border-purple-500' : 'text-white/40'">Gần
                            đây</button>
                    </div>

                    <div x-show="activeTab === 'upload'">
                        <label
                            class="flex flex-col items-center justify-center p-10 border-2 border-dashed border-white/10 rounded-xl hover:border-purple-500/50 cursor-pointer transition-all">
                            <input type="file" class="hidden" @change="handleFileSelect($event)" accept="image/*"
                                multiple>
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-white/20 mb-3"></i>
                            <span class="text-white/60 text-sm">Kéo thả hoặc nhấn để chọn ảnh</span>
                        </label>
                    </div>

                    <div x-show="activeTab === 'recent'">
                        <div x-show="isLoadingPicker" class="py-10 text-center"><i
                                class="fa-solid fa-spinner fa-spin text-purple-400 text-xl"></i></div>
                        <div x-show="!isLoadingPicker" class="grid grid-cols-4 gap-2">
                            <template x-for="img in recentImages" :key="img.url">
                                <button @click="selectFromRecent(img.url)"
                                    class="relative aspect-square rounded-lg overflow-hidden border-2 transition-all"
                                    :class="isSelected(img.url) ? 'border-purple-500' : 'border-transparent'">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    <div x-show="isSelected(img.url)"
                                        class="absolute inset-0 bg-purple-500/20 flex items-center justify-center"><i
                                            class="fa-solid fa-check text-white"></i></div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-white/5 flex justify-end gap-3">
                    <button @click="showImagePicker = false"
                        class="px-4 py-2 text-sm text-white/60 hover:text-white">Lưu</button>
                    <button @click="showImagePicker = false"
                        class="px-5 py-2 bg-purple-600 text-white text-sm font-bold rounded-lg hover:bg-purple-500 transition-all">Xác
                        nhận</button>
                </div>
            </div>
        </div>
    </template>

    <style>
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .scrollbar-none::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-none {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }

        .content-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .content-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .content-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
    </style>
</div>