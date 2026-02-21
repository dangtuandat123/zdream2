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
    <div class="max-w-4xl mx-auto px-4"
        style="padding-top: calc(var(--filter-bar-h, 3.5rem) + 1.5rem); padding-bottom: calc(var(--composer-h, 10rem) + var(--nav-bar-h, 0px) + 1rem);">

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
        <div class="flex flex-col gap-5 sm:gap-6 px-0 sm:px-1 pt-4 sm:pt-5" id="gallery-feed"
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
                                <button class="text-[15px] font-semibold leading-snug text-left text-white/90 hover:text-white transition-colors duration-200 cursor-pointer line-clamp-2 break-words break-all"
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
                            x-transition:enter-end="opacity-100 translate-y-0 max-h-[500px]"
                            class="px-0 pb-2">
                            <div class="p-3 rounded-lg bg-white/[0.03] border border-white/[0.06] text-[13px] text-white/70 leading-relaxed break-words break-all">
                                {{ $prompt }}
                            </div>
                        </div>

                        {{-- ── Image Grid ── --}}
                        {{-- ── Image Grid ── --}}
                        <div class="px-0">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach($groupItems as $image)
                                    @php
                                        $isNewestGroup = $groupIdx === $totalGroups - 1;
                                        $isPriorityImage = $isNewestGroup && $loop->index < 2;
                                    @endphp
                                    <div class="relative bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden hover:border-purple-500/30 transition-all" wire:key="img-{{ $image->id }}">
                                        <!-- Image Container -->
                                        <div class="aspect-square relative overflow-hidden image-card-hover">
                                            {{-- Shimmer --}}
                                            <div class="img-shimmer absolute inset-0 bg-white/[0.04] overflow-hidden">
                                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.06] to-transparent"></div>
                                            </div>
                                            {{-- Image --}}
                                            <button @click="openPreview(null, {{ $absoluteIndex }})" class="w-full h-full cursor-pointer block">
                                                <img src="{{ $image->image_url }}" alt="Preview"
                                                    class="gallery-img w-full h-full object-contain transition-transform duration-500"
                                                    draggable="false"
                                                    onload="this.previousElementSibling.previousElementSibling && (this.previousElementSibling.previousElementSibling.style.display='none')"
                                                    onerror="this.previousElementSibling.previousElementSibling && (this.previousElementSibling.previousElementSibling.style.display='none'); this.onerror=null; this.src='/images/placeholder.svg'"
                                                    {{ $isPriorityImage ? 'loading=eager fetchpriority=high decoding=async' : 'loading=lazy fetchpriority=low decoding=async' }}>
                                            </button>
                                            
                                            <!-- Hover overlay với icon mắt -->
                                            <div class="hover-overlay pointer-events-none absolute inset-0 bg-black/50 flex flex-col items-center justify-center opacity-0 transition-opacity duration-300">
                                                <i class="fa-solid fa-eye text-[#d3d6db] text-3xl"></i>
                                            </div>
                                        </div>

                                        <!-- Info & Actions -->
                                        <div class="p-2 sm:p-2.5 bg-[#1b1c21]">
                                            <div class="flex gap-2">
                                                <button @click.stop="downloadImage('{{ $image->image_url }}')"
                                                    class="flex-1 py-1.5 sm:py-2 rounded-lg bg-purple-500/20 text-purple-300 text-[10px] sm:text-xs font-medium text-center hover:bg-purple-500/30 transition-colors inline-flex items-center justify-center gap-1 active:scale-95">
                                                    <i class="fa-solid fa-download"></i> <span class="hidden sm:inline">Tải</span>
                                                </button>
                                                <button wire:click="deleteImage({{ $image->id }})" @click.stop
                                                    wire:confirm="Bạn có chắc muốn xóa ảnh này?"
                                                    class="flex-1 py-1.5 sm:py-2 rounded-lg bg-red-500/20 text-red-300 text-[10px] sm:text-xs font-medium hover:bg-red-500/30 transition-colors inline-flex items-center justify-center gap-1 active:scale-95">
                                                    <i class="fa-solid fa-trash"></i> <span class="hidden sm:inline">Xóa</span>
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

            {{-- ═══════════════════════════════════════════ --}}
            {{-- CHẾ ĐỘ HIỆN TẠI: ĐANG TẠO ẢNH (GENERATING) --}}
            {{-- ═══════════════════════════════════════════ --}}
            @if($isGenerating && !$generatedImageUrl)
                <div x-data="{ elapsed: 0, timer: null }" x-init="
                            timer = setInterval(() => elapsed++, 1000);
                            const stopTimer = () => clearInterval(timer);
                            window.addEventListener('livewire:navigating', stopTimer, { once: true });
                            const self = $el;
                            const guard = setInterval(() => {
                                if (!document.body.contains(self)) {
                                    clearInterval(timer);
                                    clearInterval(guard);
                                }
                            }, 1500);
                        " class="mb-5">
                    <div class="relative bg-[#1b1c21] border border-purple-500/30 rounded-xl overflow-hidden shadow-[0_0_15px_rgba(168,85,247,0.15)] transition-all">
                        {{-- Thanh Process Bar chạy liên tục ở trên cùng --}}
                        <div class="h-1 w-full bg-[#11141c] overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-purple-500/20 via-purple-400 to-purple-500/20"
                                style="width: 100%; animation: progress-slide 2s ease-in-out infinite;"></div>
                        </div>
                        
                        {{-- Tiêu đề & Trạng thái --}}
                        <div class="px-0 pb-2 p-4 border-b border-white/[0.05]">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-3 sm:gap-4">
                                {{-- Tiêu đề prompt --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-[15px] font-semibold leading-snug text-white/90 line-clamp-2 break-words break-all">
                                        {{ $prompt ? $prompt : 'Đang xử lý yêu cầu...' }}
                                    </div>
                                    <div class="flex items-center gap-3 text-[12px] text-purple-300 font-medium mt-1.5 shrink-0">
                                        <div class="flex items-center gap-1.5 bg-purple-500/10 px-2 py-0.5 rounded-md border border-purple-500/20">
                                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                                            <span x-text="loadingMessages[currentLoadingMessage] || 'Đang vẽ ảnh...'">Đang vẽ ảnh...</span>
                                            <span class="ml-1 opacity-70">
                                                (<span x-text="Math.floor(elapsed / 60) > 0 ? Math.floor(elapsed / 60) + ' phút ' : ''"></span><span x-text="(elapsed % 60) + 's'"></span>)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Lưới Skeleton (Preview Grid) --}}
                        <div class="p-4 bg-black/20">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @for ($i = 0; $i < $batchSize; $i++)
                                    <div class="relative overflow-hidden bg-[#222] rounded-lg aspect-square border border-white/[0.05] flex items-center justify-center animate-pulse">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-purple-900/10 via-transparent to-purple-900/10"></div>
                                        <div class="w-8 h-8 rounded-full bg-purple-500/10 flex items-center justify-center border border-purple-500/20 backdrop-blur-sm z-10 shadow-[0_0_15px_rgba(168,85,247,0.2)]">
                                            <i class="fa-solid fa-spinner fa-spin text-purple-400 text-sm"></i>
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