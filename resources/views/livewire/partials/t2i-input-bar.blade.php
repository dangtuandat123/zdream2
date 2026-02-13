{{-- ============================================================ --}}
{{-- FIXED INPUT BAR (inside textToImage Alpine scope) --}}
{{-- ============================================================ --}}
<div class="fixed left-0 right-0 md:left-[72px] z-[60] safe-area-bottom input-bar-fixed"
    style="bottom: calc(60px + env(safe-area-inset-bottom, 0px));"
    @click.away="showRatioDropdown = false; showModelDropdown = false; showBatchDropdown = false"
    @show-toast.window="notify($event.detail.message)"
    x-ref="inputBar"
    x-init="
        const bar = $refs.inputBar;
        const ro = new ResizeObserver(() => {
            document.documentElement.style.setProperty('--input-bar-h', bar.offsetHeight + 'px');
        });
        ro.observe(bar);
        $cleanup(() => ro.disconnect());
    ">

    <div class="max-w-4xl mx-auto px-3 sm:px-4 pb-3 sm:pb-4 pt-3">
        <div class="relative">
            {{-- Glow effect --}}
            <div
                class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-20 blur-md sm:blur-lg transition-opacity duration-500">
            </div>

            {{-- Input container --}}
            <div
                class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/50 backdrop-blur-2xl border border-white/15 shadow-2xl">

                {{-- Textarea --}}
                <textarea x-ref="promptInput" wire:model.live.debounce.500ms="prompt" rows="2"
                    placeholder="Mô tả ý tưởng của bạn..."
                    class="w-full min-h-[48px] max-h-[160px] bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/40 text-sm sm:text-base resize-none focus:placeholder-white/60 transition-all overflow-y-auto"
                    x-init="
                        $watch('$wire.prompt', () => { $el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 160) + 'px'; });
                        if (window.innerWidth >= 768) { $nextTick(() => $el.focus()); }
                    " @keydown.ctrl.enter.prevent="$wire.generate()" @keydown.meta.enter.prevent="$wire.generate()"
                    {{ $isGenerating ? 'disabled' : '' }}></textarea>

                {{-- Character counter + keyboard hint --}}
                <div class="flex items-center justify-between -mt-1 mb-1" x-show="$wire.prompt?.length > 0">
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

                {{-- Bottom row: icons + button --}}
                <div class="flex items-center justify-between gap-2 sm:gap-3">
                    <div class="flex items-center gap-2"
                        @click.away="showRatioDropdown = false; showModelDropdown = false; showBatchDropdown = false">

                        {{-- Image Reference Picker --}}
                        <div class="relative" x-show="maxImages > 0" x-cloak>
                            <button type="button"
                                @click="showImagePicker = !showImagePicker; if(showImagePicker) loadRecentImages()"
                                class="flex items-center gap-1.5 h-9 px-2.5 rounded-lg transition-all duration-200 cursor-pointer"
                                :class="selectedImages.length > 0
                                    ? 'bg-purple-500/20 border border-purple-500/50'
                                    : 'bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]'">
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
                            <button x-show="selectedImages.length > 0" @click.stop="clearAll()"
                                class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] flex items-center justify-center hover:bg-red-600 transition-colors">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        {{-- Aspect Ratio Button --}}
                        <div class="relative">
                            <button type="button" data-dropdown-trigger="ratio"
                                @click="showRatioDropdown = !showRatioDropdown; showModelDropdown = false"
                                class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                :class="{ 'bg-purple-500/20 border-purple-500/40': showRatioDropdown }">
                                <i class="fa-solid fa-crop text-white/50 text-sm"></i>
                                <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                    x-text="selectedRatio === 'auto' ? 'Tỉ lệ' : selectedRatio"></span>
                                <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform hidden sm:inline"
                                    :class="{ 'rotate-180': showRatioDropdown }"></i>
                            </button>

                            {{-- Ratio Dropdown - Desktop (no teleport) --}}
                            <div x-show="showRatioDropdown" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2"
                                class="hidden sm:block absolute bottom-full left-0 mb-2 w-80 p-3 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[100]"
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

                            {{-- Ratio Bottom Sheet - Mobile (no teleport) --}}
                            <div x-show="showRatioDropdown" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showRatioDropdown = false" @click.stop>
                                <div x-show="showRatioDropdown"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full"
                                    x-transition:enter-end="translate-y-0"
                                    class="w-full max-w-lg bg-[#0f0f18]/95 backdrop-blur-[24px] saturate-[180%] border-t border-white/[0.1] rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                    <div
                                        class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Tùy chỉnh khung
                                            hình</span>
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
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Model Selector --}}
                        <div class="relative">
                            <button type="button" data-dropdown-trigger="model"
                                @click="showModelDropdown = !showModelDropdown; showRatioDropdown = false"
                                class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                :class="{ 'bg-purple-500/20 border-purple-500/40': showModelDropdown }">
                                <i class="fa-solid fa-microchip text-white/50 text-sm"></i>
                                <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                    x-text="getSelectedModel().name"></span>
                                <i class="fa-solid fa-chevron-down text-white/40 text-[10px] transition-transform hidden sm:inline"
                                    :class="{ 'rotate-180': showModelDropdown }"></i>
                            </button>

                            {{-- Model Dropdown - Desktop (no teleport) --}}
                            <div x-show="showModelDropdown" x-cloak
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2"
                                class="hidden sm:block absolute bottom-full left-0 mb-2 w-64 p-2 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[100]"
                                @click.stop>
                                <div class="space-y-1">
                                    <template x-for="model in models" :key="model.id">
                                        <button type="button" @click="selectModel(model.id)"
                                            class="w-full flex items-center gap-3 p-2 rounded-lg transition-all"
                                            :class="selectedModel === model.id ? 'bg-purple-500/20' : 'hover:bg-white/[0.06]'">
                                            <span class="text-lg" x-text="model.icon"></span>
                                            <div class="text-left">
                                                <div class="text-white/90 text-sm font-medium"
                                                    x-text="model.name"></div>
                                                <div class="text-white/40 text-[10px]" x-text="model.desc">
                                                </div>
                                            </div>
                                            <i x-show="selectedModel === model.id"
                                                class="fa-solid fa-check text-purple-400 text-xs ml-auto"></i>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Model Bottom Sheet - Mobile (no teleport) --}}
                            <div x-show="showModelDropdown" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showModelDropdown = false" @click.stop>
                                <div x-show="showModelDropdown"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full"
                                    x-transition:enter-end="translate-y-0"
                                    class="w-full max-w-lg bg-[#0f0f18]/95 backdrop-blur-[24px] saturate-[180%] border-t border-white/[0.1] rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                    <div
                                        class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
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
                                                        <div class="text-white font-semibold text-base"
                                                            x-text="model.name"></div>
                                                        <div class="text-white/50 text-sm mt-0.5"
                                                            x-text="model.desc"></div>
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

                        {{-- Batch Size Selector --}}
                        <div class="relative">
                            <button type="button" data-dropdown-trigger="batch"
                                @click="showBatchDropdown = !showBatchDropdown; showRatioDropdown = false; showModelDropdown = false"
                                class="flex items-center gap-1.5 h-9 px-2 sm:px-2.5 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] transition-all duration-200 cursor-pointer"
                                :class="{ 'bg-purple-500/20 border-purple-500/40': showBatchDropdown }">
                                <i class="fa-solid fa-layer-group text-white/50 text-sm"></i>
                                <span class="text-white/70 text-xs font-medium hidden sm:inline"
                                    x-text="'Số lượng: ' + $wire.batchSize"></span>
                                <span class="text-white/70 text-xs font-medium sm:hidden"
                                    x-text="'x' + $wire.batchSize"></span>
                            </button>

                            {{-- Batch Dropdown - Desktop (no teleport) --}}
                            <div x-show="showBatchDropdown" x-cloak @click.away="showBatchDropdown = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-2"
                                class="hidden sm:block absolute bottom-full left-0 mb-2 w-36 p-1.5 rounded-xl bg-[#0f0f18]/95 backdrop-blur-[20px] saturate-[180%] border border-white/[0.1] shadow-2xl shadow-black/50 z-[100]"
                                @click.stop>
                                <div class="text-white/50 text-xs font-medium mb-1.5 px-2">Số lượng ảnh</div>
                                <div class="space-y-0.5">
                                    @foreach([1, 2, 3, 4] as $n)
                                        <button type="button"
                                            @click="$wire.$set('batchSize', {{ $n }}); showBatchDropdown = false"
                                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors"
                                            :class="$wire.batchSize === {{ $n }} ? 'bg-purple-500/20 text-white' : 'text-white/70 hover:bg-white/[0.06]'">
                                            <span>{{ $n }} ảnh</span>
                                            <i x-show="$wire.batchSize === {{ $n }}"
                                                class="fa-solid fa-check text-purple-400 text-xs"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Batch Bottom Sheet - Mobile (no teleport) --}}
                            <div x-show="showBatchDropdown" x-cloak
                                class="sm:hidden fixed inset-0 z-[9999] flex items-end justify-center bg-black/80 backdrop-blur-md"
                                @click.self="showBatchDropdown = false" @click.stop>
                                <div x-show="showBatchDropdown"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="translate-y-full"
                                    x-transition:enter-end="translate-y-0"
                                    class="w-full max-w-lg bg-[#0f0f18]/95 backdrop-blur-[24px] saturate-[180%] border-t border-white/[0.1] rounded-t-3xl flex flex-col max-h-[85vh] shadow-[0_-10px_40px_rgba(0,0,0,0.5)]">
                                    <div
                                        class="flex items-center justify-between p-4 border-b border-white/5 shrink-0">
                                        <span class="text-white font-semibold text-base">Số lượng ảnh</span>
                                        <button type="button" @click="showBatchDropdown = false"
                                            class="w-8 h-8 flex items-center justify-center rounded-full bg-white/5 text-white/60 active:scale-95 transition-transform">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach([1, 2, 3, 4] as $n)
                                                <button type="button"
                                                    @click="$wire.$set('batchSize', {{ $n }}); showBatchDropdown = false"
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

                    </div>

                    {{-- Generate Button --}}
                    @if($isGenerating)
                        <button type="button" wire:click="cancelGeneration"
                            class="shrink-0 flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl bg-red-500/80 hover:bg-red-500 text-white font-semibold text-sm shadow-lg shadow-red-500/25 active:scale-[0.98] transition-all duration-200">
                            <i class="fa-solid fa-stop text-sm"></i>
                            <span>Hủy</span>
                        </button>
                    @else
                        <button type="button" wire:click="generate"
                            class="shrink-0 flex items-center gap-1.5 sm:gap-2 px-3 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm shadow-lg shadow-purple-500/25 hover:scale-[1.02] hover:shadow-xl hover:shadow-purple-500/40 active:scale-[0.98] transition-all duration-200"
                            wire:loading.attr="disabled" wire:loading.class="opacity-50 pointer-events-none"
                            wire:target="generate">
                            <span wire:loading.remove wire:target="generate"><i
                                    class="fa-solid fa-wand-magic-sparkles text-sm"></i></span>
                            <span wire:loading wire:target="generate"><i
                                    class="fa-solid fa-spinner fa-spin text-sm"></i></span>
                            <span class="hidden sm:inline">Tạo ảnh</span>
                            <span class="text-white/60 text-[11px] sm:text-xs font-normal"><span x-text="$wire.creditCost * $wire.batchSize"></span> <span
                                    class="hidden sm:inline">credits</span><span class="sm:hidden">cr</span></span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
