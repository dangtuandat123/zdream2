<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
</head>
<body class="min-h-screen text-white antialiased">

    {{-- Header --}}
    @include('layouts.header')

    {{-- Mobile Menu Overlay --}}
    @include('layouts.mobile-menu')

    {{-- Page Content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-white/[0.05]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles w-4 h-4 sm:w-5 sm:h-5 text-purple-400"></i>
                    <span class="font-bold text-sm sm:text-base gradient-text">ZDream</span>
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
    
    <!-- Alpine.js & Custom Scripts -->
    <script>
        // Mobile menu toggle
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
