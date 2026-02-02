<x-app-layout>
    <x-slot name="title">ZDream - Biến Ảnh Thường Thành Tác Phẩm AI</x-slot>

    <style>
        .home-hero {
            position: relative;
            overflow: hidden;
            border-radius: 0 0 2rem 2rem;
            isolation: isolate;
            background: radial-gradient(120% 140% at 0% 0%, rgba(216,180,254,0.2) 0%, rgba(10,10,15,0.92) 55%, rgba(10,10,15,1) 100%);
            box-shadow: 0 26px 60px rgba(0,0,0,0.45), inset 0 0 0 1px rgba(255,255,255,0.08);
        }
        .home-hero-grid {
            position: absolute;
            inset: -30%;
            background-image: url('/images/hero/home-grid.png');
            background-size: 900px auto;
            background-repeat: repeat;
            opacity: 0.12;
            filter: saturate(1) contrast(1.02);
            animation: home-grid-scroll 120s linear infinite;
            will-change: background-position;
            pointer-events: none;
        }
        .home-hero-overlay {
            position: absolute;
            inset: 0;
            background:
                radial-gradient(65% 60% at 20% 15%, rgba(244,114,182,0.18), transparent 60%),
                radial-gradient(60% 60% at 85% 20%, rgba(168,85,247,0.16), transparent 60%),
                linear-gradient(180deg, rgba(10,10,15,0.15), rgba(10,10,15,0.7));
            opacity: 0.55;
            pointer-events: none;
        }
        .home-hero-panel {
            background: linear-gradient(180deg, rgba(10,10,15,0.34), rgba(10,10,15,0.16));
            border: 1px solid rgba(255,255,255,0.12);
            box-shadow: 0 20px 40px rgba(0,0,0,0.35);
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
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.16);
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
            box-shadow: 0 30px 60px rgba(0,0,0,0.45);
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
            0%, 100% { transform: translate(var(--x, 0), var(--y, 0)) rotate(var(--r, 0deg)) scale(var(--s, 1)); }
            50% { transform: translate(calc(var(--x, 0) + 6px), calc(var(--y, 0) - 6px)) rotate(calc(var(--r, 0deg) + 1deg)) scale(var(--s, 1)); }
        }
        .home-hero-content {
            position: relative;
            z-index: 2;
        }
        @keyframes home-grid-scroll {
            from { background-position: 0 0; }
            to { background-position: 1200px 600px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .home-hero-grid { animation: none; }
        }
        body.is-scrolling .home-hero-grid {
            animation-play-state: paused;
        }
        @media (max-width: 768px) {
            .home-hero-grid {
                opacity: 0.08;
                animation-duration: 160s;
            }
        }
    </style>

    <!-- ========== HERO SECTION ========== -->
    <section class="home-hero">
        <div class="home-hero-grid"></div>
        <div class="home-hero-overlay"></div>

        <div class="home-hero-content max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-20">
            <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-8 lg:gap-12 items-center">
                <div class="lg:hidden text-center">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 mb-4">
                        <span class="inline-flex h-2 w-2 rounded-full bg-pink-400"></span>
                        <span class="text-xs font-medium text-white/80">Tạo ảnh cực nhanh</span>
                    </div>
                    <h1 class="text-3xl font-bold text-[#d3d6db] mb-3 leading-tight">
                        Chọn Style,<br>
                        <span class="bg-gradient-to-r from-pink-300 via-fuchsia-300 to-purple-300 bg-clip-text text-transparent">Ảnh Đẹp Tự Đến</span>
                    </h1>
                    <p class="text-white/70 text-sm mb-6">
                        Chỉ cần vài từ gợi ý. ZDream tự lo phần còn lại.
                    </p>
                    <div class="flex flex-col gap-3 items-center">
                        <a href="{{ route('styles.index') }}" class="w-full max-w-xs px-6 py-3.5 rounded-xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-purple-500 text-[#d3d6db] font-semibold shadow-lg shadow-fuchsia-500/35 hover:shadow-fuchsia-500/55 transition-all inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 16px;"></i>
                            <span>Bắt đầu ngay</span>
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="w-full max-w-xs px-6 py-3.5 rounded-xl bg-white/[0.06] border border-fuchsia-300/40 text-[#d3d6db] font-medium hover:bg-white/[0.1] transition-all inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-gift" style="font-size: 14px;"></i>
                                <span>Đăng ký miễn phí</span>
                            </a>
                        @endguest
                    </div>
                    <div class="mt-5 flex items-center justify-center gap-2 text-[11px] text-white/60">
                        <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">Nhanh</span>
                        <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">Dễ dùng</span>
                        <span class="px-2.5 py-1 rounded-full bg-white/10 border border-white/20">Style phong phú</span>
                    </div>
                </div>

                <div class="home-hero-panel hidden lg:block rounded-2xl sm:rounded-3xl px-5 sm:px-8 py-6 sm:py-8 text-center lg:text-left">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
                        <span class="text-[#d3d6db]">Chọn Style,</span><br>
                        <span class="bg-gradient-to-r from-pink-300 via-fuchsia-300 to-purple-300 bg-clip-text text-transparent">Ảnh Đẹp Tự Đến</span>
                    </h1>
                    <p class="text-white/70 text-base sm:text-lg lg:text-xl max-w-xl mx-auto lg:mx-0 mb-6 sm:mb-8">
                        Chỉ cần chọn phong cách và vài từ gợi ý. ZDream tự hoàn thiện phần còn lại.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start">
                        <a href="{{ route('styles.index') }}" class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-purple-500 text-[#d3d6db] font-semibold text-base sm:text-lg shadow-lg shadow-fuchsia-500/35 hover:shadow-fuchsia-500/55 transition-all inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                            <span>Bắt đầu ngay</span>
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl bg-white/[0.06] border border-fuchsia-300/40 text-[#d3d6db] font-medium text-base sm:text-lg hover:bg-white/[0.1] transition-all inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-gift" style="font-size: 14px;"></i>
                                <span>Đăng ký miễn phí</span>
                            </a>
                        @endguest
                    </div>
                </div>

                <div class="hidden lg:block">
                    @php
                        $heroStyles = $styles->take(3);
                    @endphp
                    @if($heroStyles->isNotEmpty())
                        <div class="hero-deck">
                            @foreach($heroStyles as $index => $style)
                                <div class="hero-card" style="--x: {{ $index * 18 }}px; --y: -{{ $index * 10 }}px; --r: {{ -4 + ($index * 5) }}deg; --s: {{ 0.98 + ($index * 0.02) }};">
                                    <div class="aspect-[3/4] overflow-hidden">
                                        <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover" loading="eager" decoding="async" fetchpriority="high">
                                    </div>
                                    <div class="p-4 border-t border-white/10 bg-black/30">
                                        <p class="text-[#d3d6db] font-semibold">{{ $style->name }}</p>
                                        <p class="text-white/60 text-sm mt-1">Phong cách nổi bật hôm nay</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- ========== STYLES GRID ========== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" id="styles">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 sm:mb-8">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500">
                    <i class="fa-solid fa-palette w-5 h-5 text-[#d3d6db]"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-[#d3d6db]">Styles Phổ Biến</h2>
                    <p class="text-white/50 text-sm">Chọn style yêu thích của bạn</p>
                </div>
            </div>
        </div>

        @if($styles->isEmpty())
            <div class="bg-[#1b1c21]  border border-[#2a2b30] rounded-2xl text-center py-16 sm:py-24">
                <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 text-lg mb-2">Chưa có Style nào</p>
                <p class="text-white/30 text-sm">Hãy quay lại sau nhé!</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                @foreach($styles as $index => $style)
                    <a href="{{ route('studio.show', $style->slug) }}" class="group block h-full">
                        <div class="style-card relative overflow-hidden h-full bg-[#1b1c21]  border border-[#2a2b30] rounded-2xl sm:rounded-3xl transition-all duration-500 hover:border-purple-500/30 hover:shadow-[0_20px_60px_rgba(168,85,247,0.15)] hover:-translate-y-2 cursor-pointer flex flex-col shine-effect">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-t-2xl sm:rounded-t-3xl">
                                <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" loading="lazy" decoding="async" fetchpriority="low">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#000000] via-transparent to-transparent opacity-80"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                                    @if($style->tag)
                                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full bg-gradient-to-r from-{{ $style->tag->color_from }} to-{{ $style->tag->color_to }} text-[#d3d6db] text-[9px] sm:text-xs font-bold shadow-lg">
                                            <i class="fa-solid {{ $style->tag->icon }} w-2 h-2 sm:w-2.5 sm:h-2.5"></i> {{ $style->tag->name }}
                                        </span>
                                    @else
                                        <div></div>
                                    @endif
                                    <div class="px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-full bg-black/60  border border-white/[0.15] shadow-lg">
                                        <span class="text-[#d3d6db] font-bold text-[9px] sm:text-xs flex items-center gap-0.5 sm:gap-1">
                                            <i class="fa-solid fa-star w-2 h-2 sm:w-3 sm:h-3 text-yellow-400"></i> {{ number_format($style->price, 0) }} Xu
                                        </span>
                                    </div>
                                </div>
                                <div class="hidden sm:flex absolute inset-0 items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        <div class="px-6 py-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold text-sm shadow-xl shadow-purple-500/30 flex items-center gap-2">
                                            Thử ngay <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col flex-1 p-2.5 sm:p-4">
                                <h3 class="font-bold text-[#d3d6db] text-xs sm:text-base lg:text-lg line-clamp-1 group-hover:text-purple-300 transition-colors duration-300">{{ $style->name }}</h3>
                                @if($style->description)
                                    <p class="hidden sm:block text-white/40 text-[10px] sm:text-sm mt-1 sm:mt-1.5 line-clamp-2 flex-1">{{ $style->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-[#2a2b30]">
                                    <div class="flex items-center gap-1 sm:gap-1.5 text-white/50 text-[10px] sm:text-xs">
                                        <i class="fa-solid fa-images w-2.5 h-2.5 sm:w-3 sm:h-3"></i>
                                        {{ number_format($style->generated_images_count) }} lượt tạo
                                    </div>
                                    <div class="flex items-center gap-1 text-purple-400 text-[10px] sm:text-xs font-medium">
                                        <i class="fa-solid fa-arrow-right w-2.5 h-2.5 sm:w-3 sm:h-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="flex justify-center mt-8">
                <a href="{{ route('styles.index') }}" class="px-6 sm:px-8 py-3 rounded-xl bg-gradient-to-r from-pink-500 via-fuchsia-500 to-purple-500 text-[#d3d6db] font-semibold text-sm sm:text-base shadow-lg shadow-fuchsia-500/35 hover:shadow-fuchsia-500/55 hover:from-pink-400 hover:to-purple-400 transition-all inline-flex items-center gap-2">
                    <span>Xem tất cả Styles</span>
                    <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
                </a>
            </div>
        @endif
    </section>

    <!-- ========== HOW IT WORKS ========== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#d3d6db] mb-2">Cách sử dụng</h2>
            <p class="text-white/50 text-sm sm:text-base">3 bước đơn giản</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-[#1b1c21] border border-[#2a2b30] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-palette w-5 h-5 sm:w-6 sm:h-6 text-[#d3d6db]"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-[#d3d6db] text-base sm:text-lg">Chọn Style</h3>
                    <p class="text-white/50 text-sm">Chọn style yêu thích</p>
                </div>
                <div class="sm:hidden w-8 h-8 rounded-full bg-white/[0.05] flex items-center justify-center text-white/40 text-sm font-mono">1</div>
            </div>
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-[#1b1c21] border border-[#2a2b30] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center">
                    <i class="fa-solid fa-sliders w-5 h-5 sm:w-6 sm:h-6 text-[#d3d6db]"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-[#d3d6db] text-base sm:text-lg">Chọn Options</h3>
                    <p class="text-white/50 text-sm">Tùy chỉnh theo ý thích</p>
                </div>
                <div class="sm:hidden w-8 h-8 rounded-full bg-white/[0.05] flex items-center justify-center text-white/40 text-sm font-mono">2</div>
            </div>
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-[#1b1c21] border border-[#2a2b30] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center">
                    <i class="fa-solid fa-wand-magic-sparkles w-5 h-5 sm:w-6 sm:h-6 text-[#d3d6db]"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-[#d3d6db] text-base sm:text-lg">Nhận Kết Quả</h3>
                    <p class="text-white/50 text-sm">Nhận ảnh trong 10s</p>
                </div>
                <div class="sm:hidden w-8 h-8 rounded-full bg-white/[0.05] flex items-center justify-center text-white/40 text-sm font-mono">3</div>
            </div>
        </div>
    </section>

    <!-- ========== CTA ========== -->
    @guest
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 sm:pb-16">
            <div class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-r from-purple-900/60 to-pink-900/60 border border-white/[0.1] p-6 sm:p-10 lg:p-14 text-center">
                <div class="absolute -top-20 -left-20 w-40 sm:w-60 h-40 sm:h-60 bg-purple-500/30 rounded-full blur-[80px]"></div>
                <div class="absolute -bottom-20 -right-20 w-40 sm:w-60 h-40 sm:h-60 bg-pink-500/30 rounded-full blur-[80px]"></div>
                <div class="relative">
                    <i class="fa-solid fa-gift text-4xl sm:text-5xl text-purple-300 mb-4 sm:mb-6"></i>
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-[#d3d6db] mb-3 sm:mb-4">Nhận {{ (int) App\Models\Setting::get('default_credits', 10) }} Xu Miễn Phí!</h2>
                    <p class="text-white/70 mb-6 sm:mb-8 max-w-lg mx-auto text-sm sm:text-lg">Đăng ký ngay để nhận {{ (int) App\Models\Setting::get('default_credits', 10) }} Xu trải nghiệm</p>
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 sm:px-10 py-3.5 sm:py-4 rounded-xl bg-white text-gray-900 font-semibold text-base sm:text-lg hover:bg-gray-100 transition-colors inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-crown" style="font-size: 18px;"></i>
                        <span>Đăng Ký Miễn Phí</span>
                    </a>
                </div>
            </div>
        </section>
    @endguest
</x-app-layout>
