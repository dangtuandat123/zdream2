{{-- ============================================================ --}}
{{-- COMPOSER CARD — Core-first prompt + quick settings + status --}}
{{-- ============================================================ --}}
<div class="fixed left-0 right-0 md:left-[72px] z-[60] safe-area-bottom composer-fixed t2i-composer-wrap"
    style="bottom: calc(60px + env(safe-area-inset-bottom, 0px));"
    @click.away="showRatioSheet = false; showModelSheet = false; showBatchSheet = false; showRefPicker = false"
    x-ref="composerCard" x-init="
        const bar = $refs.composerCard;
        const ro = new ResizeObserver(() => {
            document.documentElement.style.setProperty('--composer-h', bar.offsetHeight + 'px');
        });
        ro.observe(bar);
        const stop = () => ro.disconnect();
        window.addEventListener('livewire:navigating', stop, { once: true });
    ">



    <div class="max-w-4xl mx-auto px-3 sm:px-4 pb-3 sm:pb-4 pt-2 relative">
        {{-- Scroll To Bottom Button (Mobile: Docked Top-Right / Desktop: Fixed Bottom-Right) --}}
        <button x-show="showScrollToBottom && !isFocused" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 translate-y-4"
            @click="scrollToBottom(true)"
            class="absolute -top-14 right-2 sm:fixed sm:top-auto sm:bottom-8 sm:right-8 z-[50] w-10 h-10 rounded-full bg-black/40 hover:bg-white/10 text-white/80 hover:text-white shadow-lg backdrop-blur-md border border-white/10 flex items-center justify-center transition-all active:scale-95 group"
            title="Cuộn xuống mới nhất">
            <i class="fa-solid fa-arrow-down group-hover:animate-bounce"></i>
        </button>

        {{-- Status Strip (above prompt, visible when not idle) --}}
        <template x-if="uiMode !== 'idle'">
            <div class="mb-2 rounded-xl overflow-hidden" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">

                {{-- Generating --}}
                <div x-show="uiMode === 'generating'"
                    class="flex items-center gap-3 px-4 py-3 bg-purple-500/10 border border-purple-500/20 rounded-xl">
                    <div
                        class="w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin shrink-0">
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white/90 text-sm font-medium" x-text="loadingMessages[currentLoadingMessage]">
                        </p>
                        <p class="text-white/40 text-xs mt-0.5">
                            <span
                                x-text="Math.floor(statusElapsed / 60) > 0 ? Math.floor(statusElapsed / 60) + ' phút ' : ''"></span>
                            <span x-text="(statusElapsed % 60) + ' giây'"></span>
                        </p>
                    </div>
                    <button wire:click="cancelGeneration"
                        class="shrink-0 h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-red-500/20 border border-white/[0.08] text-xs text-white/50 hover:text-red-400 transition-all active:scale-[0.95]">
                        <i class="fa-solid fa-xmark mr-1"></i>Hủy
                    </button>
                </div>

                {{-- Partial Success --}}
                <div x-show="uiMode === 'partial_success'"
                    class="flex items-center gap-3 px-4 py-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
                    <i class="fa-solid fa-triangle-exclamation text-yellow-400 shrink-0"></i>
                    <span class="text-white/80 text-sm flex-1" x-text="statusMessage"></span>
                    <button @click="uiMode = 'idle'" class="text-white/40 hover:text-white/70 text-xs">Đóng</button>
                </div>

                {{-- Failed --}}
                <div x-show="uiMode === 'failed'"
                    class="flex items-center gap-3 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                    <i class="fa-solid fa-circle-exclamation text-red-400 shrink-0"></i>
                    <span class="text-white/80 text-sm flex-1" x-text="statusMessage"></span>
                    <button @click="$wire.retry(); uiMode = 'generating'"
                        class="shrink-0 h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs text-white/70 transition-all active:scale-[0.95]">
                        <i class="fa-solid fa-redo mr-1"></i>Thử lại
                    </button>
                    <button @click="uiMode = 'idle'" class="text-white/40 hover:text-white/70 text-xs">Đóng</button>
                </div>

                {{-- Done --}}
                <div x-show="uiMode === 'done'"
                    class="flex items-center gap-3 px-4 py-3 bg-green-500/10 border border-green-500/20 rounded-xl">
                    <i class="fa-solid fa-check-circle text-green-400 shrink-0"></i>
                    <span class="text-white/80 text-sm flex-1" x-text="statusMessage"></span>
                    <button @click="uiMode = 'idle'" class="text-white/40 hover:text-white/70 text-xs">Đóng</button>
                </div>
            </div>
        </template>

        {{-- Composer main card --}}
        <div class="relative transition-all duration-300 ease-in-out"
            :class="!isAtBottom && !isFocused ? 'px-2 mb-2' : ''">
            <div class="relative flex flex-col gap-2.5 t2i-composer-card transition-all duration-500 ease-[cubic-bezier(0.32,0.72,0,1)]"
                :class="!isAtBottom && !isFocused ? 'mx-auto w-full max-w-3xl rounded-2xl p-2 gap-0 bg-black/40 backdrop-blur-[80px] border border-white/10 shadow-2xl' : 'w-full rounded-2xl p-3 sm:p-4 bg-[#151515]/90 backdrop-blur-md border border-white/10'">

                {{-- Prompt textarea --}}
                <div class="relative flex items-center gap-2 w-full">
                    <textarea x-ref="promptInput" wire:model.live.debounce.500ms="prompt" rows="1"
                        @focus="isFocused = true" @blur="isFocused = false" placeholder="Mô tả ý tưởng của bạn..."
                        class="t2i-prompt-input flex-1 bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-sm sm:text-base resize-none transition-all overflow-y-auto leading-[24px]"
                        :class="!isAtBottom && !isFocused ? 'min-h-[40px] h-[40px] px-3 py-2 text-white/70 placeholder:text-white/40' : 'min-h-[48px] max-h-[120px] px-0'"
                        x-init="
                            $watch('$wire.prompt', () => { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'; });
                        " @keydown.ctrl.enter.prevent="$wire.generate()" @keydown.meta.enter.prevent="$wire.generate()"
                        {{ $isGenerating ? 'disabled' : '' }}></textarea>

                    {{-- Mini Send Button (Shrunk only) --}}
                    <button type="button" x-show="!isAtBottom && !isFocused"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
                        @click="$wire.generate()" :disabled="$wire.prompt?.length === 0"
                        class="shrink-0 w-10 h-10 rounded-full bg-blue-600/90 text-white flex items-center justify-center shadow-lg hover:bg-blue-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-arrow-up text-base"></i>
                    </button>
                </div>

                {{-- Counter + hint --}}
                <div class="flex items-center justify-between -mt-1" x-show="$wire.prompt?.length > 0" x-cloak>
                    <span class="text-[11px] text-white/30"
                        :class="{ 'text-amber-400/70': $wire.prompt?.length > 1800, 'text-red-400/70': $wire.prompt?.length > 2000 }"
                        x-text="($wire.prompt?.length || 0) + ' / 2000'"></span>
                    <span class="text-[11px] text-white/20 hidden sm:inline">
                        <kbd class="px-1 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">Ctrl</kbd>
                        +
                        <kbd class="px-1 py-0.5 rounded bg-white/5 border border-white/10 text-[10px]">Enter</kbd>
                        để tạo
                    </span>
                </div>

                {{-- Quick Settings Row + Generate --}}
                <div class="flex items-center justify-between gap-2 transition-all duration-300 relative z-20"
                    :class="!isAtBottom && !isFocused ? 'max-h-0 opacity-0 -mt-1 overflow-hidden' : 'max-h-[60px] opacity-100 overflow-visible'">
                    <div class="flex items-center gap-1.5 flex-wrap"
                        @click.away="showRatioSheet = false; showModelSheet = false; showBatchSheet = false; showRefPicker = false">

                        {{-- ===== MODEL CHIP ===== --}}
                        <div class="relative">
                            <button type="button"
                                @click="showModelSheet = !showModelSheet; showRatioSheet = false; showBatchSheet = false; showRefPicker = false"
                                class="glass-chip flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-xs font-medium transition-all duration-200 cursor-pointer"
                                :class="showModelSheet ? 'glass-chip-active' : ''">
                                <span x-text="getSelectedModel().icon" class="text-sm"></span>
                                <span class="hidden sm:inline max-w-[100px] truncate"
                                    x-text="getSelectedModel().name"></span>
                                <span class="sm:hidden"
                                    x-text="getSelectedModel().shortLabel || getSelectedModel().name.split(' ').pop()"></span>
                            </button>

                            {{-- Model Dropdown Desktop --}}
                            <div x-show="showModelSheet" x-cloak x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="glass-popover hidden sm:block absolute bottom-full left-0 mb-2 w-72 p-2 rounded-xl z-[100]"
                                @click.stop>
                                <div class="space-y-1">
                                    <template x-for="model in models" :key="model.id">
                                        <button type="button" @click="selectModel(model.id)"
                                            class="w-full flex items-center gap-3 p-2.5 rounded-lg transition-all"
                                            :class="selectedModel === model.id ? 'bg-purple-500/20' : 'hover:bg-white/[0.06]'">
                                            <span class="text-lg shrink-0" x-text="model.icon"></span>
                                            <div class="text-left flex-1 min-w-0">
                                                <div class="text-white/90 text-sm font-medium" x-text="model.name">
                                                </div>
                                                <div class="text-white/40 text-[10px] truncate" x-text="model.desc">
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <template x-if="model.supportsImageInput">
                                                    <span
                                                        class="text-[9px] px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 font-medium"
                                                        x-text="'Ref ×' + model.maxImages"></span>
                                                </template>
                                                <i x-show="selectedModel === model.id"
                                                    class="fa-solid fa-check text-purple-400 text-xs"></i>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Model Bottom Sheet Mobile --}}
                            <div x-show="showModelSheet" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showModelSheet = false">
                                <div x-show="showModelSheet" @click.stop
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                                    class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh]">
                                    <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Chọn Model AI</span>
                                        <button type="button" @click="showModelSheet = false"
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
                                                        <div class="text-white font-semibold text-base"
                                                            x-text="model.name"></div>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-white/50 text-sm"
                                                                x-text="model.desc"></span>
                                                            <template x-if="model.supportsImageInput">
                                                                <span
                                                                    class="text-[10px] px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 font-medium"
                                                                    x-text="'Ref ×' + model.maxImages"></span>
                                                            </template>
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
                                </div>
                            </div>
                        </div>

                        {{-- ===== RATIO CHIP ===== --}}
                        <div class="relative">
                            <button type="button"
                                @click="showRatioSheet = !showRatioSheet; showModelSheet = false; showBatchSheet = false; showRefPicker = false"
                                class="glass-chip flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-xs font-medium transition-all duration-200 cursor-pointer"
                                :class="showRatioSheet ? 'glass-chip-active' : ''">
                                <i class="fa-solid fa-crop text-[11px]"></i>
                                <span x-text="selectedRatio === 'auto' ? 'Auto' : selectedRatio"></span>
                            </button>

                            {{-- Ratio Dropdown Desktop --}}
                            <div x-show="showRatioSheet" x-cloak x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="glass-popover hidden sm:block absolute bottom-full left-0 mb-2 w-80 p-3 rounded-xl z-[100]"
                                @click.stop>
                                <div class="text-white/50 text-xs font-medium mb-2">Tỉ lệ khung hình</div>
                                <div class="grid grid-cols-5 gap-1.5">
                                    <template x-for="ratio in ratios" :key="ratio.id">
                                        <button type="button" @click="selectRatio(ratio.id)"
                                            class="flex flex-col items-center gap-1 p-2 rounded-lg transition-all"
                                            :class="selectedRatio === ratio.id ? 'bg-purple-500/20 border border-purple-500/50' : 'bg-white/[0.03] hover:bg-white/[0.06] border border-transparent'">
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

                            {{-- Ratio Bottom Sheet Mobile --}}
                            <div x-show="showRatioSheet" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showRatioSheet = false">
                                <div x-show="showRatioSheet" @click.stop
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                                    class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh]">
                                    <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Tỉ lệ khung hình</span>
                                        <button type="button" @click="showRatioSheet = false"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="p-4 overflow-y-auto">
                                        <div class="grid grid-cols-4 gap-2">
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
                                                            <div class="border-2 border-white/40 rounded-sm" :style="{
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== BATCH CHIP ===== --}}
                        <div class="relative">
                            <button type="button"
                                @click="showBatchSheet = !showBatchSheet; showRatioSheet = false; showModelSheet = false; showRefPicker = false"
                                class="glass-chip flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-xs font-medium transition-all duration-200 cursor-pointer"
                                :class="showBatchSheet ? 'glass-chip-active' : ''">
                                <i class="fa-solid fa-layer-group text-[11px]"></i>
                                <span x-text="'×' + $wire.batchSize"></span>
                            </button>

                            {{-- Batch Dropdown Desktop --}}
                            <div x-show="showBatchSheet" x-cloak @click.away="showBatchSheet = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="glass-popover hidden sm:block absolute bottom-full left-0 mb-2 w-36 p-1.5 rounded-xl z-[100]"
                                @click.stop>
                                <div class="text-white/50 text-xs font-medium mb-1.5 px-2">Số lượng ảnh</div>
                                <div class="space-y-0.5">
                                    @foreach([1, 2, 3, 4] as $n)
                                        <button type="button"
                                            @click="$wire.$set('batchSize', {{ $n }}); showBatchSheet = false"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors"
                                            :class="$wire.batchSize === {{ $n }} ? 'bg-purple-500/20 text-white' : 'text-white/70 hover:bg-white/[0.06]'">
                                            <span>{{ $n }} ảnh</span>
                                            <i x-show="$wire.batchSize === {{ $n }}"
                                                class="fa-solid fa-check text-purple-400 text-xs"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Batch Bottom Sheet Mobile --}}
                            <div x-show="showBatchSheet" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showBatchSheet = false" @click.stop>
                                <div x-show="showBatchSheet" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                                    class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[85vh]">
                                    <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Số lượng ảnh</span>
                                        <button type="button" @click="showBatchSheet = false"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach([1, 2, 3, 4] as $n)
                                                <button type="button"
                                                    @click="$wire.$set('batchSize', {{ $n }}); showBatchSheet = false"
                                                    class="flex flex-col items-center gap-1.5 p-4 rounded-xl transition-all"
                                                    :class="$wire.batchSize === {{ $n }} ? 'bg-purple-500/30 border border-purple-500/50' : 'bg-white/5 active:bg-white/10 border border-transparent'">
                                                    <span class="text-white text-2xl font-bold">{{ $n }}</span>
                                                    <span class="text-white/60 text-xs">ảnh</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ===== REFS CHIP ===== --}}
                        <div class="relative">
                            <button type="button"
                                @click="if (maxImages > 0) { showRefPicker = !showRefPicker; showModelSheet = false; showRatioSheet = false; showBatchSheet = false; if(showRefPicker) loadRecentImages(); }"
                                class="glass-chip flex items-center gap-1.5 h-8 px-2.5 rounded-lg text-xs font-medium transition-all duration-200"
                                :class="maxImages === 0
                                    ? 'bg-white/[0.02] border border-white/[0.05] text-white/30 cursor-not-allowed'
                                    : (selectedImages.length > 0
                                        ? 'glass-chip-active cursor-pointer'
                                        : 'cursor-pointer')"
                                :title="maxImages === 0 ? 'Model này không hỗ trợ ảnh tham chiếu' : ''">
                                <template x-if="selectedImages.length > 0">
                                    <div class="flex items-center gap-1">
                                        <div class="flex -space-x-1">
                                            <template x-for="(img, idx) in selectedImages.slice(0, 3)" :key="img.id">
                                                <img :src="img.url"
                                                    class="w-5 h-5 rounded border border-purple-500/50 object-cover">
                                            </template>
                                        </div>
                                        <span x-text="selectedImages.length"></span>
                                    </div>
                                </template>
                                <template x-if="selectedImages.length === 0">
                                    <span class="flex items-center gap-1">
                                        <i class="fa-solid fa-image text-[11px]"></i>
                                        <span class="hidden sm:inline">Refs</span>
                                    </span>
                                </template>
                            </button>
                            {{-- Clear refs badge --}}
                            <button x-show="selectedImages.length > 0" @click.stop="clearAll()"
                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors z-10">
                                <i class="fa-solid fa-xmark"></i>
                            </button>

                            {{-- Inline Ref Picker (desktop dropdown / mobile sheet) --}}
                            {{-- Desktop --}}
                            <div x-show="showRefPicker && maxImages > 0" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="glass-popover hidden sm:block absolute bottom-full right-0 mb-2 w-80 p-3 rounded-xl z-[100]"
                                @click.stop>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-white/50 text-xs font-medium">Ảnh tham chiếu <span
                                            x-text="selectedImages.length + '/' + maxImages"
                                            class="text-purple-300"></span></span>
                                </div>

                                {{-- Upload zone --}}
                                <label
                                    class="flex items-center justify-center gap-2 p-3 rounded-lg border-2 border-dashed border-white/[0.1] hover:border-purple-500/40 text-white/50 hover:text-purple-300 text-xs cursor-pointer transition-all mb-2">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <span>Chọn hoặc kéo thả ảnh</span>
                                    <input type="file" accept="image/*" multiple class="hidden"
                                        @change="handleFileSelect($event)">
                                </label>

                                {{-- URL input --}}
                                <div class="flex gap-1.5 mb-2">
                                    <input type="text" x-model="urlInput" placeholder="Dán URL ảnh..."
                                        class="flex-1 h-8 px-2.5 rounded-lg bg-white/[0.05] border border-white/[0.08] text-white text-xs focus:outline-none focus:border-purple-500/40"
                                        @keydown.enter.prevent="addFromUrl()">
                                    <button @click="addFromUrl()"
                                        class="h-8 px-2.5 rounded-lg bg-purple-500/20 text-purple-300 text-xs hover:bg-purple-500/30 transition-colors">Thêm</button>
                                </div>

                                {{-- Recent images --}}
                                <div x-show="recentImages.length > 0" class="mt-2">
                                    <div class="text-white/40 text-[10px] font-medium mb-1.5">Ảnh gần đây</div>
                                    <div class="grid grid-cols-5 gap-1 max-h-32 overflow-y-auto">
                                        <template x-for="img in recentImages.slice(0, 15)" :key="img.id || img.url">
                                            <button @click="selectFromRecent(img.url)"
                                                class="relative aspect-square rounded-lg overflow-hidden border transition-all"
                                                :class="isSelected(img.url) ? 'border-purple-500 ring-1 ring-purple-500' : 'border-transparent hover:border-white/20'">
                                                <img :src="img.url" class="w-full h-full object-cover">
                                                <div x-show="isSelected(img.url)"
                                                    class="absolute inset-0 bg-purple-500/30 flex items-center justify-center">
                                                    <i class="fa-solid fa-check text-white text-xs"></i>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Selected preview --}}
                                <div x-show="selectedImages.length > 0" class="mt-2 flex gap-1.5 flex-wrap">
                                    <template x-for="img in selectedImages" :key="img.id">
                                        <div class="relative w-10 h-10 rounded-lg overflow-hidden group">
                                            <img :src="img.url" class="w-full h-full object-cover">
                                            <button @click="removeImage(img.id)"
                                                class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                <i class="fa-solid fa-xmark text-white text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Mobile Ref Sheet --}}
                            <div x-show="showRefPicker && maxImages > 0" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showRefPicker = false" @click.stop>
                                <div x-show="showRefPicker" x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
                                    class="glass-popover w-full max-w-lg rounded-t-3xl flex flex-col max-h-[80vh]">
                                    <div class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Ảnh tham chiếu <span
                                                x-text="selectedImages.length + '/' + maxImages"
                                                class="text-purple-300 text-sm"></span></span>
                                        <button type="button" @click="showRefPicker = false"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="p-4 overflow-y-auto">
                                        {{-- Upload --}}
                                        <label
                                            class="flex items-center justify-center gap-2 p-4 rounded-xl border-2 border-dashed border-white/[0.1] active:border-purple-500/40 text-white/50 text-sm cursor-pointer transition-all mb-3">
                                            <i class="fa-solid fa-cloud-arrow-up text-lg"></i>
                                            <span>Chọn ảnh từ thiết bị</span>
                                            <input type="file" accept="image/*" multiple class="hidden"
                                                @change="handleFileSelect($event)">
                                        </label>

                                        {{-- URL --}}
                                        <div class="flex gap-2 mb-3">
                                            <input type="text" x-model="urlInput" placeholder="Dán URL ảnh..."
                                                class="flex-1 h-10 px-3 rounded-xl bg-white/[0.05] border border-white/[0.08] text-white text-sm focus:outline-none focus:border-purple-500/40"
                                                @keydown.enter.prevent="addFromUrl()">
                                            <button @click="addFromUrl()"
                                                class="h-10 px-4 rounded-xl bg-purple-500/20 text-purple-300 text-sm hover:bg-purple-500/30 transition-colors">Thêm</button>
                                        </div>

                                        {{-- Recent --}}
                                        <div x-show="recentImages.length > 0">
                                            <div class="text-white/40 text-xs font-medium mb-2">Ảnh gần đây</div>
                                            <div class="grid grid-cols-4 gap-1.5 max-h-40 overflow-y-auto">
                                                <template x-for="img in recentImages.slice(0, 16)"
                                                    :key="img.id || img.url">
                                                    <button @click="selectFromRecent(img.url)"
                                                        class="relative aspect-square rounded-xl overflow-hidden border-2 transition-all"
                                                        :class="isSelected(img.url) ? 'border-purple-500' : 'border-transparent'">
                                                        <img :src="img.url" class="w-full h-full object-cover">
                                                        <div x-show="isSelected(img.url)"
                                                            class="absolute inset-0 bg-purple-500/30 flex items-center justify-center">
                                                            <i class="fa-solid fa-check text-white"></i>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Selected --}}
                                        <div x-show="selectedImages.length > 0" class="mt-3">
                                            <div class="text-white/40 text-xs font-medium mb-2">Đã chọn</div>
                                            <div class="flex gap-2 flex-wrap">
                                                <template x-for="img in selectedImages" :key="img.id">
                                                    <div class="relative w-14 h-14 rounded-xl overflow-hidden">
                                                        <img :src="img.url" class="w-full h-full object-cover">
                                                        <button @click="removeImage(img.id)"
                                                            class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-black/70 flex items-center justify-center">
                                                            <i class="fa-solid fa-xmark text-white text-[10px]"></i>
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Generate Button --}}
                    @if($isGenerating)
                        <button type="button" wire:click="cancelGeneration"
                            class="t2i-cancel-btn shrink-0 flex items-center gap-2 px-4 sm:px-5 py-2 rounded-xl text-white font-semibold text-sm active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-stop text-xs"></i>
                            <span>Hủy</span>
                        </button>
                    @else
                        <button type="button" @click="$wire.generate()"
                            class="t2i-generate-btn shrink-0 flex items-center gap-1.5 px-3 sm:px-5 py-2 rounded-xl text-white font-semibold text-sm active:scale-[0.98] transition-colors duration-200"
                            :disabled="!$wire.prompt?.trim() || uiMode === 'generating'"
                            :class="{ 'opacity-40 pointer-events-none': !$wire.prompt?.trim() || uiMode === 'generating' }"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                            wire:target="generate">
                            <span x-show="uiMode !== 'generating'" wire:loading.remove wire:target="generate"><i
                                    class="fa-solid fa-wand-magic-sparkles text-xs"></i></span>
                            <span x-show="uiMode === 'generating'" wire:loading wire:target="generate"><i
                                    class="fa-solid fa-spinner fa-spin text-xs"></i></span>
                            <span>Tạo</span>
                            <span class="text-white/60 text-[11px] font-normal"><span
                                    x-text="$wire.creditCost * $wire.batchSize"></span> cr</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>