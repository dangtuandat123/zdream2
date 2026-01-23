<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? 'ZDream - Biến Ảnh Thường Thành Tác Phẩm AI' }}</title>
    <meta name="description" content="Chọn style → Upload ảnh → Nhận kết quả. Chỉ 3 bước, không cần prompt!">

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen text-white antialiased">

    <!-- ========== HEADER ========== -->
    <header id="header" class="fixed top-0 left-0 right-0 z-50 bg-[#0a0a0f]/80 backdrop-blur-[12px] border-b border-white/[0.03] transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-14 sm:h-16">
                <!-- Left: Logo + Nav -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0">
                        <i class="fa-solid fa-wand-magic-sparkles w-5 h-5 text-purple-400 transition-transform duration-300 group-hover:rotate-12"></i>
                        <span class="text-lg sm:text-xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">ZDream</span>
                    </a>
                    <nav class="hidden md:flex items-center gap-1">
                        <a href="{{ route('home') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-white/60 hover:text-white hover:bg-white/[0.05] transition-all inline-flex items-center gap-2">
                            <i class="fa-solid fa-house w-3.5 h-3.5"></i> Trang chủ
                        </a>
                        <a href="{{ route('home') }}#styles" class="px-3 py-2 rounded-lg text-sm font-medium text-white/60 hover:text-white hover:bg-white/[0.05] transition-all inline-flex items-center gap-2">
                            <i class="fa-solid fa-palette w-3.5 h-3.5"></i> Styles
                        </a>
                        @auth
                            <a href="#" class="px-3 py-2 rounded-lg text-sm font-medium text-white/60 hover:text-white hover:bg-white/[0.05] transition-all inline-flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left w-3.5 h-3.5"></i> Lịch sử
                            </a>
                        @endauth
                    </nav>
                </div>
                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    @auth
                        <div class="hidden sm:flex items-center bg-white/[0.03] rounded-full border border-white/[0.08] p-1">
                            <a href="{{ route('wallet.index') }}" class="px-3 h-8 rounded-full flex items-center gap-2 text-white/80 hover:bg-white/[0.05] transition-all">
                                <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                                <span class="font-semibold text-sm text-white/95">{{ number_format(auth()->user()->credits, 0) }}</span>
                            </a>
                            <a href="{{ route('wallet.index') }}" class="h-8 px-4 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium text-sm flex items-center gap-1.5 hover:from-purple-400 hover:to-pink-400 transition-all">
                                <i class="fa-solid fa-plus w-3 h-3"></i> Nạp Xu
                            </a>
                        </div>
                        <a href="{{ route('wallet.index') }}" class="sm:hidden h-9 px-3 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center gap-2 text-white/80">
                            <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                            <span class="font-semibold text-sm text-white/95">{{ number_format(auth()->user()->credits, 0) }}</span>
                        </a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:block h-9 px-4 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-sm hover:bg-cyan-500/20 transition-all">Admin</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:block h-9 px-4 rounded-full bg-white/[0.03] border border-white/[0.1] text-white/80 text-sm hover:bg-white/[0.06] transition-all">Đăng nhập</a>
                    @endauth
                    <button id="menu-btn" class="md:hidden w-10 h-10 rounded-xl bg-white/[0.03] border border-white/[0.08] flex items-center justify-center text-white/80 hover:text-white hover:bg-white/[0.06] transition-all">
                        <i class="fa-solid fa-bars w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- ========== MOBILE MENU OVERLAY ========== -->
    <div id="menu-overlay" class="fixed inset-0 z-40 md:hidden opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div id="mobile-menu" class="mobile-menu closed absolute right-0 top-0 h-full w-72 max-w-[85vw] bg-[#0a0a0f]/98 backdrop-blur-[24px] border-l border-white/[0.08]">
            <div class="p-4 border-b border-white/[0.05] flex items-center justify-between">
                <span class="text-white/80 font-medium">Menu</span>
                <button id="close-menu-btn" class="w-8 h-8 rounded-lg bg-white/[0.05] flex items-center justify-center text-white/60 hover:text-white">
                    <i class="fa-solid fa-xmark w-4 h-4"></i>
                </button>
            </div>
            <div class="p-4 space-y-2">
                @auth
                    <a href="{{ route('wallet.index') }}" class="block p-4 rounded-xl bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-white/60 text-sm">Số dư</span>
                            <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                        </div>
                        <div class="text-2xl font-bold text-white mb-3">{{ number_format(auth()->user()->credits, 0) }} Xu</div>
                        <div class="w-full py-2.5 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium text-sm flex items-center justify-center gap-2 shadow-lg shadow-purple-500/25">
                            <i class="fa-solid fa-plus w-3.5 h-3.5"></i> Nạp thêm Xu
                        </div>
                    </a>
                @endauth
                <div class="h-px bg-white/[0.05] my-4"></div>
                <a href="{{ route('home') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-house w-4 h-4 text-purple-400"></i> Trang chủ</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a>
                <a href="{{ route('home') }}#styles" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-palette w-4 h-4 text-purple-400"></i> Styles</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a>
                @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-user w-4 h-4 text-purple-400"></i> Hồ sơ</span>
                        <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                    </a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/15 transition-all">
                            <span class="flex items-center gap-3"><i class="fa-solid fa-crown w-4 h-4"></i> Admin Panel</span>
                            <i class="fa-solid fa-chevron-right w-3 h-3 text-cyan-400/50"></i>
                        </a>
                    @endif
                    <div class="h-px bg-white/[0.05] my-4"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 font-medium flex items-center justify-center gap-2 hover:bg-red-500/15 transition-colors">
                            <i class="fa-solid fa-right-from-bracket w-4 h-4"></i> Đăng xuất
                        </button>
                    </form>
                @else
                    <div class="h-px bg-white/[0.05] my-4"></div>
                    <a href="{{ route('register') }}" class="block w-full py-3 rounded-xl bg-white text-gray-900 font-medium text-center hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-crown w-4 h-4 mr-2"></i> Đăng ký miễn phí
                    </a>
                    <a href="{{ route('login') }}" class="block w-full py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium text-center hover:bg-white/[0.1] transition-colors">
                        <i class="fa-solid fa-right-to-bracket w-4 h-4 mr-2"></i> Đăng nhập
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- ========== MAIN CONTENT ========== -->
    <main class="pt-14 sm:pt-16">
        {{ $slot }}
    </main>

    <!-- ========== FOOTER ========== -->
    <footer class="border-t border-white/[0.05]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles w-4 h-4 sm:w-5 sm:h-5 text-purple-400"></i>
                    <span class="font-bold text-sm sm:text-base bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">ZDream</span>
                </div>
                <p class="text-white/40 text-xs sm:text-sm">© {{ date('Y') }} ZDream.vn</p>
                <div class="flex gap-4 text-white/40">
                    <a href="#" class="hover:text-white/80 text-xs sm:text-sm">Điều khoản</a>
                    <a href="#" class="hover:text-white/80 text-xs sm:text-sm">Liên hệ</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Custom Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuBtn = document.getElementById('menu-btn');
            const closeMenuBtn = document.getElementById('close-menu-btn');
            const menuOverlay = document.getElementById('menu-overlay');
            const mobileMenu = document.getElementById('mobile-menu');
            
            function openMenu() {
                menuOverlay.classList.remove('opacity-0', 'pointer-events-none');
                menuOverlay.classList.add('opacity-100');
                mobileMenu.classList.remove('closed');
                mobileMenu.classList.add('open');
            }
            
            function closeMenu() {
                menuOverlay.classList.add('opacity-0', 'pointer-events-none');
                menuOverlay.classList.remove('opacity-100');
                mobileMenu.classList.add('closed');
                mobileMenu.classList.remove('open');
            }
            
            if (menuBtn) menuBtn.addEventListener('click', openMenu);
            if (closeMenuBtn) closeMenuBtn.addEventListener('click', closeMenu);
            if (menuOverlay) menuOverlay.addEventListener('click', function(e) {
                if (e.target === menuOverlay || e.target.classList.contains('backdrop-blur-sm')) closeMenu();
            });

            // Header scroll effect
            const header = document.getElementById('header');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }
            });
        });
    </script>
</body>
</html>
