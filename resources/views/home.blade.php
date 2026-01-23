<x-app-layout>
    <x-slot name="title">Gallery - EZShot AI</x-slot>

    {{-- Hero Section --}}
    <section class="py-10 md:py-16 lg:py-24">
        <div class="container mx-auto px-4 text-center">
            {{-- Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-1.5 mb-6 rounded-full bg-primary-500/10 border border-primary-500/20 animate-fade-up">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-500"></span>
                </span>
                <span class="text-sm font-medium text-primary-300">AI Image Generator</span>
            </div>

            {{-- Headline --}}
            <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold mb-4 md:mb-6 animate-fade-up" style="animation-delay: 0.1s;">
                <span class="gradient-text">Tạo Ảnh AI</span>
                <br class="md:hidden">
                <span class="text-white/90"> Không Cần Prompt</span>
            </h1>
            
            <p class="text-base md:text-lg lg:text-xl text-white/50 max-w-xl mx-auto mb-8 leading-relaxed animate-fade-up" style="animation-delay: 0.2s;">
                Chọn Style → Bấm nút → Nhận ảnh nghệ thuật AI.
                <br>
                <span class="text-accent-cyan font-semibold">Zero-Prompt</span> dành cho GenZ! ✨
            </p>

            {{-- CTA Buttons --}}
            @guest
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 animate-fade-up" style="animation-delay: 0.3s;">
                    <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto text-base px-8 py-3.5 animate-glow-pulse">
                        🚀 Bắt đầu miễn phí
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary w-full sm:w-auto text-base px-8 py-3.5">
                        Đăng nhập
                    </a>
                </div>
            @endguest
        </div>
    </section>

    {{-- Styles Gallery --}}
    <section class="pb-16 md:pb-24">
        <div class="container mx-auto px-4">
            <h2 class="section-title mb-6 md:mb-8">
                Chọn Style
            </h2>

            @if($styles->isEmpty())
                <div class="glass-card text-center py-16 md:py-24">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-gradient-to-br from-white/5 to-white/[0.02] flex items-center justify-center">
                        <svg class="w-10 h-10 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-white/40 text-lg mb-2">Chưa có Style nào</p>
                    <p class="text-white/30 text-sm">Hãy quay lại sau nhé!</p>
                </div>
            @else
                {{-- Grid: 2 cols mobile, 3 cols tablet, 4 cols desktop --}}
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4 lg:gap-6 stagger-children">
                    @foreach($styles as $style)
                        <x-style-card :style="$style" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
