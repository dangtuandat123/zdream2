{{-- ============================================================ --}}
{{-- GALLERY FEED — Clean rewrite with reliable infinite scroll --}}
{{-- ============================================================ --}}
<div id="gallery-scroll" class="t2i-gallery-shell">

    {{-- Fixed loading indicator — always visible during history load --}}
    <div x-show="loadingMoreHistory" wire:ignore
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed mobile-filter-top md:top-0 left-0 right-0 md:left-[72px] z-[60] pointer-events-none"
        style="display:none; margin-top: var(--filter-bar-h, 2.5rem);">
        {{-- Shimmer bar --}}
        <div class="h-1 w-full bg-gradient-to-r from-transparent via-purple-500/80 to-transparent animate-pulse"></div>
        {{-- Floating pill --}}
        <div class="flex justify-center mt-3">
            <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/10 shadow-lg">
                <svg class="w-3.5 h-3.5 animate-spin text-purple-400" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.5" opacity="0.25"/>
                    <path d="M22 12a10 10 0 01-10 10" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
                <span class="text-white/60 text-[11px] font-medium">Đang tải...</span>
            </div>
        </div>
    </div>
    {{-- Top Spacer: filter-bar-h + gallery-gap --}}
    <div class="w-full shrink-0 pointer-events-none" style="height: calc(var(--filter-bar-h, 3.5rem) + var(--gallery-gap, 12px));"></div>

    <div class="max-w-4xl mx-auto px-4 w-full relative z-10">

        {{-- Error Banner --}}
        @if($errorMessage)
            <div x-data="{ show: true }" x-show="show" x-cloak
                class="mb-4 p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm flex items-center gap-3"
                role="alert">
                <i class="fa-solid fa-circle-exclamation shrink-0"></i>
                <span class="flex-1">{{ $errorMessage }}</span>
                @if($lastPrompt)
                    <button wire:click="retry"
                        class="shrink-0 h-8 px-3 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs font-medium text-white/80 transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-redo mr-1"></i>Thử lại
                    </button>
                @endif
                <button @click="show = false"
                    class="shrink-0 h-8 w-8 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-white/80 flex items-center justify-center transition-all active:scale-[0.98]">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        @endif

        {{-- Gallery Feed --}}
        <div class="flex flex-col gap-5 sm:gap-6 px-0 sm:px-1" id="gallery-feed"
            data-history='@json($flatHistoryForJs)'
            data-has-more="{{ ($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasMorePages()) ? '1' : '0' }}"
            wire:key="gallery-feed">

            @php $absoluteIndex = 0; @endphp

            <div class="space-y-5 sm:space-y-6 gallery-wrapper">

                {{-- ═══════════════════════════════════════════ --}}
                {{-- TOP SENTINEL — auto-load older history --}}
                {{-- ═══════════════════════════════════════════ --}}
                @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasMorePages())
                    <div id="load-older-sentinel" class="h-px"
                        x-show="hasMoreHistory || loadingMoreHistory" x-cloak>
                    </div>
                    {{-- End of history indicator --}}
                    <div x-show="!hasMoreHistory && !loadingMoreHistory" x-cloak
                        class="flex items-center justify-center py-3">
                        <span class="text-white/25 text-xs">Đã hiển thị tất cả ảnh</span>
                    </div>
                @endif

                {{-- ═══════════════════════════════════════════ --}}
                {{-- GROUPED BATCHES --}}
                {{-- ═══════════════════════════════════════════ --}}
                @php $totalGroups = $groupedHistory->count();
                $groupIdx = 0; @endphp
                @forelse($groupedHistory as $groupKey => $groupItems)
                    @php $wireKey = md5($groupKey); @endphp
                    @php
                        $firstItem = $groupItems->first();
                        $modelId = $firstItem->generation_params['model_id'] ?? null;
                        $ratio = $firstItem->generation_params['aspect_ratio_user'] ?? $firstItem->generation_params['aspect_ratio'] ?? null;

                        $modelName = $modelId;
                        if ($modelId && isset($availableModels)) {
                            $found = collect($availableModels)->firstWhere('id', $modelId);
                            $modelName = $found['name'] ?? $modelId;
                        }

                        $aspectRatioCss = null;
                        $outW = $firstItem->generation_params['output_width'] ?? null;
                        $outH = $firstItem->generation_params['output_height'] ?? null;
                        if ($outW && $outH) {
                            $aspectRatioCss = $outW . ' / ' . $outH;
                        } elseif ($ratio && $ratio !== 'auto' && $ratio !== 'Auto' && strpos($ratio, ':') !== false) {
                            [$w, $h] = explode(':', $ratio);
                            $aspectRatioCss = $w . ' / ' . $h;
                        }
                        if (!$aspectRatioCss) {
                            $aspectRatioCss = '1 / 1';
                        }
                        $ratioDisplay = $ratio ?: 'Auto';
                    @endphp

                    <div class="group-batch t2i-batch relative mb-5"
                        x-data="{ expanded: false }"
                        wire:key="group-{{ $wireKey }}" data-history-anchor-id="{{ $firstItem->id }}">

                        {{-- ── Card Header ── --}}
                        <div class="px-0 pb-2">
                            <div class="flex flex-col sm:flex-row sm:items-baseline gap-2 sm:gap-4">
                                {{-- Prompt --}}
                                <!-- Prompt (Truncated) -->
                                <button class="text-[15px] font-semibold leading-snug text-left text-white/90 hover:text-white transition-colors duration-200 cursor-pointer line-clamp-2 break-words"
                                    @click="expanded = !expanded" title="Nhấn xem chi tiết">
                                    {{ $prompt }}
                                </button>
                                
                                {{-- Metadata --}}
                                <div class="flex items-center gap-3 text-[12px] text-white/40 font-medium shrink-0">
                                    <span>{{ $modelName }}</span>
                                    <span class="w-[1px] h-3 bg-white/10"></span>
                                    <span>{{ $ratioDisplay }}</span>
                                    <span class="w-[1px] h-3 bg-white/10"></span>
                                    <span>{{ $groupItems->count() }} ảnh</span>
                                    <span class="w-[1px] h-3 bg-white/10"></span>
                                    <span>{{ $firstItem->created_at->diffForHumans() }}</span>
                                    
                                    {{-- Actions (Contextual) --}}
                                    <button wire:click="reusePrompt({{ $firstItem->id }})" class="ml-2 hover:text-white/80 transition-colors" title="Reuse">
                                        <i class="fa-solid fa-arrow-rotate-left"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- ── Expanded Prompt Detail ── --}}
                        <div x-show="expanded" x-cloak 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                            x-transition:enter-end="opacity-100 translate-y-0 max-h-[500px]"
                            class="px-0 pb-2">
                            <div class="p-3 rounded-lg bg-white/[0.03] border border-white/[0.06] text-[13px] text-white/70 leading-relaxed break-words">
                                {{ $prompt }}
                            </div>
                        </div>

                        {{-- ── Image Grid ── --}}
                        <div class="px-0">
                            @php
                                $imgCount = $groupItems->count();
                                $ratioDec = 1;
                                if ($imgCount === 1) {
                                    $parts = explode('/', str_replace(' / ', '/', $aspectRatioCss));
                                    $w = floatval(trim($parts[0] ?? 1)) ?: 1;
                                    $h = floatval(trim($parts[1] ?? 1)) ?: 1;
                                    $ratioDec = $h > 0 ? ($w / $h) : 1;
                                }
                                $gridClass = $imgCount === 1 ? 'flex justify-center w-full' : ($imgCount === 2 ? 'grid grid-cols-2 w-full max-w-xl mx-auto' : 'grid grid-cols-2 sm:grid-cols-4 w-full');
                            @endphp
                            <div class="gap-3 {{ $gridClass }}">
                                @foreach($groupItems as $image)
                                    @php
                                        $isNewestGroup = $groupIdx === $totalGroups - 1;
                                        $isPriorityImage = $isNewestGroup && $loop->index < 2;
                                        $itemImgUrl = $image->image_url;
                                    @endphp
                                    <div class="block group/img cursor-pointer relative" wire:key="img-{{ $image->id }}"
                                        @click="openPreview(null, {{ $absoluteIndex }})"
                                        x-data="{ loaded: false }"
                                        x-init="$nextTick(() => { if ($refs.imgElem && $refs.imgElem.complete) loaded = true; })"
                                        style="{{ $imgCount === 1 ? 'width: 100%; max-width: min(100%, 28rem, calc(var(--img-safe-h, 480px) * ' . $ratioDec . '));' : 'width: 100%;' }}">
                                        
                                        <div class="relative overflow-hidden bg-[#1c1d21] flex items-center justify-center rounded-xl border border-white/5 shadow-inner w-full"
                                            style="{{ $imgCount === 1 ? 'aspect-ratio: ' . $aspectRatioCss . '; max-height: var(--img-safe-h, 480px);' : 'aspect-ratio: 1 / 1;' }}">
                                            {{-- Shimmer --}}
                                            <div class="img-shimmer absolute inset-0 bg-[#333] overflow-hidden transition-opacity duration-300"
                                                 :class="loaded ? 'opacity-0 pointer-events-none' : 'opacity-100'">
                                                <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/5 to-transparent animate-[shimmer_2s_infinite]"></div>
                                            </div>
                                            
                                            {{-- Image --}}
                                            @if($itemImgUrl)
                                                <img x-ref="imgElem" src="{{ $itemImgUrl }}" alt="Preview"
                                                    class="gallery-img w-full h-full {{ $imgCount === 1 ? 'object-contain' : 'object-cover' }} rounded-xl cursor-pointer transform transition-transform duration-500 group-hover:scale-105"
                                                    draggable="false"
                                                    x-on:load="loaded = true"
                                                    x-on:error="loaded = true; $el.src='/images/placeholder.svg'"
                                                    {{ $isPriorityImage ? 'loading=eager fetchpriority=high decoding=async' : 'loading=lazy fetchpriority=low decoding=async' }}>
                                            @else
                                                <img x-ref="imgElem" src="/images/placeholder.svg" alt="Error"
                                                    class="gallery-img w-full h-full object-contain rounded-xl cursor-pointer transform transition-transform duration-500 group-hover:scale-105"
                                                    draggable="false"
                                                    x-on:load="loaded = true"
                                                    x-on:error="loaded = true"
                                                    loading="lazy">
                                            @endif

                                            {{-- Actions Overlay (Desktop: Hover / Mobile: Tap or Always visible?) --}}
                                            {{-- Decision: Top-Right for better standard. Mobile: Always visible but subtle. Desktop: Hover. --}}
                                            
                                            {{-- Unified Actions Overlay --}}
                                            <div class="absolute top-2 right-2 flex gap-1.5 z-10 sm:opacity-0 sm:group-hover/img:opacity-100 transition-opacity duration-200">
                                                <button @click.stop="downloadImage('{{ $image->image_url }}')" 
                                                    class="h-8 w-8 rounded-full bg-black/60 backdrop-blur-md text-white/90 hover:text-white hover:bg-white/20 flex items-center justify-center transition-colors duration-200 border border-white/10 active:scale-90 shadow-sm" 
                                                    title="Tải xuống">
                                                    <i class="fa-solid fa-download text-[13px] pointer-events-none"></i>
                                                </button>
                                                <button wire:click="deleteImage({{ $image->id }})" @click.stop 
                                                    wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                    class="h-8 w-8 rounded-full bg-black/60 backdrop-blur-md text-white/90 hover:text-white hover:bg-red-500/80 flex items-center justify-center transition-colors duration-200 border border-white/10 active:scale-90 shadow-sm" 
                                                    title="Xóa">
                                                    <i class="fa-solid fa-trash text-[13px] pointer-events-none"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @php $absoluteIndex++; @endphp
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @php $groupIdx++; @endphp

                @empty
                    @if(!$isGenerating)
                        <div class="flex flex-col items-center justify-center text-center px-4 animate-[image-entrance_0.6s_ease-out_forwards]"
                             style="min-height: calc(100dvh - var(--filter-bar-h, 56px) - var(--composer-h, 140px) - var(--gallery-gap, 12px) * 2);">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 mb-5 sm:mb-6 rounded-full bg-white/[0.03] border border-white/10 flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-wand-magic-sparkles text-2xl sm:text-3xl text-purple-400/80"></i>
                            </div>
                            <h3 class="text-lg sm:text-xl font-semibold text-white/90 mb-2">Chưa có tác phẩm nào</h3>
                            <p class="text-[13px] text-white/40 max-w-[260px] leading-relaxed mb-6">Tất cả những hình ảnh bạn tạo sẽ xuất hiện tại đây. Hãy nhập ý tưởng vào khung bên dưới để bắt đầu!</p>
                            <div class="flex flex-wrap justify-center gap-2" x-data="{
                                allPrompts: [
                                    'Một chú mèo dễ thương ngủ trên mây',
                                    'Phong cảnh núi tuyết hoàng hôn',
                                    'Logo công nghệ gradient xanh',
                                    'Cô gái anime với đôi cánh thiên thần',
                                    'Thành phố cyberpunk dưới mưa neon',
                                    'Rồng phương Đông bay trên biển mây'
                                ],
                                prompts: [],
                                init() {
                                    const shuffled = [...this.allPrompts].sort(() => Math.random() - 0.5);
                                    this.prompts = shuffled.slice(0, 3);
                                }
                            }">
                                <template x-for="p in prompts" :key="p">
                                    <button @click="$wire.set('prompt', p); setTimeout(() => { const input = document.querySelector('.t2i-prompt-input'); if(input) { input.focus(); } }, 150);"
                                        class="h-9 px-4 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs text-white/70 hover:text-white transition-all active:scale-[0.98]">
                                        <span x-text="p"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endforelse
            </div>

            {{-- ═══════════════════════════════════════════ --}}
            {{-- GENERATING SKELETON (0ms Alpine Reactivity) --}}
            {{-- ═══════════════════════════════════════════ --}}
            <div id="gen-skeleton" x-show="isLocallyGenerating || $wire.isGenerating" x-cloak
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                x-data="{ elapsed: 0, timer: null }"
                x-effect="
                    if (isLocallyGenerating || $wire.isGenerating) {
                        if (!timer) { elapsed = 0; timer = setInterval(() => elapsed++, 1000); }
                    } else {
                        if (timer) { clearInterval(timer); timer = null; }
                    }
                "
                x-init="$watch('isLocallyGenerating', () => { if(!isLocallyGenerating && timer) { clearInterval(timer); timer = null; } }); $watch('$wire.isGenerating', () => { if(!$wire.isGenerating && timer) { clearInterval(timer); timer = null; } });"
                class="group-batch t2i-batch relative mb-5 bg-[#12151e] overflow-hidden shadow-[0_0_40px_rgba(147,51,234,0.1)] border border-purple-500/30">
                
                {{-- Top Progress Bar --}}
                <div class="absolute top-0 left-0 right-0 h-1 bg-white/[0.03] overflow-hidden z-10">
                    <div class="h-full bg-gradient-to-r from-purple-500 via-indigo-500 to-purple-500 w-[200%] animate-[progress-slide_2s_linear_infinite]"></div>
                </div>

                <div class="px-0 pb-2 relative z-10 mt-1">
                    <div class="flex flex-col sm:flex-row sm:items-baseline gap-2 sm:gap-4 justify-between">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <i class="fa-solid fa-wand-magic-sparkles text-purple-400 text-sm animate-pulse shrink-0"></i>
                            <p class="text-[15px] font-semibold leading-snug text-left text-white/90 truncate" x-text="loadingMessages[currentLoadingMessage] || 'Đang khởi tạo AI...'"></p>
                        </div>

                        <div class="flex items-center gap-3 text-[12px] text-white/40 font-medium shrink-0 flex-wrap">
                            <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 flex items-center gap-1.5 font-bold uppercase tracking-wider text-[10px] border border-purple-500/30">
                                <span class="w-[5px] h-[5px] rounded-full bg-purple-400" x-data="{ toggle: true, dotTimer: null }" x-init="dotTimer = setInterval(() => toggle = !toggle, 800);" @destroyed.window="if(dotTimer) clearInterval(dotTimer)" :class="toggle ? 'opacity-100' : 'opacity-20'" style="transition: opacity 0.3s ease-in-out;"></span> ĐANG VẼ
                            </span>
                            <span class="w-[1px] h-3 bg-white/10 hidden sm:block"></span>
                            <span><span x-text="$wire.batchSize"></span> ảnh</span>
                            <span class="w-[1px] h-3 bg-white/10"></span>
                            <span class="text-purple-300 font-semibold" x-text="(Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : '') + (elapsed % 60) + ' giây'"></span>
                        </div>
                    </div>
                </div>

                {{-- Grid Display --}}
                <div class="px-0 pt-2 border-t border-white/5 relative z-10">
                    <div class="gap-3" :class="parseInt($wire.batchSize) === 1 ? 'flex justify-center w-full' : (parseInt($wire.batchSize) === 2 ? 'grid grid-cols-2 w-full max-w-xl mx-auto' : 'grid grid-cols-2 sm:grid-cols-4 w-full')">
                        <template x-for="i in Array.from({length: parseInt($wire.batchSize)})" :key="'skel-' + i">
                            <div :style="parseInt($wire.batchSize) === 1 ? `width: 100%; max-width: min(100%, 28rem, calc(var(--img-safe-h, 480px) * ${ (() => { let r=$wire.aspectRatio; if(r==='auto'||!r)return 1; let p=r.replace(':','/').split('/'); let w=parseFloat(p[0])||1, h=parseFloat(p[1])||1; return w/h; })() }));` : 'width: 100%;'">
                                <div class="relative bg-[#1c1d21] rounded-xl overflow-hidden border border-white/5 shadow-inner w-full"
                                     :style="parseInt($wire.batchSize) === 1 ? `aspect-ratio: ${$wire.aspectRatio !== 'auto' && typeof $wire.aspectRatio === 'string' && $wire.aspectRatio.includes(':') ? $wire.aspectRatio.replace(':', ' / ') : '1 / 1'}; max-height: var(--img-safe-h, 480px);` : 'aspect-ratio: 1 / 1;'">
                                 
                                 {{-- Shimmer Effect --}}
                                 <div class="absolute inset-0 bg-gradient-to-tr from-transparent via-white/[0.03] to-transparent w-[200%] animate-[shimmer_2s_infinite]"></div>
                                 
                                 {{-- Center Spinner --}}
                                 <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                     <div class="w-8 h-8 rounded-full border-2 border-purple-500/20 border-t-purple-500 animate-[spin_1s_linear_infinite] shadow-[0_0_15px_rgba(168,85,247,0.3)]"></div>
                                 </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Bottom Spacer: composer-h + mobile-nav(56px) + safe-area + gallery-gap
         Trên md+ (desktop), composer nằm sát đáy → không cần 56px.
         Trên mobile, composer cách đáy thêm 56px (bottom nav bar). --}}
    <div id="bottom-spacer" class="w-full shrink-0 pointer-events-none"
         style="height: calc(var(--composer-h, 140px) + env(safe-area-inset-bottom, 0px) + var(--gallery-gap, 12px) + 56px);"></div>

<style>
    @media (min-width: 768px) {
        /* Desktop: composer nằm sát đáy, không cần thêm 56px nav offset */
        #bottom-spacer {
            height: calc(var(--composer-h, 140px) + var(--gallery-gap, 12px)) !important;
        }
    }
</style>