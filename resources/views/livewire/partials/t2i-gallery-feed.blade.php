{{-- ============================================================ --}}
{{-- GALLERY FEED — Compact batch cards with mobile-visible actions --}}
{{-- ============================================================ --}}
<div id="gallery-scroll">
    <div class="max-w-4xl mx-auto px-4"
        style="padding-top: calc(var(--filter-bar-h, 3.5rem) + 1.5rem); padding-bottom: calc(var(--composer-h, 10rem) + 1rem);">

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
        <div class="space-y-6 px-1 md:px-2 pt-6" id="gallery-feed" data-history='@json($flatHistoryForJs)'
            wire:key="gallery-feed">

            @php $absoluteIndex = 0; @endphp

            <div class="space-y-6 gallery-wrapper" x-data="{ initialLoad: true }" x-init="
                if (initialLoad) {
                    $el.style.visibility = 'hidden';
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'auto' });
                            $el.style.visibility = 'visible';
                            initialLoad = false;
                        });
                    });
                }
            ">
                {{-- Infinite Scroll Sentinel --}}
                @if($history instanceof \Illuminate\Pagination\LengthAwarePaginator && $history->hasMorePages())
                    <div id="load-more-sentinel" class="flex justify-center py-4" x-data="{
                            observer: null,
                            isLoading: false,
                            ready: false,
                            init() {
                                setTimeout(() => {
                                    this.ready = true;
                                    this.observer = new IntersectionObserver((entries) => {
                                        entries.forEach(entry => {
                                            if (entry.isIntersecting && !this.isLoading && this.ready) {
                                                this.isLoading = true;
                                                const scrollH = document.documentElement.scrollHeight;
                                                $wire.loadMore().then(() => {
                                                    this.$nextTick(() => {
                                                        requestAnimationFrame(() => {
                                                            const newScrollH = document.documentElement.scrollHeight;
                                                            document.documentElement.scrollTop += (newScrollH - scrollH);
                                                            this.isLoading = false;
                                                        });
                                                    });
                                                }).catch(() => { this.isLoading = false; });
                                            }
                                        });
                                    }, { rootMargin: '100px 0px 0px 0px' });
                                    this.observer.observe(this.$el);
                                }, 500);
                            },
                            destroy() {
                                if (this.observer) this.observer.disconnect();
                            }
                        }">
                        <div class="flex items-center gap-2 text-white/40 text-sm" wire:loading.flex wire:target="loadMore">
                            <i class="fa-solid fa-spinner fa-spin text-purple-400"></i>
                            <span>Đang tải thêm...</span>
                        </div>
                        <div class="text-white/20 text-xs" wire:loading.remove wire:target="loadMore">
                            <i class="fa-solid fa-ellipsis"></i>
                        </div>
                    </div>
                @endif

                {{-- Grouped Batches --}}
                @php $totalGroups = $groupedHistory->count();
                $groupIdx = 0; @endphp
                @forelse($groupedHistory as $groupKey => $groupItems)
                    @php $wireKey = md5($groupKey); @endphp
                    @php
                        $firstItem = $groupItems->first();
                        $modelId = $firstItem->generation_params['model_id'] ?? null;
                        $ratio = $firstItem->generation_params['aspect_ratio'] ?? '1:1';

                        $modelName = $modelId;
                        if ($modelId && isset($availableModels)) {
                            $found = collect($availableModels)->firstWhere('id', $modelId);
                            $modelName = $found['name'] ?? $modelId;
                        }

                        $aspectRatioCss = '1 / 1';
                        if ($ratio !== 'Auto' && strpos($ratio, ':') !== false) {
                            [$w, $h] = explode(':', $ratio);
                            $aspectRatioCss = $w . ' / ' . $h;
                        }
                    @endphp

                    <div class="space-y-2 group-batch" x-data="{ expanded: false }" wire:key="group-{{ $wireKey }}"
                        style="content-visibility: auto;">

                        {{-- Batch Header --}}
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-between gap-2 flex-1 text-sm min-w-0">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-white/70 text-xs font-medium shrink-0">Tạo ảnh</span>
                                    <i class="fa-solid fa-chevron-right text-white/30 text-[9px] shrink-0"></i>
                                    <button
                                        class="min-w-0 flex-1 text-sm text-left text-white/50 first-letter:capitalize hover:text-white/80 transition-colors duration-200 cursor-pointer truncate overflow-hidden"
                                        @click="expanded = !expanded" title="Nhấn để xem prompt đầy đủ">
                                        {{ $firstItem->final_prompt }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-0.5 shrink-0">
                                    <button x-data="{ copied: false }"
                                        @click="navigator.clipboard.writeText(@js($firstItem->final_prompt)); copied = true; $dispatch('show-toast', { message: 'Đã copy prompt' }); setTimeout(() => copied = false, 2000)"
                                        class="inline-flex items-center justify-center h-7 px-2 rounded-lg bg-transparent text-white/50 hover:bg-white/[0.05] hover:text-white/90 text-xs transition-all duration-200 active:scale-[0.98]"
                                        title="Copy prompt">
                                        <i :class="copied ? 'fa-solid fa-check text-green-400' : 'fa-regular fa-copy'"
                                            class="text-[11px] mr-1"></i>
                                        <span class="hidden sm:inline" x-text="copied ? 'Copied!' : 'Copy'"></span>
                                    </button>
                                    <button wire:click="reusePrompt({{ $firstItem->id }})"
                                        class="inline-flex items-center justify-center h-7 px-2 rounded-lg bg-transparent text-white/50 hover:bg-white/[0.05] hover:text-white/90 text-xs transition-all duration-200 active:scale-[0.98]"
                                        title="Dùng lại prompt + cài đặt">
                                        <i class="fa-solid fa-arrow-rotate-left text-xs mr-1"></i>
                                        <span class="hidden sm:inline">Reuse</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Expanded Prompt Detail --}}
                        <div x-show="expanded" x-cloak x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="px-3 py-2.5 rounded-xl bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)] text-sm text-white/70 leading-relaxed">
                            {{ $firstItem->final_prompt }}
                            <div class="flex items-center gap-2 mt-2 text-[11px] text-white/40">
                                <span class="text-purple-300/70">{{ $modelName }}</span>
                                <span class="text-white/20">•</span>
                                <span>{{ $ratio }}</span>
                                <span class="text-white/20">•</span>
                                <span>{{ $groupItems->count() }} ảnh</span>
                                <span class="text-white/20">•</span>
                                <span>{{ $firstItem->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        {{-- Image Grid --}}
                        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-1 rounded-lg overflow-hidden">
                            @foreach($groupItems as $image)
                                <div class="block group cursor-pointer" @click="openPreview(null, {{ $absoluteIndex }})">
                                    <div class="h-full bg-white/[0.02]">
                                        <div class="relative overflow-hidden" style="aspect-ratio: {{ $aspectRatioCss }};">
                                            {{-- Shimmer --}}
                                            <div class="img-shimmer absolute inset-0 bg-white/[0.04]">
                                                <div
                                                    class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.06] to-transparent animate-shimmer">
                                                </div>
                                            </div>
                                            {{-- Image --}}
                                            <img src="{{ $image->image_url }}" alt="Preview"
                                                class="gallery-img w-full h-full object-cover transition-all duration-300 ease-out group-hover:scale-[1.05]"
                                                draggable="false"
                                                onload="this.classList.add('is-loaded');this.previousElementSibling.style.display='none'"
                                                onerror="this.classList.add('is-error');this.src='/images/placeholder-broken.svg'"
                                                {{ $groupIdx < $totalGroups - 2 ? 'loading=lazy decoding=async' : 'fetchpriority=high' }}>

                                            {{-- Desktop Hover Overlay --}}
                                            <div
                                                class="hidden sm:block absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                <div class="absolute bottom-2 right-2 flex gap-1.5">
                                                    <button @click.stop="downloadImage('{{ $image->image_url }}')"
                                                        class="h-8 w-8 rounded-lg bg-black/50 backdrop-blur-[8px] hover:bg-white/20 text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1] active:scale-[0.95]"
                                                        aria-label="Tải xuống">
                                                        <i class="fa-solid fa-download text-[11px]"></i>
                                                    </button>
                                                    <button wire:click="deleteImage({{ $image->id }})" @click.stop
                                                        wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                        class="h-8 w-8 rounded-lg bg-black/50 backdrop-blur-[8px] hover:bg-red-500/80 text-white flex items-center justify-center transition-all duration-200 border border-white/[0.1] active:scale-[0.95]"
                                                        aria-label="Xóa">
                                                        <i class="fa-solid fa-trash text-[11px]"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Mobile Always-visible Mini Actions --}}
                                            <div class="sm:hidden absolute bottom-1 right-1 flex gap-1">
                                                <button @click.stop="downloadImage('{{ $image->image_url }}')"
                                                    class="h-6 w-6 rounded-md bg-black/60 backdrop-blur-sm text-white flex items-center justify-center active:scale-[0.9] transition-all"
                                                    aria-label="Tải xuống">
                                                    <i class="fa-solid fa-download text-[9px]"></i>
                                                </button>
                                                <button wire:click="deleteImage({{ $image->id }})" @click.stop
                                                    wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                    class="h-6 w-6 rounded-md bg-black/60 backdrop-blur-sm text-white flex items-center justify-center active:scale-[0.9] transition-all"
                                                    aria-label="Xóa">
                                                    <i class="fa-solid fa-trash text-[9px]"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @php $absoluteIndex++; @endphp
                            @endforeach
                        </div>
                    </div>
                    @php $groupIdx++; @endphp

                @empty
                    @if(!$isGenerating)
                        <div class="py-16 sm:py-24 text-center" x-data="{
                                    allPrompts: [
                                        'Một chú mèo dễ thương ngủ trên mây',
                                        'Phong cảnh núi tuyết hoàng hôn',
                                        'Logo công nghệ gradient xanh',
                                        'Cô gái anime với đôi cánh thiên thần',
                                        'Thành phố cyberpunk dưới mưa neon',
                                        'Rồng phương Đông bay trên biển mây',
                                        'Chiếc xe cổ điển trên con đường hoa anh đào',
                                        'Lâu đài fantasy trên ngọn núi tuyết',
                                        'Robot dễ thương đang tưới hoa',
                                        'Bình minh trên cánh đồng hoa lavender',
                                        'Phi hành gia lơ lửng trong không gian đầy sao',
                                        'Quán cà phê ấm cúng ngày mưa phong cách Ghibli'
                                    ],
                                    prompts: [],
                                    init() {
                                        const shuffled = [...this.allPrompts].sort(() => Math.random() - 0.5);
                                        this.prompts = shuffled.slice(0, 3);
                                    }
                                }">
                            <div
                                class="w-16 h-16 mx-auto rounded-2xl bg-white/[0.05] border border-white/[0.08] flex items-center justify-center mb-4 shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                                <i class="fa-solid fa-image text-3xl text-white/20"></i>
                            </div>
                            <h3 class="text-white/95 font-medium text-lg mb-2">Chưa có hình ảnh nào</h3>
                            <p class="text-white/50 text-sm max-w-sm mx-auto mb-6">
                                Hãy thử tạo một hình ảnh mới bằng cách nhập mô tả vào khung chat bên dưới.
                            </p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <template x-for="p in prompts" :key="p">
                                    <button @click="$wire.set('prompt', p)"
                                        class="h-9 px-4 rounded-lg bg-white/[0.05] hover:bg-white/[0.08] border border-white/[0.08] text-xs text-white/70 hover:text-white transition-all active:scale-[0.98]">
                                        <span x-text="p"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif
                @endforelse
            </div>

        </div>

        {{-- Loading Skeleton (inline, replaces t2i-loading.blade.php) --}}
        @if($isGenerating && !$generatedImageUrl)
            <div x-data="{ elapsed: 0, timer: null }" x-init="
                    timer = setInterval(() => elapsed++, 1000);
                    $cleanup(() => { clearInterval(timer); });
                    $nextTick(() => setTimeout(() => document.documentElement.scrollTo({ top: document.documentElement.scrollHeight, behavior: 'smooth' }), 100));
                ">
                <div
                    class="bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl overflow-hidden shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]">
                    <div class="h-0.5 bg-white/[0.03] overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-purple-500 via-fuchsia-500 to-purple-500 animate-pulse"
                            style="width: 100%; animation: progress-slide 2s ease-in-out infinite;"></div>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                <div
                                    class="w-5 h-5 border-2 border-purple-400 border-t-transparent rounded-full animate-spin">
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 text-sm font-medium"
                                    x-text="loadingMessages[currentLoadingMessage]">Đang tạo ảnh...</p>
                                <p class="text-white/40 text-xs mt-0.5">
                                    <span
                                        x-text="Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : ''"></span>
                                    <span x-text="(elapsed % 60) + ' giây'"></span>
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-1 rounded-lg overflow-hidden">
                            @for ($i = 0; $i < $batchSize; $i++)
                                <div class="bg-white/[0.03] flex items-center justify-center {{ $batchSize == 1 ? 'col-span-2 max-w-sm' : '' }}"
                                    style="aspect-ratio: {{ $aspectRatio !== 'auto' && strpos($aspectRatio, ':') !== false ? str_replace(':', ' / ', $aspectRatio) : '1 / 1' }};">
                                    <div
                                        class="w-6 h-6 border-2 border-purple-500/40 border-t-transparent rounded-full animate-spin">
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>