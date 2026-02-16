{{-- ============================================================ --}}
{{-- TEXT-TO-IMAGE — Root Orchestrator (Redesigned: Core-first) --}}
{{-- ============================================================ --}}
<div class="relative min-h-screen t2i-shell" @if($isGenerating) wire:poll.1500ms="pollImageStatus" @endif
    x-data="textToImage" @keydown.window="handleKeydown($event)"
    x-on:show-toast.window="notify($event.detail.message, $event.detail.type || 'success')">

    {{-- Toast --}}
    <div x-show="showToast" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-[300] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-2xl flex items-center gap-2"
        :class="{ 'bg-green-500/95': toastType==='success', 'bg-red-500/95': toastType==='error', 'bg-yellow-500/95 text-black': toastType==='warning' }">
        <i
            :class="{ 'fa-solid fa-check-circle': toastType==='success', 'fa-solid fa-exclamation-circle': toastType==='error', 'fa-solid fa-triangle-exclamation': toastType==='warning' }"></i>
        <span x-text="toastMessage"></span>
    </div>


    @php
        // 1. Grouping Logic
        $historyCollection = ($history instanceof \Illuminate\Pagination\LengthAwarePaginator)
            ? $history->getCollection()
            : collect($history);
        $historyCollection = $historyCollection
            ->sort(function ($a, $b) {
                $aTs = $a->created_at?->getTimestamp() ?? 0;
                $bTs = $b->created_at?->getTimestamp() ?? 0;
                if ($aTs === $bTs) {
                    return ($a->id ?? 0) <=> ($b->id ?? 0);
                }
                return $aTs <=> $bTs;
            })
            ->values();
        $groupedHistory = $historyCollection
            ->groupBy(function ($item) {
                if (!empty($item->generation_params['batch_id'])) {
                    return $item->generation_params['batch_id'];
                }
                return $item->final_prompt . '|' .
                    ($item->generation_params['model_id'] ?? '') . '|' .
                    ($item->generation_params['aspect_ratio'] ?? '') . '|' .
                    'legacy-' . $item->id;
            })
            ->map(function ($items) {
                return collect($items)
                    ->sort(function ($a, $b) {
                        $aTs = $a->created_at?->getTimestamp() ?? 0;
                        $bTs = $b->created_at?->getTimestamp() ?? 0;
                        if ($aTs === $bTs) {
                            return ($a->id ?? 0) <=> ($b->id ?? 0);
                        }
                        return $aTs <=> $bTs;
                    })
                    ->values();
            })
            ->sort(function ($groupA, $groupB) {
                $aFirst = $groupA->first();
                $bFirst = $groupB->first();
                $aTs = $aFirst?->created_at?->getTimestamp() ?? 0;
                $bTs = $bFirst?->created_at?->getTimestamp() ?? 0;
                if ($aTs === $bTs) {
                    return ($aFirst->id ?? 0) <=> ($bFirst->id ?? 0);
                }
                return $aTs <=> $bTs;
            })
            ->values();

        // 2. Flatten for JS (chat order: oldest -> newest) — map model_id to friendly name
        $modelMap = collect($availableModels)->pluck('name', 'id')->toArray();
        $flatHistoryForJs = $groupedHistory->flatten(1)->map(fn($img) => [
            'id' => $img->id,
            'url' => $img->image_url,
            'prompt' => $img->final_prompt,
            'model' => $modelMap[$img->generation_params['model_id'] ?? ''] ?? ($img->generation_params['model_id'] ?? null),
            'ratio' => $img->generation_params['aspect_ratio_user'] ?? $img->generation_params['aspect_ratio'] ?? 'Auto',
            'created_at' => $img->created_at->diffForHumans(),
        ])->values()->toArray();
    @endphp

    {{-- PARTIALS (new core-first layout) --}}
    @include('livewire.partials.t2i-filter-compact')
    @include('livewire.partials.t2i-gallery-feed')
    @include('livewire.partials.t2i-composer-card')

    {{-- MODALS --}}
    @include('livewire.partials.image-preview-modal')

    {{-- ============================================================ --}}
    {{-- STYLES --}}
    {{-- ============================================================ --}}
    <style>
        .t2i-shell {
            --surface-1: rgba(255, 255, 255, 0.03);
            --surface-2: rgba(255, 255, 255, 0.05);
            --surface-3: rgba(255, 255, 255, 0.08);
            --line-1: rgba(255, 255, 255, 0.08);
            --line-2: rgba(255, 255, 255, 0.12);
            --text-strong: rgba(255, 255, 255, 0.95);
            --text-normal: rgba(255, 255, 255, 0.74);
            --text-muted: rgba(255, 255, 255, 0.5);
            background: #0b0d12;
            color: var(--text-strong);
        }

        .glass-popover {
            background: #12151d;
            border: 1px solid var(--line-2);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
        }

        .glass-chip {
            background: #141821;
            border: 1px solid var(--line-1);
            color: var(--text-normal);
        }

        .glass-chip:hover {
            background: #1a1f2a;
            color: var(--text-strong);
            border-color: var(--line-2);
        }

        .glass-chip-active {
            background: rgba(59, 130, 246, 0.18);
            border-color: rgba(59, 130, 246, 0.45);
            color: rgba(219, 234, 254, 0.96);
        }

        .t2i-filter-wrap .t2i-topbar {
            background: rgba(11, 13, 18, 0.94);
            border-bottom: 1px solid var(--line-1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .t2i-gallery-shell #gallery-feed {
            overflow-anchor: none;
        }

        .t2i-gallery-shell .group-batch {
            overflow-anchor: none;
        }

        .t2i-gallery-shell .t2i-batch {
            padding: 0.85rem;
            border-radius: 0.9rem;
            border: 1px solid var(--line-1);
            background: #11141c;
        }

        .t2i-gallery-shell .t2i-batch .gallery-img {
            will-change: auto;
        }

        .t2i-jump-newest {
            background: rgba(30, 64, 175, 0.92);
            border: 1px solid rgba(96, 165, 250, 0.5);
            color: #eff6ff;
            box-shadow: 0 8px 20px rgba(30, 64, 175, 0.28);
        }

        .t2i-jump-newest:hover {
            background: rgba(37, 99, 235, 0.96);
        }

        .t2i-composer-wrap .t2i-composer-card {
            border: 1px solid var(--line-2);
            background: rgba(16, 19, 27, 0.96);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.32);
        }

        .t2i-prompt-input {
            color: var(--text-strong);
        }

        .t2i-prompt-input::placeholder {
            color: var(--text-muted);
        }

        .t2i-generate-btn {
            background: #2563eb;
            border: 1px solid #3b82f6;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.3);
        }

        .t2i-generate-btn:hover {
            background: #1d4ed8;
        }

        .t2i-cancel-btn {
            background: #b91c1c;
            border: 1px solid #ef4444;
            box-shadow: 0 8px 18px rgba(185, 28, 28, 0.3);
        }

        .t2i-cancel-btn:hover {
            background: #991b1b;
        }

        .t2i-preview {
            backdrop-filter: blur(2px);
        }

        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .safe-area-top {
            padding-top: env(safe-area-inset-top, 0px);
        }

        @keyframes image-entrance {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-batch-animate {
            animation: image-entrance 0.32s ease-out forwards;
        }

        .new-batch-glow {
            box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.28) inset;
            border-radius: 0.55rem;
            transition: box-shadow 0.8s ease-out;
        }

        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .animate-shimmer {
            animation: shimmer 1.8s linear infinite;
        }

        @keyframes progress-slide {

            0%,
            100% {
                opacity: 0.45;
                transform: translateX(-30%);
            }

            50% {
                opacity: 1;
                transform: translateX(0%);
            }
        }

        @media (min-width: 768px) {
            .composer-fixed {
                bottom: 0 !important;
            }
        }

        .gallery-img {
            opacity: 1 !important;
            transform: none !important;
        }

        @media (prefers-reduced-motion: reduce) {

            .new-batch-animate,
            .animate-shimmer {
                animation: none !important;
            }
        }
    </style>

    {{-- ============================================================ --}}
    {{-- ALPINE DATA --}}
    {{-- ============================================================ --}}
    @script
    <script>
        (() => {
            const registerTextToImage = () => {
                if (!window.Alpine) {
                    return;
                }

                Alpine.data('textToImage', () => ({
                    // ── UI State ───────────────────────────────────                            ───
                    uiMode: 'idle', // idle | generating | partial_success | failed | done
                    statusMessage: '',
                    statusElapsed: 0,
                    statusTimer: null,
                    autoScrollEnabled: true, // Auto-follow newest content (bottom)
                    showScrollToBottom: false, // Floating jump-to-newest button

                    // Toast
                    showToast: false,
                    toastMessage: '',
                    toastType: 'success',
                    toastTimer: null,

                    // ── Composer ──────────────────────────────────────
                    showRatioSheet: false,
                    showModelSheet: false,
                    showBatchSheet: false,
                    showRefPicker: false,

                    selectedRatio: @js($aspectRatio),
                    selectedModel: @js($modelId),
                    customWidth: 1024,
                    customHeight: 1024,
                    linkDimensions: true,

                    ratios: [
                        { id: 'auto', label: 'Auto', icon: 'fa-expand' },
                        { id: '1:1', label: '1:1', icon: null },
                        { id: '16:9', label: '16:9', icon: null },
                        { id: '9:16', label: '9:16', icon: null },
                        { id: '4:3', label: '4:3', icon: null },
                        { id: '3:4', label: '3:4', icon: null },
                        { id: '3:2', label: '3:2', icon: null },
                        { id: '2:3', label: '2:3', icon: null },
                        { id: '5:4', label: '5:4', icon: null },
                        { id: '4:5', label: '4:5', icon: null },
                        { id: '21:9', label: '21:9', icon: null }
                    ],
                    models: @js(collect($availableModels)->values()->map(fn($m) => [
                        'id' => $m['id'],
                        'name' => $m['name'],
                        'desc' => $m['description'] ?? '',
                        'icon' => match (true) {
                            str_contains($m['id'], 'ultra') => '⚡',
                            str_contains($m['id'], 'pro') => '💎',
                            str_contains($m['id'], 'schnell') => '🚀',
                            default => '🛠️'
                        },
                        'shortLabel' => match (true) {
                            str_contains($m['id'], 'kontext-max') => 'K-Max',
                            str_contains($m['id'], 'kontext-pro') => 'K-Pro',
                            str_contains($m['id'], 'flux-2-max') => '2 Max',
                            str_contains($m['id'], 'flux-2-pro') => '2 Pro',
                            str_contains($m['id'], 'flux-2-flex') => '2 Flex',
                            str_contains($m['id'], 'klein-4b') => 'K-4B',
                            str_contains($m['id'], 'klein-9b') => 'K-9B',
                            str_contains($m['id'], 'pro-1.1-ultra') => '1.1 Ultra',
                            str_contains($m['id'], 'pro-1.1') => '1.1 Pro',
                            str_contains($m['id'], 'schnell') => 'Schnell',
                            str_contains($m['id'], 'dev') => 'Dev',
                            default => last(explode('-', $m['id'])),
                        },
                        'maxImages' => ($m['supports_image_input'] ?? false) ? ($m['max_input_images'] ?? 1) : 0,
                        'supportsImageInput' => $m['supports_image_input'] ?? false,
                    ])),

                    // ── Refs ──────────────────────────────────────────
                    selectedImages: [],
                    recentImages: [],
                    isLoadingPicker: false,
                    urlInput: '',

                    // ── Preview ───────────────────────────────────────
                    showPreview: false,
                    previewIndex: 0,
                    previewImage: null,
                    historyData: @js($flatHistoryForJs),
                    historySignature: '',

                    // ── Loading ───────────────────────────────────────
                    loadingMessages: [
                        'Đang tạo ảnh...',
                        'AI đang sáng tạo...',
                        'Đang xử lý prompt...',
                        'Đang render chi tiết...',
                        'Sắp xong rồi...'
                    ],
                    currentLoadingMessage: 0,
                    loadingInterval: null,

                    // ── Internal refs (scroll / IO / cleanup) ─────
                    _scrollHandler: null,
                    _onNavigating: null,
                    _morphCleanup: null,
                    _wireListeners: [],
                    _scrollRestoration: null,
                    _loadMoreFailSafeTimer: null,
                    _resizeHandler: null,
                    _sentinelObserver: null,
                    _anchorId: null,
                    _anchorTop: 0,

                    // ── Infinite scroll state ─────────────────────
                    hasMoreHistory: @js($history instanceof \Illuminate\Pagination\LengthAwarePaginator ? $history->hasMorePages() : false),
                    loadingMoreHistory: false,
                    isPrependingHistory: false,
                    lastLoadMoreAt: 0,
                    lastScrollY: 0,
                    loadOlderStep: 3,

                    // Dynamic max images based on selected model
                    get maxImages() {
                        const model = this.models.find(m => m.id === this.selectedModel);
                        return model?.maxImages ?? 0;
                    },

                    // ============================================================
                    // INIT
                    // ============================================================
                    init() {
                        // Keep Alpine state in sync with Livewire without entangle() (safe across wire:navigate remounts).
                        if (this.$wire?.aspectRatio !== undefined) {
                            this.selectedRatio = this.$wire.aspectRatio;
                        }
                        if (this.$wire?.modelId !== undefined) {
                            this.selectedModel = this.$wire.modelId;
                        }

                        this.$watch('selectedRatio', (value) => {
                            if (this.$wire?.aspectRatio !== value) {
                                this.$wire.set('aspectRatio', value);
                            }
                        });
                        this.$watch('selectedModel', (value) => {
                            if (this.$wire?.modelId !== value) {
                                this.$wire.set('modelId', value);
                            }
                        });
                        this.$watch('$wire.aspectRatio', (value) => {
                            if (value !== undefined && value !== this.selectedRatio) {
                                this.selectedRatio = value;
                            }
                        });
                        this.$watch('$wire.modelId', (value) => {
                            if (value !== undefined && value !== this.selectedModel) {
                                this.selectedModel = value;
                            }
                        });

                        if ('scrollRestoration' in history) {
                            this._scrollRestoration = history.scrollRestoration;
                            history.scrollRestoration = 'manual';
                        }

                        const dataEl = document.getElementById('gallery-feed');
                        if (dataEl?.dataset?.hasMore !== undefined) {
                            this.hasMoreHistory = dataEl.dataset.hasMore === '1';
                        }
                        this.syncHistoryData(this.historyData);

                        // Scroll handler: manages auto-scroll + jump button
                        this._scrollHandler = () => {
                            const currentY = window.scrollY || document.documentElement.scrollTop || 0;
                            this.lastScrollY = currentY;

                            if (this.autoScrollEnabled && !this.isNearBottom(120)) {
                                this.autoScrollEnabled = false;
                            }
                            if (this.isNearBottom(120)) {
                                this.showScrollToBottom = false;
                            }
                        };
                        window.addEventListener('scroll', this._scrollHandler, { passive: true });

                        // FIX BUG 7: observe sentinel AFTER scrollToBottom so it doesn't fire on page load
                        this.$nextTick(() => {
                            requestAnimationFrame(() => {
                                this.scrollToBottom(false);
                                this.lastScrollY = window.scrollY || document.documentElement.scrollTop || 0;
                                this.maybeBootstrapHistory();
                                this._reobserveSentinel();
                            });
                        });

                        this._resizeHandler = () => {
                            this.maybeBootstrapHistory();
                        };
                        window.addEventListener('resize', this._resizeHandler, { passive: true });

                        // Image generated → scroll + celebrate
                        const offGenerated = this.$wire.$on('imageGenerated', (params) => {
                            const { successCount, failedCount } = Array.isArray(params) ? params[0] || {} : params || {};
                            this.$nextTick(() => {
                                setTimeout(() => {
                                    // Always center the first image of the new batch
                                    this.centerLatestBatchWhenReady();
                                    this.autoScrollEnabled = true;
                                    this.showScrollToBottom = false;

                                    const batches = document.querySelectorAll('.group-batch');
                                    const lastBatch = batches[batches.length - 1];
                                    if (lastBatch) {
                                        lastBatch.classList.add('new-batch-animate', 'new-batch-glow');
                                        setTimeout(() => lastBatch.classList.remove('new-batch-glow'), 3000);
                                        setTimeout(() => lastBatch.classList.remove('new-batch-animate'), 600);
                                    }

                                    // Update uiMode based on results
                                    if (failedCount > 0 && successCount > 0) {
                                        this.uiMode = 'partial_success';
                                        this.statusMessage = `🎨 ${successCount} ảnh thành công, ${failedCount} thất bại`;
                                    } else if (failedCount > 0) {
                                        this.uiMode = 'failed';
                                        this.statusMessage = `❌ Tạo ảnh thất bại`;
                                    } else {
                                        this.uiMode = 'done';
                                        this.statusMessage = `🎨 Đã tạo xong ${successCount > 1 ? successCount + ' ảnh' : 'ảnh'}!`;
                                        setTimeout(() => { if (this.uiMode === 'done') this.uiMode = 'idle'; }, 5000);
                                    }
                                    this.stopStatusTimer();
                                    this.notify(this.statusMessage, failedCount > 0 ? 'warning' : 'success');
                                }, 300);
                            });
                        });
                        if (typeof offGenerated === 'function') this._wireListeners.push(offGenerated);

                        // Generation failed (all images)
                        const offFailed = this.$wire.$on('imageGenerationFailed', () => {
                            this.uiMode = 'failed';
                            this.statusMessage = '❌ Tạo ảnh thất bại. Vui lòng thử lại.';
                            this.stopStatusTimer();
                            this.notify(this.statusMessage, 'error');
                        });
                        if (typeof offFailed === 'function') this._wireListeners.push(offFailed);

                        const offHistoryUpdated = this.$wire.$on('historyUpdated', (params) => {
                            const payload = Array.isArray(params) ? params[0] || {} : params || {};
                            if (Object.prototype.hasOwnProperty.call(payload, 'hasMore')) {
                                this.hasMoreHistory = !!payload.hasMore;
                            }

                            this.$nextTick(() => {
                                clearTimeout(this._loadMoreFailSafeTimer);
                                this._loadMoreFailSafeTimer = null;
                                // Backup restoration (if morph.updated didn't run)
                                if (this.isPrependingHistory && this._anchorId) {
                                    this._restoreScrollPosition();
                                }
                                this.loadingMoreHistory = false;
                                this.isPrependingHistory = false;
                                this._reobserveSentinel();
                            });
                        });
                        if (typeof offHistoryUpdated === 'function') this._wireListeners.push(offHistoryUpdated);

                        // Auto-trim refs when model changes
                        this.$watch('selectedModel', () => {
                            const max = this.maxImages;
                            if (max === 0 && this.selectedImages.length > 0) {
                                this.selectedImages = [];
                                this.$wire.setReferenceImages([]);
                                this.notify('Model này không hỗ trợ ảnh tham chiếu', 'warning');
                            } else if (this.selectedImages.length > max) {
                                this.selectedImages = this.selectedImages.slice(0, max);
                                this.$wire.setReferenceImages(this.selectedImages.map(img => ({ url: img.url })));
                                this.notify(`Model này hỗ trợ tối đa ${max} ảnh tham chiếu`, 'warning');
                            }
                        });

                        // Start status timer when generating
                        this.$watch('$wire.isGenerating', (val) => {
                            if (val) {
                                this.uiMode = 'generating';
                                this.startStatusTimer();
                                this.startLoading();
                            } else {
                                this.stopLoading();
                                this.stopStatusTimer();
                                // Failsafe: if stopped but mode is still generating (no success/fail event), reset
                                if (this.uiMode === 'generating') {
                                    this.uiMode = 'idle';
                                }
                            }
                        });

                        // Update historyData after Livewire re-renders
                        if (window.Livewire?.hook) {
                            this._morphCleanup = Livewire.hook('morph.updated', ({ el }) => {
                                if (el.id === 'gallery-feed' || el.querySelector?.('#gallery-feed')) {
                                    const dataEl = document.getElementById('gallery-feed');
                                    if (!dataEl) return;

                                    if (dataEl.dataset?.hasMore !== undefined) {
                                        this.hasMoreHistory = dataEl.dataset.hasMore === '1';
                                    }

                                    if (dataEl?.dataset?.history) {
                                        try {
                                            this.syncHistoryData(JSON.parse(dataEl.dataset.history));
                                            if (this.showPreview && this.previewImage) {
                                                const currentId = this.previewImage.id ?? null;
                                                let nextIndex = -1;
                                                if (currentId !== null) {
                                                    nextIndex = this.historyData.findIndex((img) => img.id === currentId);
                                                } else if (this.previewImage.url) {
                                                    nextIndex = this.historyData.findIndex((img) => img.url === this.previewImage.url);
                                                }
                                                if (nextIndex === -1) {
                                                    this.closePreview();
                                                } else {
                                                    this.previewIndex = nextIndex;
                                                    this.previewImage = this.historyData[nextIndex];
                                                }
                                            }
                                        } catch (e) { }
                                    }

                                    // Restore scroll position IMMEDIATELY after morph (before paint)
                                    if (this.isPrependingHistory && this._anchorId) {
                                        this._restoreScrollPosition();
                                        // Null out to prevent double-execution in historyUpdated
                                    }

                                    // Don't re-observe during prepend — historyUpdated handler does it
                                    if (!this.isPrependingHistory) {
                                        this._reobserveSentinel();
                                    }
                                    this.maybeBootstrapHistory();
                                }
                            });
                        }

                        // Cleanup on SPA navigation
                        this._onNavigating = () => {
                            this._cleanup();
                        };
                        document.addEventListener('livewire:navigating', this._onNavigating, { once: true });
                    },

                    // ============================================================
                    // Status Timer
                    // ============================================================
                    startStatusTimer() {
                        this.statusElapsed = 0;
                        this.stopStatusTimer();
                        this.statusTimer = setInterval(() => this.statusElapsed++, 1000);
                    },
                    stopStatusTimer() {
                        clearInterval(this.statusTimer);
                        this.statusTimer = null;
                    },

                    // Centralized cleanup
                    _cleanup() {
                        if (typeof this._morphCleanup === 'function') {
                            this._morphCleanup();
                            this._morphCleanup = null;
                        }
                        if (this._scrollHandler) {
                            window.removeEventListener('scroll', this._scrollHandler);
                            this._scrollHandler = null;
                        }
                        if (this._resizeHandler) {
                            window.removeEventListener('resize', this._resizeHandler);
                            this._resizeHandler = null;
                        }
                        if (this._sentinelObserver) {
                            this._sentinelObserver.disconnect();
                            this._sentinelObserver = null;
                        }
                        if (this._onNavigating) {
                            document.removeEventListener('livewire:navigating', this._onNavigating);
                            this._onNavigating = null;
                        }
                        if (Array.isArray(this._wireListeners) && this._wireListeners.length) {
                            this._wireListeners.forEach((off) => {
                                if (typeof off === 'function') off();
                            });
                            this._wireListeners = [];
                        }
                        this.stopLoading();
                        this.stopStatusTimer();
                        clearTimeout(this._loadMoreFailSafeTimer);
                        this._loadMoreFailSafeTimer = null;
                        if (this._sentinelObserver) {
                            this._sentinelObserver.disconnect();
                            this._sentinelObserver = null;
                        }
                        this.loadingMoreHistory = false;
                        this.isPrependingHistory = false;
                        document.body.style.overflow = '';
                        document.documentElement.style.overflow = '';
                        if (this._scrollRestoration !== null && 'scrollRestoration' in history) {
                            history.scrollRestoration = this._scrollRestoration;
                            this._scrollRestoration = null;
                        }
                    },

                    destroy() {
                        this._cleanup();
                    },

                    // ============================================================
                    // Input Settings
                    // ============================================================
                    selectRatio(id) {
                        this.selectedRatio = id;
                        if (id !== 'auto') {
                            const [w, h] = id.split(':').map(Number);
                            const baseSize = 1024;
                            this.customWidth = Math.round(baseSize * Math.sqrt(w / h) / 64) * 64;
                            this.customHeight = Math.round(baseSize * Math.sqrt(h / w) / 64) * 64;
                        }
                        if (window.innerWidth >= 640) {
                            this.showRatioSheet = false;
                        }
                    },
                    selectModel(id) {
                        this.selectedModel = id;
                        this.showModelSheet = false;
                    },
                    getSelectedModel() {
                        return this.models.find(m => m.id === this.selectedModel) || this.models[0] || { name: 'Model', icon: '🛠️', desc: '' };
                    },

                    // ============================================================
                    // Scroll helpers
                    // ============================================================
                    scrollToBottom(smooth = true) {
                        const targetTop = document.documentElement.scrollHeight;
                        if (smooth) {
                            window.scrollTo({ top: targetTop, behavior: 'smooth' });
                        } else {
                            // Override CSS scroll-behavior: smooth → instant teleport
                            const html = document.documentElement;
                            html.style.scrollBehavior = 'auto';
                            window.scrollTo(0, targetTop);
                            requestAnimationFrame(() => { html.style.scrollBehavior = ''; });
                        }
                        this.showScrollToBottom = false;
                    },
                    isNearTop(threshold = 200) {
                        return document.documentElement.scrollTop < threshold;
                    },
                    isNearBottom(threshold = 200) {
                        const el = document.documentElement;
                        return (el.scrollHeight - (el.scrollTop + window.innerHeight)) < threshold;
                    },
                    syncHistoryData(items) {
                        this.historyData = Array.isArray(items) ? items : [];
                    },

                    // ============================================================
                    // IntersectionObserver for top sentinel (re-observe after morph)
                    // ============================================================
                    _reobserveSentinel() {
                        if (this._sentinelObserver) {
                            this._sentinelObserver.disconnect();
                            this._sentinelObserver = null;
                        }
                        const sentinel = document.getElementById('load-older-sentinel');
                        if (!sentinel) return;

                        this._sentinelObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (!entry.isIntersecting) return;
                                if (this.loadingMoreHistory || !this.hasMoreHistory) return;
                                this.requestLoadOlder(this.loadOlderStep);
                            });
                        }, { rootMargin: '400px 0px 0px 0px', threshold: 0 });
                        this._sentinelObserver.observe(sentinel);
                    },


                    // ============================================================
                    // Save anchor element before prepending older images
                    // ============================================================
                    capturePrependAnchor() {
                        const groups = Array.from(
                            document.querySelectorAll('#gallery-feed .group-batch[data-history-anchor-id]')
                        );
                        // Find the first batch whose bottom is visible (= top of visible area)
                        const anchor = groups.find(el => el.getBoundingClientRect().bottom > 0)
                            || groups[0] || null;
                        this._anchorId = anchor?.dataset?.historyAnchorId ?? null;
                        this._anchorTop = anchor ? anchor.getBoundingClientRect().top : 0;
                    },

                    /**
                     * Restore scroll so the anchor element stays at the same visual position.
                     * New content appears above — user sees no jump.
                     */
                    _restoreScrollPosition() {
                        if (!this._anchorId) return;
                        const anchor = document.querySelector(
                            `#gallery-feed .group-batch[data-history-anchor-id="${this._anchorId}"]`
                        );
                        this._anchorId = null; // prevent double-execution
                        if (!anchor) return;
                        const newTop = anchor.getBoundingClientRect().top;
                        const diff = newTop - this._anchorTop;
                        if (Math.abs(diff) > 1) {
                            const html = document.documentElement;
                            html.style.scrollBehavior = 'auto';
                            window.scrollBy(0, diff);
                            requestAnimationFrame(() => { html.style.scrollBehavior = ''; });
                        }
                    },

                    // ============================================================
                    // Center latest batch (after image generation)
                    // ============================================================
                    centerLatestBatch(smooth = false) {
                        const batches = Array.from(document.querySelectorAll('#gallery-feed .group-batch'));
                        const latest = batches[batches.length - 1];
                        if (!latest) return false;

                        // Target the first image in the batch for centering
                        const firstImg = latest.querySelector('img');
                        const target = firstImg || latest;
                        const rect = target.getBoundingClientRect();
                        const currentY = window.scrollY || document.documentElement.scrollTop || 0;

                        // Center the first image vertically in the viewport
                        const fitsViewport = rect.height < window.innerHeight;
                        const desiredTop = fitsViewport
                            ? currentY + rect.top - ((window.innerHeight - rect.height) / 2)
                            : currentY + rect.top - 24;

                        const top = Math.max(0, Math.round(desiredTop));
                        if (smooth) {
                            window.scrollTo({ top, behavior: 'smooth' });
                        } else {
                            window.scrollTo(0, top);
                        }
                        return true;
                    },
                    centerLatestBatchWhenReady() {
                        const batches = Array.from(document.querySelectorAll('#gallery-feed .group-batch'));
                        const latest = batches[batches.length - 1];
                        if (!latest) return false;

                        const images = Array.from(latest.querySelectorAll('img'));
                        const pending = images.filter((img) => !img.complete);
                        if (!pending.length) return this.centerLatestBatch(true);

                        let settled = false;
                        let timeoutId = null;
                        const cleanupFns = [];

                        const settle = () => {
                            if (settled) return;
                            settled = true;
                            if (timeoutId) { clearTimeout(timeoutId); timeoutId = null; }
                            cleanupFns.forEach((fn) => fn());
                            cleanupFns.length = 0;
                            requestAnimationFrame(() => {
                                const centered = this.centerLatestBatch(true);
                                if (centered) {
                                    requestAnimationFrame(() => this.centerLatestBatch(false));
                                }
                            });
                        };

                        pending.forEach((img) => {
                            const onLoad = () => settle();
                            const onError = () => settle();
                            img.addEventListener('load', onLoad, { once: true });
                            img.addEventListener('error', onError, { once: true });
                            cleanupFns.push(() => {
                                img.removeEventListener('load', onLoad);
                                img.removeEventListener('error', onError);
                            });
                        });
                        timeoutId = setTimeout(settle, 1500);
                        return true;
                    },

                    // ============================================================
                    // Bootstrap & load older
                    // ============================================================
                    maybeBootstrapHistory() {
                        if (!this.hasMoreHistory || this.loadingMoreHistory) return;
                        const el = document.documentElement;
                        const canScroll = (el.scrollHeight - window.innerHeight) > 8;
                        if (canScroll) return;
                        this.requestLoadOlder(2);
                    },
                    requestLoadOlder(count = null) {
                        if (!this.hasMoreHistory || this.loadingMoreHistory) return;
                        const now = Date.now();
                        if (now - this.lastLoadMoreAt < 500) return;
                        this.lastLoadMoreAt = now;

                        this.capturePrependAnchor();
                        this.loadingMoreHistory = true;
                        this.isPrependingHistory = true;

                        const batch = Math.max(1, Math.min(12, Math.trunc(count || this.loadOlderStep)));
                        this.$wire.loadMore(batch);

                        clearTimeout(this._loadMoreFailSafeTimer);
                        this._loadMoreFailSafeTimer = setTimeout(() => {
                            this.loadingMoreHistory = false;
                            this.isPrependingHistory = false;
                            this._loadMoreFailSafeTimer = null;
                        }, 7000);
                    },

                    // ============================================================
                    // Toast
                    // ============================================================
                    notify(msg, type = 'success') {
                        this.toastMessage = msg;
                        this.toastType = type;
                        this.showToast = true;
                        clearTimeout(this.toastTimer);
                        this.toastTimer = setTimeout(() => this.showToast = false, 3000);
                    },

                    // ============================================================
                    // Loading messages rotation
                    // ============================================================
                    startLoading() {
                        this.stopLoading();
                        this.currentLoadingMessage = 0;
                        this.loadingInterval = setInterval(() => {
                            this.currentLoadingMessage = (this.currentLoadingMessage + 1) % this.loadingMessages.length;
                        }, 3000);
                    },
                    stopLoading() {
                        clearInterval(this.loadingInterval);
                        this.loadingInterval = null;
                    },

                    // ============================================================
                    // Preview
                    // ============================================================
                    openPreview(url, index) {
                        if (index !== null && index !== undefined && this.historyData[index]) {
                            this.previewIndex = index;
                            this.previewImage = this.historyData[index];
                        } else if (url) {
                            this.previewImage = { url: url, prompt: '' };
                            this.previewIndex = 0;
                        }
                        this.showPreview = true;
                        document.body.style.overflow = 'hidden';
                        document.documentElement.style.overflow = 'hidden';
                    },
                    closePreview() {
                        this.showPreview = false;
                        document.body.style.overflow = '';
                        document.documentElement.style.overflow = '';
                    },
                    nextImage() {
                        if (this.previewIndex < this.historyData.length - 1) {
                            this.previewIndex++;
                            this.previewImage = this.historyData[this.previewIndex];
                        }
                    },
                    prevImage() {
                        if (this.previewIndex > 0) {
                            this.previewIndex--;
                            this.previewImage = this.historyData[this.previewIndex];
                        }
                    },
                    goToImage(index) {
                        if (index >= 0 && index < this.historyData.length) {
                            this.previewIndex = index;
                            this.previewImage = this.historyData[index];
                        }
                    },
                    goToDot(position) {
                        const dot = this.previewDotAt(position);
                        if (dot) {
                            this.goToImage(dot.idx);
                        }
                    },
                    previewDotLabel(position) {
                        const dot = this.previewDotAt(position);
                        const index = dot && Number.isFinite(dot.idx) ? dot.idx : position;
                        return 'Anh ' + (index + 1);
                    },
                    dotClassAt(position, withHover = false) {
                        return this.dotButtonClass(this.previewDotAt(position), withHover);
                    },
                    previewDots() {
                        const len = Array.isArray(this.historyData) ? this.historyData.length : 0;
                        if (len <= 0) return [];

                        if (len <= 7) {
                            return Array.from({ length: len }, (_, i) => ({
                                id: `dot-${i}`,
                                idx: i,
                                size: 'normal',
                            }));
                        }

                        const center = Math.max(0, Math.min(this.previewIndex, len - 1));
                        const start = Math.max(0, Math.min(center - 3, len - 7));
                        const end = Math.min(len, start + 7);

                        return Array.from({ length: end - start }, (_, i) => {
                            const idx = start + i;
                            const isEdge = (idx === start || idx === end - 1) && len > 7;
                            return {
                                id: `dot-${idx}`,
                                idx,
                                size: isEdge ? 'small' : 'normal',
                            };
                        });
                    },
                    previewDotAt(position) {
                        const dots = this.previewDots();
                        return (position >= 0 && position < dots.length) ? dots[position] : null;
                    },
                    dotButtonClass(dot, withHover = false) {
                        if (!dot) return 'w-2 h-2 bg-white/30';

                        const active = dot.idx === this.previewIndex;
                        const small = dot.size === 'small';

                        if (active) {
                            return small ? 'w-1.5 h-1.5 bg-purple-400' : 'w-2.5 h-2.5 bg-purple-400';
                        }

                        if (small) {
                            return 'w-1 h-1 bg-white/30';
                        }

                        return withHover ? 'w-2 h-2 bg-white/40 hover:bg-white/60' : 'w-2 h-2 bg-white/40';
                    },

                    // ============================================================
                    // Keyboard
                    // ============================================================
                    handleKeydown(e) {
                        if (this.showPreview) {
                            if (e.key === 'ArrowLeft') this.prevImage();
                            if (e.key === 'ArrowRight') this.nextImage();
                            if (e.key === 'Escape') this.closePreview();
                        }
                    },

                    // ============================================================
                    // Preview Actions
                    // ============================================================
                    copyPrompt() {
                        if (this.previewImage?.prompt) {
                            navigator.clipboard.writeText(this.previewImage.prompt);
                            this.notify('Đã copy prompt');
                        }
                    },
                    async shareImage() {
                        const url = this.previewImage?.url;
                        if (!url) return;
                        if (navigator.share) {
                            try {
                                await navigator.share({ title: 'AI Generated Image', url: url });
                            } catch (e) { /* user cancelled */ }
                        } else {
                            navigator.clipboard.writeText(url);
                            this.notify('Đã copy link ảnh');
                        }
                    },
                    useAsReference() {
                        const url = this.previewImage?.url;
                        if (!url) return;
                        if (this.maxImages === 0) {
                            this.notify('Model này không hỗ trợ ảnh tham chiếu', 'warning');
                            return;
                        }
                        if (this.selectedImages.some(i => i.url === url)) {
                            this.notify('Ảnh đã có trong danh sách', 'warning');
                            this.closePreview();
                            return;
                        }
                        if (this.selectedImages.length >= this.maxImages) {
                            this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                            return;
                        }
                        this.selectedImages.push({ id: Date.now(), url: url });
                        this.$wire.setReferenceImages(
                            this.selectedImages.map(img => ({ url: img.url }))
                        );
                        this.closePreview();
                        this.notify('Đã thêm ảnh làm tham chiếu');
                    },

                    // Touch swipe for mobile preview
                    touchStartX: 0,
                    handleTouchStart(e) {
                        this.touchStartX = e.touches[0].clientX;
                    },
                    handleTouchEnd(e) {
                        const diff = e.changedTouches[0].clientX - this.touchStartX;
                        if (Math.abs(diff) > 50) {
                            if (diff > 0) this.prevImage();
                            else this.nextImage();
                        }
                    },
                    async downloadImage(url) {
                        try {
                            const res = await fetch(url);
                            const blob = await res.blob();
                            // P1#2: detect correct extension from blob type
                            const extMap = { 'image/jpeg': '.jpg', 'image/png': '.png', 'image/webp': '.webp', 'image/gif': '.gif' };
                            const ext = extMap[blob.type] || '.png';
                            const a = document.createElement('a');
                            a.href = URL.createObjectURL(blob);
                            a.download = 'zdream-' + Date.now() + ext;
                            a.click();
                            URL.revokeObjectURL(a.href);
                        } catch (e) {
                            window.open(url, '_blank');
                        }
                    },

                    // ============================================================
                    // Image Picker (inline in composer)
                    // ============================================================
                    async loadRecentImages() {
                        this.isLoadingPicker = true;
                        try {
                            const res = await fetch('/api/user/recent-images');
                            if (res.ok) this.recentImages = await res.json();
                        } catch (e) {
                            console.error(e);
                            this.notify('Không tải được ảnh gần đây', 'error');
                        }
                        this.isLoadingPicker = false;
                    },

                    handleFileSelect(e) {
                        const files = Array.from(e.target.files);
                        this.processFiles(files);
                        e.target.value = '';
                    },

                    processFiles(files) {
                        const remaining = this.maxImages - this.selectedImages.length;
                        const toProcess = files.slice(0, remaining);
                        const skipped = files.length - toProcess.length;
                        let processed = 0;
                        const total = toProcess.length;

                        if (total === 0 && skipped > 0) {
                            this.notify(`Đã đạt giới hạn ${this.maxImages} ảnh`, 'warning');
                            return;
                        }

                        toProcess.forEach(file => {
                            if (file.size > 10 * 1024 * 1024) {
                                this.notify('Ảnh quá lớn (tối đa 10MB)', 'error');
                                processed++;
                                return;
                            }
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                this.selectedImages.push({
                                    id: Date.now() + Math.random(),
                                    url: ev.target.result,
                                    file: file
                                });
                                processed++;
                                if (processed >= total) {
                                    this.$wire.setReferenceImages(
                                        this.selectedImages.map(img => ({ url: img.url }))
                                    );
                                    if (skipped > 0) {
                                        this.notify(`Đã thêm ${total} ảnh, bỏ ${skipped} (vượt giới hạn ${this.maxImages})`, 'warning');
                                    }
                                }
                            };
                            reader.readAsDataURL(file);
                        });
                    },

                    addFromUrl() {
                        const url = this.urlInput.trim();
                        if (!url) return;
                        if (!/^https?:\/\//i.test(url)) {
                            this.notify('URL phải bắt đầu bằng http:// hoặc https://', 'warning');
                            return;
                        }
                        if (this.selectedImages.some(i => i.url === url)) {
                            this.notify('Ảnh đã có trong danh sách', 'warning');
                            return;
                        }
                        if (this.selectedImages.length >= this.maxImages) {
                            this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                            return;
                        }
                        this.selectedImages.push({ id: Date.now(), url: url });
                        this.$wire.setReferenceImages(
                            this.selectedImages.map(img => ({ url: img.url }))
                        );
                        this.urlInput = '';
                    },

                    selectFromRecent(url) {
                        const idx = this.selectedImages.findIndex(i => i.url === url);
                        if (idx > -1) {
                            this.selectedImages.splice(idx, 1);
                        } else {
                            if (this.selectedImages.length >= this.maxImages) {
                                this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                                return;
                            }
                            this.selectedImages.push({ id: Date.now(), url: url });
                        }
                        this.$wire.setReferenceImages(
                            this.selectedImages.map(img => ({ url: img.url }))
                        );
                    },

                    isSelected(url) {
                        return this.selectedImages.some(i => i.url === url);
                    },

                    removeImage(id) {
                        this.selectedImages = this.selectedImages.filter(i => i.id !== id);
                        this.$wire.setReferenceImages(
                            this.selectedImages.map(img => ({ url: img.url }))
                        );
                    },

                    clearAll() {
                        this.selectedImages = [];
                        this.$wire.setReferenceImages([]);
                    },
                }));
            };

            if (window.Alpine) {
                registerTextToImage();
            } else {
                document.addEventListener('alpine:init', registerTextToImage, { once: true });
            }
        })();
    </script>
    @endscript

</div>