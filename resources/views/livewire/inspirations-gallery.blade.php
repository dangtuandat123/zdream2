<section class="px-2 sm:px-4 py-8 sm:py-12" wire:ignore x-data="{
        items: {{ \Illuminate\Support\Js::from($initialInspirations) }},
        columns: [[], [], [], []],
        columnCount: 4,
        loading: false,
        hasMore: {{ $hasMore ? 'true' : 'false' }},
        
        lastDistributedIndex: 0,

        init() {
            this.updateColumnCount();
            this.distributeNewItems();
            
            // Re-distribute on resize
            window.addEventListener('resize', () => {
                let oldCols = this.columnCount;
                this.updateColumnCount();
                if (oldCols !== this.columnCount) {
                    this.redistributeAll();
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
            this.loading = false;
        }
    }">
    <div class="text-center mb-8 sm:mb-10">
        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#d3d6db] mb-2">Inspiration Gallery</h2>
        <p class="text-white/50 text-sm sm:text-base">Khám phá ý tưởng sáng tạo từ cộng đồng</p>
    </div>

    <!-- Flex Masonry Grid -->
    <div class="flex gap-1 sm:gap-1.5 align-top">
        <template x-for="(colItems, colIndex) in columns" :key="colIndex">
            <div class="flex-1 flex flex-col gap-1 sm:gap-1.5">
                <template x-for="inspiration in colItems" :key="inspiration.id">
                    <div class="group relative break-inside-avoid">
                        <div
                            class="inspiration-card relative overflow-hidden rounded-lg bg-[#1b1c21] border border-[#2a2b30] transition-all duration-300 hover:border-purple-500/40 hover:shadow-lg hover:shadow-purple-500/10">
                            <!-- Skeleton Loading -->
                            <div
                                class="skeleton-loader aspect-square bg-gradient-to-r from-[#1b1c21] via-[#2a2b30] to-[#1b1c21] bg-[length:200%_100%] animate-pulse">
                                <!-- Icon placeholder -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <i class="fa-solid fa-image text-white/10 text-2xl"></i>
                                </div>
                            </div>

                            <!-- Image -->
                            <img :src="inspiration.image_url" alt="Inspiration"
                                class="inspiration-img absolute inset-0 w-full h-full object-cover transition-all duration-500 opacity-0 group-hover:scale-105"
                                loading="lazy" decoding="async"
                                onload="this.classList.remove('opacity-0'); this.classList.add('opacity-100'); this.previousElementSibling.style.display='none'; this.classList.remove('absolute', 'inset-0', 'h-full'); this.classList.add('relative', 'h-auto');">

                            <!-- Hover Overlay -->
                            <div
                                class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-3 sm:p-4 pointer-events-none group-hover:pointer-events-auto">
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
    <div x-show="hasMore" x-intersect.threshold.50="loadMoreItems()" class="flex justify-center p-4 mt-4">
        <div x-show="loading" class="flex items-center gap-2 text-white/50">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Đang tải thêm...</span>
        </div>
    </div>
</section>