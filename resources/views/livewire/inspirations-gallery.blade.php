<section class="px-2 sm:px-4 pt-2 pb-4 sm:pt-4 sm:pb-8" wire:ignore x-data="{
        items: {{ \Illuminate\Support\Js::from($initialInspirations) }},
        columns: [[], [], [], []],
        columnCount: 4,
        loading: false,
        hasMore: {{ $hasMore ? 'true' : 'false' }},
        
        lastDistributedIndex: 0,
        activeInspiration: null,
        scrollPos: 0,

        init() {
            this.updateColumnCount();
            this.redistributeAll(); // Resizes columns to match count
            
            // Re-distribute on resize
            window.addEventListener('resize', () => {
                let oldCols = this.columnCount;
                this.updateColumnCount();
                if (oldCols !== this.columnCount) {
                    this.redistributeAll();
                }
            });

            // Close modal on escape
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.activeInspiration = null;
            });

            // Toggle body scroll (Aggressive)
            this.$watch('activeInspiration', value => {
                if (value) {
                    document.documentElement.style.setProperty('overflow', 'hidden', 'important');
                    document.body.style.setProperty('overflow', 'hidden', 'important');
                } else {
                    document.documentElement.style.removeProperty('overflow');
                    document.body.style.removeProperty('overflow');
                }
            });
        },
        
        updateColumnCount() {
            let width = window.innerWidth;
            if (width < 640) this.columnCount = 2; // Mobile
            else if (width < 1024) this.columnCount = 3; // Tablet
            else this.columnCount = 4; // Desktop
        },

        redistributeAll() {
            // Full reset only on resize
            this.columns = Array.from({ length: this.columnCount }, () => []);
            this.lastDistributedIndex = 0;
            this.distributeNewItems();
        },
        
        distributeNewItems() {
            // Only distribute items we haven't processed yet
            for (let i = this.lastDistributedIndex; i < this.items.length; i++) {
                // Ensure column exists
                if (!this.columns[i % this.columnCount]) {
                   this.columns[i % this.columnCount] = [];
                }
                this.columns[i % this.columnCount].push(this.items[i]);
            }
            this.lastDistributedIndex = this.items.length;
        },
        
        async loadMoreItems() {
            if (this.loading || !this.hasMore) return;
            
            this.loading = true;
            try {
                // Call Livewire method directly
                let newItems = await $wire.loadMore();
                
                if (newItems && newItems.length > 0) {
                    // Append to master list
                    // We need to trigger reactivity for distribute but limit DOM thrashing
                    this.items.push(...newItems);
                    
                    // Distribute ONLY the new items
                    this.distributeNewItems();
                } else {
                    this.hasMore = false;
                }
            } catch (e) {
                console.error(e);
                this.loading = false; // Ensure loading is reset on error
            }
            // Note: We don't necessarily reset loading to false immediately if we want to throttle,
            // but here we should reset it.
            this.loading = false;
        }
    }">
    <div class="text-left mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#d3d6db] mb-2">Inspiration Gallery</h2>
        <p class="text-white/50 text-sm sm:text-base">Khám phá ý tưởng sáng tạo từ cộng đồng</p>
    </div>

    <!-- Flex Masonry Grid -->
    <div class="flex gap-1 sm:gap-1.5 align-top">
        <template x-for="(colItems, colIndex) in columns" :key="colIndex">
            <div class="flex-1 flex flex-col gap-1 sm:gap-1.5 min-w-0">
                <template x-for="inspiration in colItems" :key="inspiration.id">
                    <div class="group relative break-inside-avoid" x-data="{ imgLoaded: false }">
                        <div @click="activeInspiration = inspiration"
                            class="inspiration-card relative overflow-hidden rounded-lg bg-[#1b1c21] border border-[#2a2b30] transition-all duration-300 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10 cursor-pointer">
                            <!-- Skeleton Loading -->
                            <div x-show="!imgLoaded"
                                class="skeleton-loader aspect-square bg-gradient-to-r from-[#1b1c21] via-[#2a2b30] to-[#1b1c21] bg-[length:200%_100%] animate-pulse">
                                <!-- Icon placeholder -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <i class="fa-solid fa-image text-white/10 text-2xl"></i>
                                </div>
                            </div>

                            <!-- Image -->
                            <img :src="inspiration.image_url" alt="Inspiration"
                                class="inspiration-img w-full object-cover transition-all duration-500 group-hover:scale-105"
                                :class="imgLoaded ? 'relative h-auto opacity-100' : 'absolute inset-0 h-full opacity-0'"
                                loading="lazy" decoding="async" @load="imgLoaded = true">

                            <!-- Reference Images Grid (Top Left) -->
                            <template x-if="inspiration.ref_images && inspiration.ref_images.length > 0">
                                <div
                                    class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none group-hover:pointer-events-auto">
                                    <div
                                        class="bg-black/60 backdrop-blur-md rounded-lg p-1.5 border border-white/10 shadow-xl">
                                        <div class="grid gap-1.5"
                                            :class="inspiration.ref_images.length > 1 ? 'grid-cols-2' : 'grid-cols-1'">
                                            <template x-for="(refImg, idx) in inspiration.ref_images.slice(0, 4)"
                                                :key="idx">
                                                <div
                                                    class="relative w-10 h-10 rounded overflow-hidden bg-white/5 border border-white/10">
                                                    <img :src="refImg" class="w-full h-full object-cover">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Hover Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3 sm:p-4 pointer-events-none">
                                <p class="text-white/90 text-xs sm:text-sm line-clamp-4 leading-relaxed"
                                    x-text="inspiration.prompt.length > 150 ? inspiration.prompt.substring(0, 150) + '...' : inspiration.prompt">
                                </p>

                                <template x-if="inspiration.ref_images && inspiration.ref_images.length > 0">
                                    <div class="mt-2 flex items-center gap-1.5">
                                        <i class="fa-solid fa-images text-purple-400 text-xs"></i>
                                        <span class="text-purple-300 text-xs"
                                            x-text="inspiration.ref_images.length + ' ảnh tham chiếu'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </div>

    <!-- Infinite Scroll Trigger -->
    <div x-show="hasMore" x-intersect.margin.2000px="loadMoreItems()" class="flex justify-center p-4 mt-4">
        <div x-show="loading" class="flex items-center gap-2 text-white/50">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Đang tải thêm...</span>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="activeInspiration" style="display: none;"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/90 backdrop-blur-xl" @click="activeInspiration = null"></div>

        <!-- Modal Content -->
        <div class="relative w-full max-w-6xl h-full md:h-auto md:max-h-[90vh] bg-[#15161A] border-0 md:border border-white/10 rounded-none md:rounded-2xl shadow-2xl flex flex-col md:flex-row-reverse overflow-hidden"
            @click.stop x-transition:enter="transition ease-out duration-300 delay-100"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95">
            <!-- Close Button -->
            <button @click="activeInspiration = null"
                class="absolute top-4 right-4 z-50 w-10 h-10 flex items-center justify-center text-white/70 hover:text-white bg-black/50 hover:bg-black/70 rounded-full backdrop-blur-md transition-all shadow-lg">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <!-- Right: Main Image (Mobile: Top) -->
            <div
                class="w-full md:flex-1 bg-black/50 relative flex items-center justify-center p-4 md:p-8 h-[40vh] md:h-auto shrink-0">
                <template x-if="activeInspiration">
                    <img :src="activeInspiration.image_url"
                        class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
                </template>
            </div>

            <!-- Left: Reference Images & Details (Mobile: Bottom) -->
            <div
                class="w-full md:w-96 lg:w-[28rem] border-t md:border-t-0 md:border-r border-white/5 bg-[#0F1014] p-5 md:p-6 overflow-y-auto custom-scrollbar flex flex-col gap-6 h-full">
                <div>
                    <h3 class="text-white/70 font-semibold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-images text-purple-400"></i> Ảnh tham chiếu
                    </h3>
                    <template
                        x-if="activeInspiration && activeInspiration.ref_images && activeInspiration.ref_images.length > 0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-2 gap-2 md:gap-3">
                            <template x-for="(refImg, idx) in activeInspiration.ref_images" :key="idx">
                                <div class="relative aspect-square rounded-lg overflow-hidden border border-white/10 group/ref cursor-pointer"
                                    @click="window.open(refImg, '_blank')">
                                    <img :src="refImg"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover/ref:scale-110">
                                </div>
                            </template>
                        </div>
                    </template>
                    <template
                        x-if="!activeInspiration || !activeInspiration.ref_images || activeInspiration.ref_images.length === 0">
                        <div
                            class="text-white/30 text-sm italic py-8 text-center border border-dashed border-white/10 rounded-lg">
                            Không có ảnh tham chiếu
                        </div>
                    </template>
                </div>

                <!-- Prompt Section -->
                <div class="mt-auto">
                    <h3 class="text-white/70 font-semibold mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-quote-left text-purple-400"></i> Prompt
                    </h3>
                    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                        <p class="text-gray-300 text-xs sm:text-sm leading-relaxed max-h-32 md:max-h-40 overflow-y-auto custom-scrollbar"
                            x-text="activeInspiration ? activeInspiration.prompt : ''"></p>
                    </div>
                </div>

                <!-- Action Button -->
                <button
                    class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-bold rounded-xl shadow-lg shadow-purple-500/25 transition-all flex items-center justify-center gap-2 group/btn shrink-0">
                    <span>Phối lại ảnh này</span>
                    <i class="fa-solid fa-wand-magic-sparkles transition-transform group-hover/btn:rotate-12"></i>
                </button>
            </div>
        </div>
    </div>
</section>