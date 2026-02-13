{{-- ============================================================ --}}
{{-- TEXT-TO-IMAGE — Root Orchestrator --}}
{{-- ============================================================ --}}
<div class="relative min-h-screen pb-36 md:pb-28" @if($isGenerating) wire:poll.2s="pollImageStatus" @endif
    x-data="textToImage" @keydown.window="handleKeydown($event)">

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
        $groupedHistory = $historyCollection->groupBy(function ($item) {
            return $item->final_prompt . '|' .
                ($item->generation_params['model_id'] ?? '') . '|' .
                ($item->generation_params['aspect_ratio'] ?? '') . '|' .
                $item->created_at->format('Y-m-d H:i');
        })->reverse();

        // 2. Flatten for JS (keep reversed order)
        $flatHistoryForJs = $groupedHistory->flatten(1)->map(fn($img) => [
            'id' => $img->id,
            'url' => $img->image_url,
            'prompt' => $img->final_prompt,
            'model' => $img->generation_params['model_id'] ?? null,
            'ratio' => $img->generation_params['aspect_ratio'] ?? null,
            'created_at' => $img->created_at->diffForHumans(),
        ])->values()->toArray();
    @endphp

    {{-- PARTIALS --}}
    @include('livewire.partials.t2i-filter-bar')
    @include('livewire.partials.t2i-gallery')
    @include('livewire.partials.t2i-input-bar')

    {{-- MODALS --}}
    @include('livewire.partials.image-picker-modal')
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

        /* CSS-only image loading (survives Livewire morph) */
        .gallery-img {
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }

        .gallery-img.is-loaded {
            opacity: 1;
        }

        .gallery-img.is-error {
            opacity: 0.5;
            object-fit: contain;
        }

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

        /* Responsive filter bar spacing */
        @media (max-width: 640px) {
            #gallery-scroll>div:first-child {
                padding-top: 5.5rem;
            }
        }

        /* Fix input bar for md+ */
        @media (min-width: 768px) {
            .input-bar-fixed {
                bottom: 0 !important;
            }
        }
    </style>

    {{-- ============================================================ --}}
    {{-- ALPINE DATA --}}
    {{-- ============================================================ --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('textToImage', () => ({
                // Toast
                showToast: false,
                toastMessage: '',
                toastType: 'success',
                toastTimer: null,

                // Image picker
                showImagePicker: false,
                selectedImages: [],
                recentImages: [],
                isLoadingPicker: false,
                activeTab: 'upload',
                isDragging: false,
                urlInput: '',

                // Preview
                showPreview: false,
                previewIndex: 0,
                previewImage: null,
                historyData: @js($flatHistoryForJs),

                // Loading
                loadingMessages: [
                    'Đang tạo ảnh...',
                    'AI đang sáng tạo...',
                    'Đang xử lý prompt...',
                    'Đang render chi tiết...',
                    'Sắp xong rồi...'
                ],
                currentLoadingMessage: 0,
                loadingInterval: null,

                // Input Bar & Settings
                showRatioDropdown: false,
                showModelDropdown: false,
                showBatchDropdown: false,
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
                    'maxImages' => $m['max_input_images'] ?? 1,
                ])),

                // Dynamic max images based on selected model
                get maxImages() {
                    const model = this.models.find(m => m.id === this.selectedModel);
                    return model?.maxImages || 1;
                },

                // ============================================================
                // INIT
                // ============================================================
                init() {
                    // Scroll + celebrate when new image generated (with counts)
                    this.$wire.$on('imageGenerated', (params) => {
                        const { successCount, failedCount } = Array.isArray(params) ? params[0] || {} : params || {};
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.scrollToBottom(true);
                                const batches = document.querySelectorAll('.group-batch');
                                const lastBatch = batches[batches.length - 1];
                                if (lastBatch) {
                                    lastBatch.classList.add('new-batch-animate', 'new-batch-glow');
                                    setTimeout(() => lastBatch.classList.remove('new-batch-glow'), 3000);
                                    setTimeout(() => lastBatch.classList.remove('new-batch-animate'), 600);
                                }
                                if (failedCount > 0) {
                                    this.notify(`🎨 ${successCount} ảnh thành công, ${failedCount} thất bại`, 'warning');
                                } else {
                                    this.notify(`🎨 Đã tạo xong ${successCount > 1 ? successCount + ' ảnh' : 'ảnh'}!`);
                                }
                            }, 300);
                        });
                    });

                    // Handle generation failure (all images in batch failed)
                    this.$wire.$on('imageGenerationFailed', () => {
                        this.notify('❌ Tạo ảnh thất bại. Vui lòng thử lại.', 'error');
                    });

                    // Auto-trim excess images when model changes
                    this.$watch('selectedModel', () => {
                        const max = this.maxImages;
                        if (this.selectedImages.length > max) {
                            this.selectedImages = this.selectedImages.slice(0, max);
                            this.$wire.setReferenceImages(this.selectedImages.map(img => ({ url: img.url })));
                            this.notify(`Model này hỗ trợ tối đa ${max} ảnh tham chiếu`, 'warning');
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

                // Centralized cleanup (P0#3)
                _cleanup() {
                    if (typeof this._morphCleanup === 'function') {
                        this._morphCleanup();
                    }
                    this.stopLoading();
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
                        this.showRatioDropdown = false;
                    }
                },
                selectModel(id) {
                    this.selectedModel = id;
                    this.showModelDropdown = false;
                },
                getSelectedModel() {
                    return this.models.find(m => m.id === this.selectedModel) || this.models[0] || { name: 'Model', icon: '🛠️', desc: '' };
                },
                updateWidth(newWidth) {
                    this.customWidth = newWidth;
                    if (this.linkDimensions && this.selectedRatio !== 'auto') {
                        const [w, h] = this.selectedRatio.split(':').map(Number);
                        this.customHeight = Math.round(newWidth * h / w / 64) * 64;
                    }
                },
                updateHeight(newHeight) {
                    this.customHeight = newHeight;
                    if (this.linkDimensions && this.selectedRatio !== 'auto') {
                        const [w, h] = this.selectedRatio.split(':').map(Number);
                        this.customWidth = Math.round(newHeight * w / h / 64) * 64;
                    }
                },

                // ============================================================
                // Scroll
                // ============================================================
                scrollToBottom(smooth = true) {
                    const el = document.documentElement;
                    el.scrollTo({
                        top: el.scrollHeight,
                        behavior: smooth ? 'smooth' : 'instant'
                    });
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
                // Loading
                // ============================================================
                startLoading() {
                    this.stopLoading(); // Clear any existing interval first
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
                },
                closePreview() {
                    this.showPreview = false;
                    document.body.style.overflow = '';
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
                    if (this.selectedImages.length >= this.maxImages) {
                        this.notify('Tối đa ' + this.maxImages + ' ảnh', 'warning');
                        return;
                    }
                    if (!this.selectedImages.some(i => i.url === url)) {
                        this.selectedImages.push({ id: Date.now(), url: url });
                        this.$wire.setReferenceImages(
                            this.selectedImages.map(img => ({ url: img.url }))
                        );
                    }
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
                        const a = document.createElement('a');
                        a.href = URL.createObjectURL(blob);
                        a.download = 'zdream-' + Date.now() + '.png';
                        a.click();
                        URL.revokeObjectURL(a.href);
                    } catch (e) {
                        window.open(url, '_blank');
                    }
                },

                // ============================================================
                // Image Picker
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

                handleDrop(e) {
                    this.isDragging = false;
                    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                    this.processFiles(files);
                },

                handleDirectUpload(e) {
                    const files = Array.from(e.target.files);
                    this.processFiles(files);
                    e.target.value = '';
                    this.notify(files.length + ' ảnh đã thêm làm tham chiếu');
                },

                processFiles(files) {
                    const remaining = this.maxImages - this.selectedImages.length;
                    const toProcess = files.slice(0, remaining);
                    toProcess.forEach(file => {
                        if (file.size > 10 * 1024 * 1024) {
                            this.notify('Ảnh quá lớn (tối đa 10MB)', 'error');
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            this.selectedImages.push({
                                id: Date.now() + Math.random(),
                                url: ev.target.result,
                                file: file
                            });
                            this.$wire.setReferenceImages(
                                this.selectedImages.map(img => ({ url: img.url }))
                            );
                        };
                        reader.readAsDataURL(file);
                    });
                },

                addFromUrl() {
                    const url = this.urlInput.trim();
                    if (!url) return;
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

                confirmSelection() {
                    this.$wire.setReferenceImages(
                        this.selectedImages.map(img => ({ url: img.url }))
                    );
                    this.showImagePicker = false;
                    if (this.selectedImages.length > 0) {
                        this.notify(this.selectedImages.length + ' ảnh tham chiếu đã chọn');
                    }
                },
            }));
        });
    </script>

</div>