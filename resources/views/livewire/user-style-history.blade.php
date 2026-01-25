@if($userImages->isNotEmpty())
    <div 
        x-data="{ 
            open: false, 
            currentIndex: 0,
            images: @js($userImages->pluck('image_url')->toArray()),
            openGallery(index) {
                this.currentIndex = index;
                this.open = true;
                document.body.style.overflow = 'hidden';
            },
            closeGallery() {
                this.open = false;
                document.body.style.overflow = '';
            },
            next() {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            },
            prev() {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
            }
        }"
        @keydown.escape.window="if(open) closeGallery()"
        @keydown.arrow-right.window="if(open) next()"
        @keydown.arrow-left.window="if(open) prev()"
        class="bg-white/[0.03] border border-white/[0.08] rounded-xl overflow-hidden"
        wire:poll.5s
    >
        <div class="flex items-center justify-between p-4 border-b border-white/[0.05]">
            <div class="flex items-center gap-2 text-white/60">
                <i class="fa-solid fa-clock-rotate-left" style="font-size: 14px;"></i>
                <span class="font-medium text-sm">Ảnh đã tạo</span>
            </div>
            <a href="{{ route('history.index') }}" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                Xem tất cả
            </a>
        </div>
        <div class="p-3">
            <div class="grid grid-cols-3 gap-2">
                @foreach($userImages as $index => $img)
                    <button 
                        @click="openGallery({{ $index }})"
                        class="group relative aspect-square rounded-lg overflow-hidden bg-white/[0.05] cursor-pointer focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    >
                        <img src="{{ $img->image_url }}" alt="Generated" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='/images/placeholder.svg'">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <i class="fa-solid fa-expand text-white"></i>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Lightbox Modal - Glassmorphism -->
        <template x-teleport="body">
            <div 
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9999] flex flex-col bg-black/80 backdrop-blur-xl"
                x-cloak
            >
                <!-- Top Bar -->
                <div class="flex-shrink-0 h-16 flex items-center justify-between px-4 sm:px-6 bg-black/40 border-b border-white/10">
                    <div class="text-white/80 text-sm font-medium">
                        <span x-text="currentIndex + 1"></span> / <span x-text="images.length"></span>
                    </div>
                    <button 
                        @click="closeGallery()" 
                        class="w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors"
                    >
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Main Content -->
                <div class="flex-1 flex items-center justify-center relative overflow-hidden">
                    <!-- Nav Left -->
                    <button 
                        @click.stop="prev()"
                        class="absolute left-2 sm:left-6 z-10 w-12 h-12 rounded-full bg-black/50 backdrop-blur-sm hover:bg-black/70 text-white flex items-center justify-center transition-colors border border-white/10"
                        x-show="images.length > 1"
                    >
                        <i class="fa-solid fa-chevron-left text-lg"></i>
                    </button>

                    <!-- Image Container -->
                    <div class="w-full h-full flex items-center justify-center p-4 sm:p-8" @click="closeGallery()">
                        <img 
                            :src="images[currentIndex]" 
                            class="max-w-full max-h-full object-contain rounded-xl shadow-2xl"
                            onerror="this.src='/images/placeholder.svg'"
                            @click.stop
                        >
                    </div>

                    <!-- Nav Right -->
                    <button 
                        @click.stop="next()"
                        class="absolute right-2 sm:right-6 z-10 w-12 h-12 rounded-full bg-black/50 backdrop-blur-sm hover:bg-black/70 text-white flex items-center justify-center transition-colors border border-white/10"
                        x-show="images.length > 1"
                    >
                        <i class="fa-solid fa-chevron-right text-lg"></i>
                    </button>
                </div>

                <!-- Bottom Thumbnails -->
                <div class="flex-shrink-0 h-20 flex items-center justify-center bg-black/40 border-t border-white/10" x-show="images.length > 1">
                    <div class="flex gap-2 p-2 max-w-[90vw] overflow-x-auto" style="scrollbar-width: none;">
                        <template x-for="(img, idx) in images" :key="idx">
                            <button 
                                @click.stop="currentIndex = idx"
                                class="w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 border-2 transition-all hover:scale-105"
                                :class="currentIndex === idx ? 'border-cyan-400 ring-2 ring-cyan-400/50' : 'border-white/20 opacity-60 hover:opacity-100'"
                            >
                                <img :src="img" class="w-full h-full object-cover">
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endif
