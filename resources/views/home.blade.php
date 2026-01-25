<x-app-layout>
    <x-slot name="title">ZDream - Biến Ảnh Thường Thành Tác Phẩm AI</x-slot>

    <!-- ========== HERO SECTION ========== -->
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-0 left-1/4 w-64 sm:w-96 h-64 sm:h-96 bg-purple-600/20 rounded-full blur-[100px] sm:blur-[150px]"></div>
            <div class="absolute bottom-0 right-0 w-48 sm:w-80 h-48 sm:h-80 bg-pink-600/15 rounded-full blur-[80px] sm:blur-[130px]"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 lg:py-20">
            <div class="text-center lg:text-left lg:grid lg:grid-cols-2 lg:gap-12 lg:items-center">
                <div>
                    <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full bg-gradient-to-r from-purple-500/20 to-pink-500/20 border border-purple-500/30 mb-4 sm:mb-6">
                        <i class="fa-solid fa-star w-3 h-3 sm:w-4 sm:h-4 text-yellow-400"></i>
                        <span class="text-xs sm:text-sm font-medium text-white/80">AI tiên tiến nhất</span>
                    </div>
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold mb-4 sm:mb-6 leading-tight">
                        <span class="text-white">Biến Ảnh Thường</span><br>
                        <span class="bg-gradient-to-r from-purple-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">Thành Tác Phẩm</span>
                    </h1>
                    <p class="text-white/60 text-base sm:text-lg lg:text-xl max-w-lg mx-auto lg:mx-0 mb-6 sm:mb-8">
                        Chọn style → Bấm nút → Nhận kết quả.
                        <span class="hidden sm:inline"> Chỉ 3 bước, không cần prompt!</span>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center lg:justify-start mb-8 sm:mb-0">
                        <a href="#styles" class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base sm:text-lg shadow-lg shadow-purple-500/30 hover:shadow-purple-500/50 transition-all inline-flex items-center justify-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles" style="font-size: 18px;"></i>
                            <span>Bắt đầu ngay</span>
                        </a>
                        @guest
                            <a href="{{ route('register') }}" class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white font-medium text-base sm:text-lg hover:bg-white/[0.1] transition-all inline-flex items-center justify-center gap-2">
                                <i class="fa-solid fa-gift" style="font-size: 14px;"></i>
                                <span>Đăng ký ngay</span>
                            </a>
                        @endguest
                    </div>
                    <div class="flex items-center justify-center lg:justify-start gap-6 sm:gap-8 mt-8 pt-6 sm:pt-8 border-t border-white/[0.05]">
                        <div class="text-center">
                            <p class="text-2xl sm:text-3xl font-bold text-white">{{ \App\Models\Style::active()->count() }}+</p>
                            <p class="text-white/50 text-xs sm:text-sm">Styles</p>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-white/10"></div>
                        <div class="text-center">
                            <p class="text-2xl sm:text-3xl font-bold text-white">10s</p>
                            <p class="text-white/50 text-xs sm:text-sm">Xử lý</p>
                        </div>
                        <div class="w-px h-8 sm:h-10 bg-white/10"></div>
                        <div class="text-center">
                            <p class="text-2xl sm:text-3xl font-bold text-white">2K</p>
                            <p class="text-white/50 text-xs sm:text-sm">Từ</p>
                        </div>
                    </div>
                </div>
                <!-- Preview Images - Desktop only -->
                <div class="hidden lg:block relative mt-8 lg:mt-0">
                    <div class="relative w-full aspect-square max-w-md mx-auto">
                        @if($styles->count() > 0)
                            <div class="absolute top-0 right-0 w-64 xl:w-72 h-80 xl:h-96 rounded-2xl overflow-hidden shadow-2xl shadow-purple-500/20 border border-white/[0.1] transform rotate-3 hover:rotate-0 transition-transform duration-500">
                                <img src="{{ $styles->first()->thumbnail }}" alt="AI Generated" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-4 left-4 right-4">
                                    <p class="text-white font-semibold">{{ $styles->first()->name }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-16 h-16 xl:w-20 xl:h-20 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shadow-lg shadow-purple-500/50">
                            <i class="fa-solid fa-wand-magic-sparkles w-6 h-6 xl:w-8 xl:h-8 text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========== STYLES GRID ========== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12" id="styles">
        <div class="flex items-center justify-between mb-6 sm:mb-8">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-br from-purple-500 to-pink-500">
                    <i class="fa-solid fa-palette w-5 h-5 text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white">Styles Phổ Biến</h2>
                    <p class="text-white/50 text-sm">Chọn style yêu thích của bạn</p>
                </div>
            </div>
            <a href="{{ route('styles.index') }}" class="px-4 py-2 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/70 text-sm font-medium hover:bg-white/[0.1] hover:text-white transition-all inline-flex items-center gap-2">
                <span>Xem tất cả</span>
                <i class="fa-solid fa-arrow-right w-3 h-3"></i>
            </a>
        </div>

        @if($styles->isEmpty())
            <div class="bg-white/[0.03] backdrop-blur-xl border border-white/[0.08] rounded-2xl text-center py-16 sm:py-24">
                <i class="fa-solid fa-palette text-4xl text-white/20 mb-4"></i>
                <p class="text-white/50 text-lg mb-2">Chưa có Style nào</p>
                <p class="text-white/30 text-sm">Hãy quay lại sau nhé!</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                @foreach($styles as $index => $style)
                    <a href="{{ route('studio.show', $style->slug) }}" class="group block h-full">
                        <div class="style-card relative overflow-hidden h-full bg-gradient-to-b from-white/[0.05] to-white/[0.02] backdrop-blur-[8px] border border-white/[0.08] rounded-2xl sm:rounded-3xl transition-all duration-500 hover:border-purple-500/30 hover:shadow-[0_20px_60px_rgba(168,85,247,0.15)] hover:-translate-y-2 cursor-pointer flex flex-col shine-effect">
                            <div class="relative aspect-[3/4] overflow-hidden rounded-t-2xl sm:rounded-t-3xl">
                                <img src="{{ $style->thumbnail }}" alt="{{ $style->name }}" class="w-full h-full object-cover transition-all duration-700 group-hover:scale-110" loading="lazy">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a0f] via-transparent to-transparent opacity-80"></div>
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                <div class="absolute top-2 sm:top-3 left-2 sm:left-3 right-2 sm:right-3 flex items-start justify-between">
                                    @if($index < 3)
                                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full bg-gradient-to-r from-orange-500 to-red-500 text-white text-[9px] sm:text-xs font-bold shadow-lg">
                                            <i class="fa-solid fa-fire w-2 h-2 sm:w-2.5 sm:h-2.5"></i> HOT
                                        </span>
                                    @elseif($index < 5)
                                        <span class="inline-flex items-center gap-1 px-1.5 sm:px-2 py-0.5 sm:py-1 rounded-full bg-gradient-to-r from-cyan-500 to-blue-500 text-white text-[9px] sm:text-xs font-bold shadow-lg">
                                            <i class="fa-solid fa-bolt w-2 h-2 sm:w-2.5 sm:h-2.5"></i> MỚI
                                        </span>
                                    @else
                                        <div></div>
                                    @endif
                                    <div class="px-2 sm:px-3 py-0.5 sm:py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/[0.15] shadow-lg">
                                        <span class="text-white font-bold text-[9px] sm:text-xs flex items-center gap-0.5 sm:gap-1">
                                            <i class="fa-solid fa-star w-2 h-2 sm:w-3 sm:h-3 text-yellow-400"></i> {{ number_format($style->price, 0) }} Xu
                                        </span>
                                    </div>
                                </div>
                                <div class="hidden sm:flex absolute inset-0 items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
                                    <div class="transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                                        <div class="px-6 py-3 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-sm shadow-xl shadow-purple-500/30 flex items-center gap-2">
                                            Thử ngay <i class="fa-solid fa-arrow-right w-3.5 h-3.5"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col flex-1 p-2.5 sm:p-4">
                                <h3 class="font-bold text-white text-xs sm:text-base lg:text-lg line-clamp-1 group-hover:text-purple-300 transition-colors duration-300">{{ $style->name }}</h3>
                                @if($style->description)
                                    <p class="hidden sm:block text-white/40 text-[10px] sm:text-sm mt-1 sm:mt-1.5 line-clamp-2 flex-1">{{ $style->description }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-2 sm:mt-3 pt-2 sm:pt-3 border-t border-white/[0.05]">
                                    <div class="flex items-center gap-1 sm:gap-1.5 text-white/50 text-[10px] sm:text-xs">
                                        <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-green-500 animate-pulse"></span> Sẵn sàng
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
        @endif
    </section>

    <!-- ========== HOW IT WORKS ========== -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-white mb-2">Cách sử dụng</h2>
            <p class="text-white/50 text-sm sm:text-base">3 bước đơn giản</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-white/[0.02] border border-white/[0.06] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center">
                    <i class="fa-solid fa-palette w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-white text-base sm:text-lg">Chọn Style</h3>
                    <p class="text-white/50 text-sm">Chọn style yêu thích</p>
                </div>
                <div class="sm:hidden w-8 h-8 rounded-full bg-white/[0.05] flex items-center justify-center text-white/40 text-sm font-mono">1</div>
            </div>
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-white/[0.02] border border-white/[0.06] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-pink-500 to-pink-600 flex items-center justify-center">
                    <i class="fa-solid fa-sliders w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-white text-base sm:text-lg">Chọn Options</h3>
                    <p class="text-white/50 text-sm">Tùy chỉnh theo ý thích</p>
                </div>
                <div class="sm:hidden w-8 h-8 rounded-full bg-white/[0.05] flex items-center justify-center text-white/40 text-sm font-mono">2</div>
            </div>
            <div class="flex sm:flex-col items-center sm:text-center gap-4 sm:gap-0 bg-white/[0.02] border border-white/[0.06] rounded-xl sm:rounded-2xl p-4 sm:p-6">
                <div class="flex-shrink-0 w-12 h-12 sm:w-14 sm:h-14 sm:mb-4 rounded-xl sm:rounded-2xl bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center">
                    <i class="fa-solid fa-wand-magic-sparkles w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
                </div>
                <div class="flex-1 sm:flex-none">
                    <h3 class="font-semibold text-white text-base sm:text-lg">Nhận Kết Quả</h3>
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
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-3 sm:mb-4">Nhận {{ (int) App\Models\Setting::get('default_credits', 10) }} Xu Miễn Phí!</h2>
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
