{{-- ============================================================ --}}
{{-- TEXT-TO-IMAGE — Root Orchestrator (Redesigned: Core-first) --}}
{{-- ============================================================ --}}
<div class="relative min-h-screen" @if($isGenerating) wire:poll.2s="pollImageStatus" @endif x-data="textToImage"
    @keydown.window="handleKeydown($event)"
    @show-toast.window="notify($event.detail.message, $event.detail.type || 'success')">

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
        $historyCollection = $historyCollection->sortByDesc('created_at')->values();
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
            ->map(fn($items) => collect($items)->sortByDesc('created_at')->values())
            ->sortByDesc(function ($items) {
                $first = $items->first();
                return $first && $first->created_at ? $first->created_at->getTimestamp() : 0;
            });

        // 2. Flatten for JS (keep reversed order) — Fix 7: map model_id to friendly name
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
        .safe-area-bottom {
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .safe-area-top {
            padding-top: env(safe-area-inset-top, 0px);
        }

        /* New image entrance animation */

        /* New image entrance animation */
        @keyframes image-entrance {
            from {
                opacity: 0;
                transform: scale(0.92) translateY(12px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .new-batch-animate {
            animation: image-entrance 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .new-batch-glow {
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.3), 0 0 40px rgba(168, 85, 247, 0.1);
            border-radius: 0.5rem;
            transition: box-shadow 3s ease-out;
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
            animation: shimmer 2s infinite;
        }

        @keyframes progress-slide {

            0%,
            100% {
                opacity: 0.4;
                transform: translateX(-30%);
            }

            50% {
                opacity: 1;
                transform: translateX(0%);
            }
        }

        /* Fix composer for md+ */
        .composer-fixed {
            bottom: 0 !important;
        }

        /* Fix 3: Force gallery images to be visible, don't rely on JS class */
        .gallery-img {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>

    {{-- ============================================================ --}}
    {{-- ALPINE DATA --}}
    {{-- ============================================================ --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('textToImage', () => ({
                // ── UI State ──────────────────────────────────────
                uiMode: 'idle', // idle | generating | partial_success | failed | done
                statusMessage: '',
                statusElapsed: 0,
                statusTimer: null,
                autoScrollEnabled: true, // Auto-follow newest content (top)
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

                selectedRatio: @entangle('aspectRatio'),
                selectedModel: @entangle('modelId'),
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

                // Dynamic max images based on selected model
                get maxImages() {
                    const model = this.models.find(m => m.id === this.selectedModel);
                    return model?.maxImages ?? 0;
                },

                // ============================================================
                // INIT
                // ============================================================
                init() {
                    // Fix: Removed initial scroll to bottom on mount

                    // Fix: Scroll listener to disable auto-scroll when user scrolls up
                    window.addEventListener('scroll', () => {
                        if (!this.autoScrollEnabled) return;
                        // If user scrolls up significantly (not at bottom), disable auto-scroll
                        if (!this.isNearTop(120)) {
                            this.autoScrollEnabled = false;
                            // Optional: notify('Auto-scroll tắt');
                        }
                    }, { passive: true });

                    // Image generated → scroll + celebrate
                    this.$wire.$on('imageGenerated', (params) => {
                        const { successCount, failedCount } = Array.isArray(params) ? params[0] || {} : params || {};
                        this.$nextTick(() => {
                            setTimeout(() => {
                                if (this.autoScrollEnabled || this.isNearTop(240)) {
                                    this.scrollToTop(true);
                                    setTimeout(() => {
                                        this.scrollToTop(false);
                                        this.autoScrollEnabled = true;
                                    }, 100);
                                    this.showScrollToBottom = false;
                                } else {
                                    this.showScrollToBottom = true;
                                }
                                const batches = document.querySelectorAll('.group-batch');
                                const firstBatch = batches[0];
                                if (firstBatch) {
                                    firstBatch.classList.add('new-batch-animate', 'new-batch-glow');
                                    setTimeout(() => firstBatch.classList.remove('new-batch-glow'), 3000);
                                    setTimeout(() => firstBatch.classList.remove('new-batch-animate'), 600);
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

                    // Generation failed (all images)
                    this.$wire.$on('imageGenerationFailed', () => {
                        this.uiMode = 'failed';
                        this.statusMessage = '❌ Tạo ảnh thất bại. Vui lòng thử lại.';
                        this.stopStatusTimer();
                        this.notify(this.statusMessage, 'error');
                    });

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
                    this._morphCleanup = Livewire.hook('morph.updated', ({ el }) => {
                        if (el.id === 'gallery-feed' || el.querySelector?.('#gallery-feed')) {
                            const dataEl = document.getElementById('gallery-feed');
                            if (dataEl?.dataset?.history) {
                                try {
                                    this.historyData = JSON.parse(dataEl.dataset.history);
                                } catch (e) { }
                            }
                        }
                    });

                    // Cleanup on SPA navigation
                    document.addEventListener('livewire:navigating', () => {
                        this._cleanup();
                    }, { once: true });
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
                    }
                    this.stopLoading();
                    this.stopStatusTimer();
                    document.body.style.overflow = '';
                    document.documentElement.style.overflow = '';
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
                // Scroll
                // ============================================================
                scrollToTop(smooth = true) {
                    const el = document.documentElement;
                    el.scrollTo({
                        top: 0,
                        behavior: smooth ? 'smooth' : 'auto'
                    });
                    this.showScrollToBottom = false;
                },
                isNearTop(threshold = 200) {
                    const el = document.documentElement;
                    return el.scrollTop < threshold;
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
        });
    </script>

</div>
