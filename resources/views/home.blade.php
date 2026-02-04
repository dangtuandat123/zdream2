<x-app-layout>
    <x-slot name="title">ZDream - Biến Ảnh Thường Thành Tác Phẩm AI</x-slot>

    <style>
        .home-hero {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            background: radial-gradient(120% 140% at 0% 0%, rgba(216, 180, 254, 0.2) 0%, rgba(10, 10, 15, 0.92) 55%, rgba(10, 10, 15, 1) 100%);
            box-shadow: 0 26px 60px rgba(0, 0, 0, 0.45), inset 0 0 0 1px rgba(255, 255, 255, 0.08);
            -webkit-mask-image: linear-gradient(to bottom, black 0%, black 70%, transparent 100%);
            mask-image: linear-gradient(to bottom, black 0%, black 70%, transparent 100%);
        }

        .home-hero-grid {
            position: absolute;
            inset: -30%;
            background-image: url('/images/hero/home-grid.png');
            background-size: 900px auto;
            background-repeat: repeat;
            opacity: 0.45;
            filter: saturate(1) contrast(1.02);
            animation: home-grid-scroll 120s linear infinite;
            will-change: background-position;
            pointer-events: none;
        }

        .home-hero-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(65% 60% at 20% 15%, rgba(244, 114, 182, 0.18), transparent 60%),
                radial-gradient(60% 60% at 85% 20%, rgba(168, 85, 247, 0.16), transparent 60%),
                linear-gradient(180deg, rgba(10, 10, 15, 0.55) 0%, rgba(10, 10, 15, 0.3) 50%, transparent 100%);
            pointer-events: none;
        }

        .home-hero-panel {
            background: linear-gradient(180deg, rgba(10, 10, 15, 0.34), rgba(10, 10, 15, 0.16));
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(12px);
        }

        .hero-deck {
            position: relative;
            width: min(360px, 100%);
            height: 420px;
            margin-left: auto;
        }

        .hero-card {
            position: absolute;
            inset: 0;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0.02));
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.45);
            overflow: hidden;
            transform-origin: bottom right;
            animation: hero-card-float 8s ease-in-out infinite;
        }

        .hero-card:nth-child(1) {
            transform: translate(0, 0) rotate(-4deg) scale(0.98);
            animation-delay: 0s;
        }

        .hero-card:nth-child(2) {
            transform: translate(18px, -12px) rotate(1deg) scale(1);
            animation-delay: 1.2s;
        }

        .hero-card:nth-child(3) {
            transform: translate(38px, -28px) rotate(6deg) scale(1.02);
            animation-delay: 2.1s;
        }

        .hero-card:hover {
            transform: translate(38px, -32px) rotate(6deg) scale(1.04);
        }

        @keyframes hero-card-float {

            0%,
            100% {
                transform: translate(var(--x, 0), var(--y, 0)) rotate(var(--r, 0deg)) scale(var(--s, 1));
            }

            50% {
                transform: translate(calc(var(--x, 0) + 6px), calc(var(--y, 0) - 6px)) rotate(calc(var(--r, 0deg) + 1deg)) scale(var(--s, 1));
            }
        }

        .home-hero-content {
            position: relative;
            z-index: 2;
        }

        @keyframes home-grid-scroll {
            from {
                background-position: 0 0;
            }

            to {
                background-position: 1200px 600px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .home-hero-grid {
                animation: none;
            }
        }

        body.is-scrolling .home-hero-grid {
            animation-play-state: paused;
        }

        @media (max-width: 768px) {
            .home-hero-grid {
                animation-duration: 160s;
            }

            .home-hero-overlay {
                background:
                    radial-gradient(65% 60% at 20% 15%, rgba(244, 114, 182, 0.18), transparent 60%),
                    radial-gradient(60% 60% at 85% 20%, rgba(168, 85, 247, 0.16), transparent 60%),
                    linear-gradient(180deg, rgba(10, 10, 15, 0.55) 0%, rgba(10, 10, 15, 0.3) 50%, transparent 100%);
            }

            .home-hero-panel {
                background: linear-gradient(180deg, rgba(10, 10, 15, 0.15), rgba(10, 10, 15, 0.08));
                border: 1px solid rgba(255, 255, 255, 0.08);
                backdrop-filter: blur(8px);
            }
        }
    </style>

    <!-- ========== HERO SECTION ========== -->
    <section class="home-hero">
        <div class="home-hero-grid"></div>
        <div class="home-hero-overlay"></div>

        <!-- Hero Content -->
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 py-20 sm:py-28 lg:py-36 text-center">
            <!-- Title - ZDream Logo -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black mb-6 sm:mb-10 flex items-center justify-center gap-1 sm:gap-2"
                style="font-family: 'Inter', sans-serif; letter-spacing: -0.03em;">
                <i class="fa-solid fa-wand-magic-sparkles text-4xl sm:text-6xl lg:text-7xl text-purple-400"></i>
                <span
                    class="inline-block px-2 py-1 bg-gradient-to-r from-white via-pink-300 to-white bg-clip-text text-transparent animate-gradient bg-[length:200%_auto] italic">
                    ZDream
                </span>
            </h1>

            <!-- Prompt Input Bar - Enhanced -->
            <form action="{{ route('styles.index') }}" method="GET"
                class="w-full max-w-2xl mx-auto mb-6 sm:mb-8 group/form">
                <div class="relative">
                    <!-- Glow effect -->
                    <div
                        class="absolute -inset-0.5 sm:-inset-1 bg-gradient-to-r from-purple-600 via-pink-500 to-purple-600 rounded-2xl opacity-20 blur-md sm:blur-lg group-hover/form:opacity-35 transition-opacity duration-500">
                    </div>

                    <!-- Input container -->
                    <div
                        class="relative flex flex-col gap-3 p-3 sm:p-4 rounded-2xl bg-black/50 backdrop-blur-2xl border border-white/15 shadow-2xl">
                        <!-- Textarea - Fixed height with scroll -->
                        <textarea name="prompt" rows="3" placeholder="Mô tả ý tưởng của bạn..."
                            class="w-full h-20 bg-transparent border-none outline-none ring-0 focus:ring-0 focus:outline-none text-white placeholder-white/40 text-sm sm:text-base resize-none focus:placeholder-white/60 transition-all overflow-y-auto"></textarea>

                        <!-- Bottom row: icons + button -->
                        <div class="flex items-center justify-between gap-3">
                            <!-- Left icons -->
                            <div class="flex items-center gap-2">
                                <button type="button"
                                    class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer">
                                    <i class="fa-solid fa-image text-white/50 text-sm"></i>
                                </button>
                                <button type="button"
                                    class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 border border-white/10 transition-all cursor-pointer">
                                    <i class="fa-solid fa-palette text-white/50 text-sm"></i>
                                </button>
                            </div>

                            <!-- Generate Button -->
                            <button type="submit"
                                class="flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-pink-500 text-white font-semibold text-sm hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/30 active:scale-[0.98] transition-all duration-200">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                                <span>Tạo ảnh</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tool Icons - Grid on mobile, inline on desktop -->
            <div
                class="grid grid-cols-4 gap-2 sm:inline-flex sm:items-center sm:gap-1 p-1.5 sm:p-2 rounded-2xl sm:rounded-full bg-black/30 backdrop-blur-xl border border-white/10 max-w-xs sm:max-w-none mx-auto">
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-image text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Image</span>
                </a>
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-palette text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Styles</span>
                </a>
                <a href="{{ route('styles.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all relative">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">AI Art</span>
                    <span
                        class="absolute -top-0.5 -right-0.5 sm:-top-1 sm:-right-1 w-2 h-2 sm:w-auto sm:h-auto sm:px-1 sm:py-0.5 rounded-full text-[8px] font-bold bg-gradient-to-r from-pink-500 to-purple-500 text-white sm:text-[9px]">
                        <span class="hidden sm:inline">NEW</span>
                    </span> </a>
                <a href="{{ route('history.index') }}"
                    class="flex flex-col sm:flex-row items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 sm:py-2 rounded-xl sm:rounded-full text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <i class="fa-solid fa-images text-lg sm:text-base"></i>
                    <span class="text-[10px] sm:text-sm font-medium">Gallery</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ========== STYLES CAROUSEL ========== -->
    <section class="py-8 sm:py-12" id="styles" x-data="{
        currentIndex: 0,
        itemCount: {{ $styles->count() }},
        autoScroll: null,
        paused: false,
        scrollContainer: null,

        init() {
            this.scrollContainer = this.$refs.carousel;
            this.startAutoScroll();
        },

        startAutoScroll() {
            this.autoScroll = setInterval(() => {
                if (!this.paused) {
                    this.next();
                }
            }, 2000);
        },

        stopAutoScroll() {
            if (this.autoScroll) {
                clearInterval(this.autoScroll);
                this.autoScroll = null;
            }
        },

        pauseAutoScroll() {
            this.paused = true;
            // Resume after 5 seconds of no interaction
            setTimeout(() => {
                this.paused = false;
            }, 5000);
        },

        next() {
            if (!this.scrollContainer) return;
            // Calculate true stride (width + gap)
            const style = window.getComputedStyle(this.scrollContainer);
            const gap = parseFloat(style.gap) || 12; // Fallback to 12 if dynamic fail
            const cardWidth = this.scrollContainer.querySelector('.style-card-wrapper')?.offsetWidth || 180;
            const stride = cardWidth + gap;

            const maxScroll = this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth;
            
            // If near end, loop back to start
            if (this.scrollContainer.scrollLeft >= maxScroll - 10) {
                this.scrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
            } else {
                this.scrollContainer.scrollBy({ left: stride, behavior: 'smooth' });
            }
        },

        prev() {
            if (!this.scrollContainer) return;
            // Calculate true stride
            const style = window.getComputedStyle(this.scrollContainer);
            const gap = parseFloat(style.gap) || 12;
            const cardWidth = this.scrollContainer.querySelector('.style-card-wrapper')?.offsetWidth || 180;
            const stride = cardWidth + gap;
            
            // If at start, loop to end
            if (this.scrollContainer.scrollLeft <= 10) {
                const maxScroll = this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth;
                this.scrollContainer.scrollTo({ left: maxScroll, behavior: 'smooth' });
            } else {
                this.scrollContainer.scrollBy({ left: -stride, behavior: 'smooth' });
            }
        }
    }">
        <div class="px-2 sm:px-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-[#d3d6db]">Chọn Style yêu thích</h2>
                    <p class="text-white/40 text-xs sm:text-sm mt-1">Biến ảnh thường thành tác phẩm nghệ thuật</p>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Nav Buttons -->
                    <button @click="prev(); pauseAutoScroll();"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 text-white/60 hover:text-white transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-left text-sm"></i>
                    </button>
                    <button @click="next(); pauseAutoScroll();"
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-purple-500/50 text-white/60 hover:text-white transition-all flex items-center justify-center">
                        <i class="fa-solid fa-chevron-right text-sm"></i>
                    </button>
                    <!-- View All -->
                    <a href="{{ route('styles.index') }}"
                        class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-medium hover:from-purple-500 hover:to-pink-500 transition-all">
                        <span>Xem tất cả</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        @if($styles->isEmpty())
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-2xl text-center py-16">
                    <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                    <p class="text-white/50 text-lg mb-2">Chưa có Style nào</p>
                    <p class="text-white/30 text-sm">Hãy quay lại sau nhé!</p>
                </div>
            </div>
        @else
            <!-- Carousel Container with Edge Fades -->
            <div class="relative" @mouseenter="pauseAutoScroll()" @mouseleave="paused = false">
                <!-- Left Fade Gradient -->
                <div
                    class="absolute left-0 top-0 bottom-0 w-6 sm:w-10 lg:w-12 bg-gradient-to-r from-[#0a0a0f] to-transparent z-10 pointer-events-none">
                </div>
                <!-- Right Fade Gradient -->
                <div
                    class="absolute right-0 top-0 bottom-0 w-6 sm:w-10 lg:w-12 bg-gradient-to-l from-[#0a0a0f] to-transparent z-10 pointer-events-none">
                </div>

                <div x-ref="carousel"
                    class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth py-4 px-2 sm:px-4 no-scrollbar snap-x snap-mandatory"
                    style="-webkit-overflow-scrolling: touch;">
                    @foreach($styles as $style)
                        <a href="{{ route('studio.show', $style->slug) }}"
                            class="style-card-wrapper flex-shrink-0 w-52 sm:w-60 lg:w-64 group relative z-0 hover:z-20 snap-start">
                            <div
                                class="bg-[#1b1c21] border border-[#2a2b30] rounded-xl overflow-hidden transition-all duration-300 hover:border-purple-500/40 hover:shadow-xl hover:shadow-purple-500/20 hover:scale-105">
                                <!-- Image -->
                                <div class="relative aspect-[3/4] overflow-hidden">
                                    <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}"
                                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                        loading="lazy" decoding="async">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent">
                                    </div>

                                    <!-- Tag -->
                                    @if($style->tag)
                                        <span
                                            class="absolute top-2 left-2 inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full bg-gradient-to-r from-{{ $style->tag->color_from }} to-{{ $style->tag->color_to }} text-white text-[8px] sm:text-[9px] font-bold shadow">
                                            <i class="fa-solid {{ $style->tag->icon }}"></i> {{ $style->tag->name }}
                                        </span>
                                    @endif

                                    <!-- Price -->
                                    <div
                                        class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full bg-black/60 border border-white/10 text-white text-[9px] sm:text-[10px] font-bold flex items-center gap-0.5">
                                        <i class="fa-solid fa-star text-yellow-400"></i> {{ number_format($style->price, 0) }}
                                    </div>

                                    <!-- Hover CTA -->
                                    <div
                                        class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/40">
                                        <span
                                            class="px-3 py-1.5 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-semibold shadow-lg">
                                            Thử ngay
                                        </span>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="p-2 sm:p-2.5">
                                    <h3
                                        class="font-semibold text-white text-xs sm:text-sm line-clamp-1 group-hover:text-purple-300 transition-colors">
                                        {{ $style->name }}
                                    </h3>
                                    <div class="flex items-center gap-1 text-white/40 text-[9px] sm:text-[10px] mt-1">
                                        <i class="fa-solid fa-images"></i>
                                        {{ number_format($style->generated_images_count) }} lượt
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Mobile View All Button -->
        <div class="flex justify-center mt-4 sm:hidden px-4">
            <a href="{{ route('styles.index') }}"
                class="w-full py-3 rounded-xl bg-gradient-to-r from-purple-600 to-pink-600 text-white text-sm font-semibold text-center">
                Xem tất cả Styles
            </a>
        </div>

        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    </section>

    <!-- ========== INSPIRATIONS GALLERY ========== -->
    <livewire:inspirations-gallery />


</x-app-layout>