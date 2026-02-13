{{-- ========== IMAGE PREVIEW - DESKTOP ========== --}}
<template x-teleport="body">
    <div x-show="showPreview" x-cloak
        class="hidden sm:flex fixed inset-0 z-[200] items-center justify-center bg-black/95 backdrop-blur-md"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="closePreview()"
        @keydown.escape.window="closePreview()">

        <div x-show="showPreview" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="relative max-w-4xl w-full mx-4" @click.stop>

            {{-- Close --}}
            <button @click="closePreview()"
                class="absolute -top-12 right-0 w-10 h-10 rounded-full bg-white/10 text-white/70 hover:text-white hover:bg-white/20 flex items-center justify-center transition-all">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            {{-- Nav Arrows --}}
            <button x-show="previewIndex > 0" @click="prevImage()"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/60 hover:bg-black/80 text-white/80 hover:text-white flex items-center justify-center transition-all z-10 backdrop-blur-sm">
                <i class="fa-solid fa-chevron-left text-lg"></i>
            </button>
            <button x-show="previewIndex < historyData.length - 1" @click="nextImage()"
                class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 rounded-full bg-black/60 hover:bg-black/80 text-white/80 hover:text-white flex items-center justify-center transition-all z-10 backdrop-blur-sm">
                <i class="fa-solid fa-chevron-right text-lg"></i>
            </button>

            {{-- Image + Info --}}
            <div class="rounded-2xl overflow-hidden bg-[#15161A] border border-white/10">
                <img :src="previewImage?.url" alt="Preview" class="w-full max-h-[70vh] object-contain">

                <div class="p-5 border-t border-white/5">
                    {{-- Prompt --}}
                    <div x-data="{ expanded: false }" class="mb-3">
                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-quote-left text-purple-400/50 text-sm mt-0.5 shrink-0"></i>
                            <p class="text-white/70 text-sm italic flex-1" :class="expanded ? '' : 'line-clamp-2'"
                                x-text="previewImage?.prompt || ''"></p>
                        </div>
                        <button x-show="(previewImage?.prompt || '').length > 150" @click="expanded = !expanded"
                            class="mt-2 text-purple-400 text-xs font-medium hover:text-purple-300 transition-colors flex items-center gap-1">
                            <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                            <i class="fa-solid fa-chevron-down text-[10px] transition-transform"
                                :class="expanded && 'rotate-180'"></i>
                        </button>
                    </div>

                    {{-- Meta --}}
                    <div class="flex flex-wrap items-center gap-3 mb-4 text-xs text-white/40">
                        <span x-show="previewImage?.model" class="flex items-center gap-1"><i
                                class="fa-solid fa-microchip"></i><span x-text="previewImage?.model"></span></span>
                        <span x-show="previewImage?.ratio" class="flex items-center gap-1"><i
                                class="fa-solid fa-crop"></i><span x-text="previewImage?.ratio"></span></span>
                        <span x-show="previewImage?.created_at" class="flex items-center gap-1"><i
                                class="fa-regular fa-clock"></i><span x-text="previewImage?.created_at"></span></span>
                        <span class="flex items-center gap-1"><i class="fa-solid fa-image"></i><span
                                x-text="(previewIndex + 1) + '/' + historyData.length"></span></span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-wrap gap-3">
                        <button @click="downloadImage(previewImage?.url)"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all">
                            <i class="fa-solid fa-download"></i> Tải xuống
                        </button>
                        <button @click="shareImage()"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all">
                            <i class="fa-solid fa-share-nodes"></i> Chia sẻ
                        </button>
                        <button @click="useAsReference()"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-500/20 hover:bg-purple-500/30 text-purple-300 hover:text-purple-200 text-sm font-medium transition-all">
                            <i class="fa-solid fa-images"></i> Dùng làm mẫu
                        </button>
                        <button @click="copyPrompt()"
                            class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-sm font-medium transition-all">
                            <i class="fa-solid fa-copy"></i> Copy prompt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========== IMAGE PREVIEW - MOBILE ========== --}}
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

        {{-- Image with swipe --}}
        <div class="flex-1 flex flex-col items-center justify-center p-4 overflow-hidden relative"
            @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">
            <button x-show="previewIndex > 0" @click="prevImage()"
                class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 text-white/80 flex items-center justify-center active:scale-95 z-10 backdrop-blur-sm">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <img :src="previewImage?.url" alt="Preview" class="max-w-full max-h-full object-contain rounded-xl">
            <button x-show="previewIndex < historyData.length - 1" @click="nextImage()"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/60 text-white/80 flex items-center justify-center active:scale-95 z-10 backdrop-blur-sm">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            {{-- Dots --}}
            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 px-2 py-1 rounded-full bg-black/40 backdrop-blur-sm"
                x-show="historyData.length > 1 && historyData.length <= 10">
                <template x-for="(_, i) in historyData.slice(0, 10)" :key="i">
                    <button @click="goToImage(i)" class="w-2 h-2 rounded-full transition-all"
                        :class="previewIndex === i ? 'bg-white scale-125' : 'bg-white/40'"></button>
                </template>
            </div>
            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-black/40 backdrop-blur-sm text-white/70 text-xs"
                x-show="historyData.length > 10" x-text="(previewIndex + 1) + ' / ' + historyData.length"></div>
        </div>

        {{-- Prompt --}}
        <div class="px-4 py-3 bg-white/5" x-data="{ expanded: false }">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-quote-left text-purple-400/50 text-[10px] mt-1 shrink-0"></i>
                <p class="text-white/60 text-xs italic flex-1" :class="expanded ? '' : 'line-clamp-2'"
                    x-text="previewImage?.prompt || ''"></p>
            </div>
            <button x-show="(previewImage?.prompt || '').length > 100" @click="expanded = !expanded"
                class="mt-1 text-purple-400 text-[10px] font-medium flex items-center gap-1">
                <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                <i class="fa-solid fa-chevron-down text-[8px] transition-transform"
                    :class="expanded && 'rotate-180'"></i>
            </button>
        </div>

        {{-- Actions --}}
        <div class="shrink-0 grid grid-cols-4 gap-2 p-4 bg-[#0a0a0f] border-t border-white/5 safe-area-bottom">
            <button @click="downloadImage(previewImage?.url)"
                class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                <i class="fa-solid fa-download text-white/70"></i><span
                    class="text-white/60 text-[10px] font-medium">Tải</span>
            </button>
            <button @click="shareImage()"
                class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                <i class="fa-solid fa-share-nodes text-white/70"></i><span
                    class="text-white/60 text-[10px] font-medium">Chia sẻ</span>
            </button>
            <button @click="useAsReference()"
                class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-purple-500/20 active:bg-purple-500/30 transition-colors">
                <i class="fa-solid fa-images text-purple-400"></i><span
                    class="text-purple-300 text-[10px] font-medium">Mẫu</span>
            </button>
            <button @click="copyPrompt()"
                class="flex flex-col items-center gap-1 py-2.5 rounded-xl bg-white/10 active:bg-white/20 transition-colors">
                <i class="fa-solid fa-copy text-white/70"></i><span
                    class="text-white/60 text-[10px] font-medium">Copy</span>
            </button>
        </div>
    </div>
</template>