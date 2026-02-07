<div class="relative min-h-screen" x-data="{
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
    <div class="max-w-6xl mx-auto px-4 pb-40">
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

    {{-- Prompt Bar Container - STICKY AND SYNCED WITH SITE --}}
    <div class="sticky bottom-16 sm:bottom-0 left-0 right-0 z-40 pb-6 px-4">
        {{-- Floating fade-out gradient inside content area --}}
        <div
            class="absolute inset-x-0 bottom-full h-12 bg-gradient-to-t from-[#0a0a0c] to-transparent pointer-events-none">
        </div>

        <div class="max-w-4xl mx-auto">
            <div
                class="bg-[#1b1c21]/90 backdrop-blur-2xl border border-white/10 p-4 rounded-2xl shadow-2xl flex flex-col gap-4">

                {{-- Ref images slot --}}
                <div x-show="selectedImages.length > 0" class="flex flex-wrap gap-2 pb-2" x-cloak>
                    <template x-for="img in selectedImages" :key="img.id">
                        <div class="relative group/img">
                            <img :src="img.url" class="w-10 h-10 rounded-lg object-cover border border-white/10">
                            <button @click="removeImage(img.id)"
                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Input Area --}}
                <div class="flex flex-col gap-3">
                    <textarea wire:model="prompt" rows="2"
                        placeholder="Mô tả tác phẩm AI của bạn (VD: Một thành phố tương lai đầy ánh đèn neon...)"
                        class="w-full bg-transparent border-none outline-none ring-0 focus:ring-0 text-white text-sm sm:text-base placeholder-white/20 resize-none min-h-[60px]"
                        {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-white/5">
                        <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1">
                            {{-- Image Ref Button --}}
                            <button type="button" @click="showImagePicker = true; loadRecentImages()"
                                class="h-9 w-9 flex items-center justify-center rounded-lg bg-white/5 border border-white/5 text-white/40 hover:text-purple-400 hover:bg-purple-500/10 transition-all">
                                <i class="fa-solid fa-image text-sm"></i>
                            </button>

                            {{-- Model Selector --}}
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" type="button"
                                    class="h-9 px-3 flex items-center gap-2 rounded-lg bg-white/5 border border-white/5 text-xs text-white/60 hover:text-white transition-all">
                                    <i class="fa-solid fa-microchip text-purple-500 text-[10px]"></i>
                                    <span
                                        class="max-w-[70px] truncate text-[11px] font-medium uppercase tracking-tight">
                                        {{ collect($availableModels)->firstWhere('id', $modelId)['name'] ?? 'Model' }}
                                    </span>
                                </button>
                                <div x-show="open" x-cloak
                                    class="absolute bottom-full left-0 mb-2 w-48 bg-[#16171d] border border-white/10 rounded-xl shadow-2xl py-1 z-50 overflow-hidden">
                                    @foreach($availableModels as $m)
                                        <button @click="open = false" wire:click="$set('modelId', '{{ $m['id'] }}')"
                                            class="w-full px-4 py-2.5 text-left text-xs hover:bg-white/5 flex items-center justify-between {{ $modelId === $m['id'] ? 'text-purple-400 bg-purple-500/5' : 'text-white/50' }}">
                                            <span>{{ $m['name'] ?? $m['id'] }}</span>
                                            @if($modelId === $m['id']) <i class="fa-solid fa-check"></i> @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Aspect Ratio --}}
                            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                <button @click="open = !open" type="button"
                                    class="h-9 px-3 flex items-center gap-2 rounded-lg bg-white/5 border border-white/5 text-xs text-white/60 hover:text-white transition-all">
                                    <i class="fa-solid fa-expand text-cyan-500 text-[10px]"></i>
                                    <span class="text-[11px] font-medium">{{ $aspectRatio }}</span>
                                </button>
                                <div x-show="open" x-cloak
                                    class="absolute bottom-full left-0 mb-2 w-32 bg-[#16171d] border border-white/10 rounded-xl shadow-2xl py-1 z-50 max-h-48 overflow-y-auto scrollbar-none">
                                    @foreach($aspectRatios as $ratio => $lab)
                                        <button @click="open = false" wire:click="$set('aspectRatio', '{{ $ratio }}')"
                                            class="w-full px-4 py-2 text-left text-[11px] hover:bg-white/5 flex items-center justify-between {{ $aspectRatio === $ratio ? 'text-cyan-400 bg-cyan-500/5' : 'text-white/50' }}">
                                            <span>{{ $lab }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Generate Button --}}
                        <div class="flex items-center gap-3">
                            <span class="text-[10px] text-white/30 hidden sm:inline-flex items-center gap-1">
                                <i class="fa-solid fa-coins animate-pulse"></i>
                                <span>{{ $creditCost }} Credits</span>
                            </span>
                            <button wire:click="generate" {{ $isGenerating ? 'disabled' : '' }}
                                class="h-10 px-6 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-sm font-bold shadow-lg shadow-purple-500/20 transition-all flex items-center gap-2 active:scale-95 disabled:opacity-50 disabled:grayscale">
                                @if($isGenerating)
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <span class="hidden sm:inline">Đang tạo...</span>
                                @else
                                    <i class="fa-solid fa-bolt"></i>
                                    <span>TẠO ẢNH</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS (Image Picker) - Reused structure --}}
    @include('components.image-picker-modal') {{-- Giả định có component này hoặc dán code gốc --}}

    {{-- Inline Image Picker (Backup if not componentized) --}}
    <div x-show="showImagePicker" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center pointer-events-none">
        <div class="absolute inset-0 bg-[#0a0a0c]/80 backdrop-blur-md pointer-events-auto"
            @click="showImagePicker = false"></div>
        <div
            class="relative w-full max-w-xl bg-[#1b1c21] border border-white/10 sm:rounded-3xl shadow-3xl pointer-events-auto overflow-hidden mx-4">
            <div class="p-6 border-b border-white/5 flex items-center justify-between">
                <h3 class="text-white font-bold">Thêm ảnh tham chiếu</h3>
                <button @click="showImagePicker = false" class="text-white/40 hover:text-white"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="p-6">
                {{-- Tabs & Content (Omitting detail for brevity but keeping structure) --}}
                <div
                    class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-white/5 rounded-2xl">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-white/10 mb-3"></i>
                    <p class="text-white/40 text-sm">Tính năng đang được kích hoạt...</p>
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
                        <input type="file" class="hidden" @change="handleFileSelect($event)" accept="image/*" multiple>
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