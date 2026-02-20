{{-- ============================================================ --}}
{{-- IMAGE PREVIEW MODAL (no x-teleport — stays in Alpine scope) --}}
{{-- ============================================================ --}}
<div x-show="showPreview" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center t2i-preview"
    role="dialog" aria-modal="true" aria-label="Xem trước ảnh" @keydown.escape.window="if (showPreview) closePreview()"
    @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)" x-ref="previewModal" @keydown.tab.prevent="
        const focusable = [...$refs.previewModal.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex=\'-1\'])')].filter(el => el.offsetParent !== null);
        if (!focusable.length) return;
        const idx = focusable.indexOf(document.activeElement);
        if ($event.shiftKey) { focusable[idx <= 0 ? focusable.length - 1 : idx - 1].focus(); }
        else { focusable[idx >= focusable.length - 1 ? 0 : idx + 1].focus(); }
    "
    x-effect="if (showPreview) { $nextTick(() => { const btn = $refs.previewModal?.querySelector('[aria-label]'); if (btn) btn.focus(); }); }">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closePreview()" x-show="showPreview"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    {{-- ============ DESKTOP VIEW (sm+) ============ --}}
    <div class="hidden sm:flex relative z-10 w-full h-[100dvh] max-w-6xl mx-auto px-4 py-4 items-center gap-4"
        x-show="showPreview" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95">

        {{-- Close button --}}
        <button @click="closePreview()" aria-label="Đóng xem trước"
            class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-[8px] text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1]">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>

        {{-- Prev Arrow --}}
        <button @click="prevImage()" x-show="previewIndex > 0" aria-label="Ảnh trước"
            class="shrink-0 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-[8px] text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1]">
            <i class="fa-solid fa-chevron-left text-lg"></i>
        </button>
        <div x-show="previewIndex === 0" class="shrink-0 w-12"></div>

        {{-- Main Image + Info --}}
        <div class="flex-1 flex flex-col items-center gap-4 min-w-0">
            {{-- Image --}}
            <div class="relative w-full flex items-center justify-center" style="max-height: 68vh;">
                <img :src="previewImage?.url" :key="'preview-' + previewIndex"
                    class="max-w-full max-h-[80vh] object-contain rounded-xl shadow-2xl transition-opacity duration-200"
                    :alt="previewImage?.prompt || 'Preview'"
                    onerror="this.onerror=null; this.src='/images/placeholder.svg'">
            </div>

            {{-- Info panel --}}
            <div
                class="w-full max-w-3xl max-h-[22vh] overflow-y-auto rounded-xl bg-black/30 border border-white/[0.08] p-3">
                {{-- Prompt --}}
                <div x-data="{ expanded: false }" class="mb-3">
                    <p class="text-white/80 text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-2'"
                        x-text="previewImage?.prompt || ''"></p>
                    <button x-show="(previewImage?.prompt || '').length > 150" @click="expanded = !expanded"
                        class="text-purple-400 text-xs mt-1 hover:text-purple-300 transition-colors">
                        <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                    </button>
                </div>

                {{-- Meta --}}
                <div class="flex items-center gap-3 text-xs text-white/40 mb-3">
                    <span x-show="previewImage?.model" class="text-purple-300/70" x-text="previewImage?.model"></span>
                    <span x-show="previewImage?.ratio" class="text-white/20">•</span>
                    <span x-show="previewImage?.ratio" x-text="previewImage?.ratio"></span>
                    <span x-show="previewImage?.created_at" class="text-white/20">•</span>
                    <span x-show="previewImage?.created_at" x-text="previewImage?.created_at"></span>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <button @click="downloadImage(previewImage?.url)"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/70 hover:text-white text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-download text-[11px]"></i>
                        <span>Tải xuống</span>
                    </button>
                    <button @click="window.open(previewImage?.url, '_blank')"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/70 hover:text-white text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                        <span>Mở tab mới</span>
                    </button>
                    <button @click="shareImage()"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/70 hover:text-white text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-share-nodes text-[11px]"></i>
                        <span>Chia sẻ</span>
                    </button>
                    <button @click="useAsReference()"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/70 hover:text-white text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-image text-[11px]"></i>
                        <span>Dùng tham chiếu</span>
                    </button>
                    <button @click="copyPrompt()"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/70 hover:text-white text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-regular fa-copy text-[11px]"></i>
                        <span>Copy Prompt</span>
                    </button>
                    {{-- Reuse Prompt --}}
                    <button
                        @click="$wire.reusePrompt(previewImage?.id); closePreview(); notify('Đã nạp prompt + cài đặt')"
                        x-show="previewImage?.id"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 border border-purple-500/40 text-purple-300 hover:text-purple-200 text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-arrow-rotate-left text-[11px]"></i>
                        <span>Reuse Prompt</span>
                    </button>
                    {{-- Delete --}}
                    <button x-show="previewImage?.id"
                        @click="if(confirm('Bạn có chắc muốn xóa ảnh này?')) { $wire.deleteImage(previewImage?.id); closePreview(); notify('Đã xóa ảnh'); }"
                        class="inline-flex items-center gap-1.5 h-9 px-3.5 rounded-lg bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-400 hover:text-red-300 text-xs font-medium transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-trash text-[11px]"></i>
                        <span>Xóa</span>
                    </button>
                </div>
            </div>

            {{-- Dot Navigation --}}
            <div class="flex items-center gap-1.5" x-show="historyData.length > 1">
                @for ($dotSlot = 0; $dotSlot < 7; $dotSlot++)
                    <button x-show="previewDotAt({{ $dotSlot }})" @click="goToDot({{ $dotSlot }})"
                        :aria-label="previewDotLabel({{ $dotSlot }})" class="rounded-full transition-all duration-200"
                        :class="dotClassAt({{ $dotSlot }}, true)"></button>
                @endfor
            </div>
        </div>

        {{-- Next Arrow --}}
        <button @click="nextImage()" x-show="previewIndex < historyData.length - 1" aria-label="Ảnh tiếp"
            class="shrink-0 w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-[8px] text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1]">
            <i class="fa-solid fa-chevron-right text-lg"></i>
        </button>
        <div x-show="previewIndex >= historyData.length - 1" class="shrink-0 w-12"></div>
    </div>

    {{-- ============ MOBILE VIEW (sm:hidden) ============ --}}
    <div class="sm:hidden relative z-10 flex flex-col w-full h-[100dvh] overflow-hidden" x-show="showPreview" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0">

        {{-- Top bar --}}
        <div class="flex items-center justify-between px-4 py-3 safe-area-top">
            <span class="text-white/60 text-sm" x-text="(previewIndex + 1) + ' / ' + historyData.length"></span>
            <button @click="closePreview()" aria-label="Đóng xem trước"
                class="w-9 h-9 rounded-full bg-white/10 text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Image area (takes remaining space) --}}
        <div class="flex-1 min-h-0 flex items-center justify-center px-4 overflow-hidden">
            <img :src="previewImage?.url" :key="'mobile-preview-' + previewIndex"
                class="max-w-full max-h-full object-contain rounded-xl transition-opacity duration-200"
                :alt="previewImage?.prompt || 'Preview'"
                onerror="this.onerror=null; this.src='/images/placeholder.svg'">
        </div>

        {{-- Bottom info + actions --}}
        <div
            class="px-4 pb-4 safe-area-bottom max-h-[42vh] overflow-y-auto bg-gradient-to-t from-black/90 to-black/20 rounded-t-2xl">
            {{-- Prompt --}}
            <div x-data="{ expanded: false }" class="mb-3">
                <p class="text-white/80 text-sm leading-relaxed" :class="expanded ? '' : 'line-clamp-2'"
                    x-text="previewImage?.prompt || ''"></p>
                <button x-show="(previewImage?.prompt || '').length > 100" @click="expanded = !expanded"
                    class="text-purple-400 text-xs mt-1">
                    <span x-text="expanded ? 'Thu gọn' : 'Xem thêm'"></span>
                </button>
            </div>

            {{-- Dot navigation --}}
            <div class="flex justify-center gap-1.5 mb-3" x-show="historyData.length > 1">
                @for ($dotSlot = 0; $dotSlot < 7; $dotSlot++)
                    <button x-show="previewDotAt({{ $dotSlot }})" @click="goToDot({{ $dotSlot }})"
                        :aria-label="previewDotLabel({{ $dotSlot }})" class="rounded-full transition-all duration-200"
                        :class="dotClassAt({{ $dotSlot }}, false)"></button>
                @endfor
            </div>

            {{-- Action buttons (auto-fit grid) --}}
            <div class="flex flex-wrap justify-center gap-1.5">
                <button @click="downloadImage(previewImage?.url)"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-white/[0.05] border border-white/[0.08] text-white/70 active:scale-[0.95] transition-all">
                    <i class="fa-solid fa-download text-sm"></i>
                    <span class="text-[10px]">Tải xuống</span>
                </button>
                <button @click="shareImage()"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-white/[0.05] border border-white/[0.08] text-white/70 active:scale-[0.95] transition-all">
                    <i class="fa-solid fa-share-nodes text-sm"></i>
                    <span class="text-[10px]">Chia sẻ</span>
                </button>
                <button @click="useAsReference()"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-white/[0.05] border border-white/[0.08] text-white/70 active:scale-[0.95] transition-all">
                    <i class="fa-solid fa-image text-sm"></i>
                    <span class="text-[10px]">Tham chiếu</span>
                </button>
                <button @click="copyPrompt()"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-white/[0.05] border border-white/[0.08] text-white/70 active:scale-[0.95] transition-all">
                    <i class="fa-regular fa-copy text-sm"></i>
                    <span class="text-[10px]">Copy</span>
                </button>
                <button @click="$wire.reusePrompt(previewImage?.id); closePreview(); notify('Đã nạp prompt')"
                    x-show="previewImage?.id"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-purple-500/20 border border-purple-500/40 text-purple-300 active:scale-[0.95] transition-all">
                    <i class="fa-solid fa-arrow-rotate-left text-sm"></i>
                    <span class="text-[10px]">Reuse</span>
                </button>
                <button x-show="previewImage?.id"
                    @click="if(confirm('Bạn có chắc muốn xóa ảnh này?')) { $wire.deleteImage(previewImage?.id); closePreview(); notify('Đã xóa ảnh'); }"
                    class="flex flex-col items-center gap-1 py-3 px-3 min-w-[60px] rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 active:scale-[0.95] transition-all">
                    <i class="fa-solid fa-trash text-sm"></i>
                    <span class="text-[10px]">Xóa</span>
                </button>
            </div>
        </div>
    </div>
</div>