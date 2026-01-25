<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title>{{ $title ?? App\Models\Setting::get('site_name', 'ZDream') . ' - Biến Ảnh Thường Thành Tác Phẩm AI' }}</title>
    <meta name="description" content="Chọn style → Upload ảnh → Nhận kết quả. Chỉ 3 bước, không cần prompt!">

    <!-- Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets (CSS + JS với Alpine) -->
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
                            <a href="{{ route('history.index') }}" class="px-3 py-2 rounded-lg text-sm font-medium text-white/60 hover:text-white hover:bg-white/[0.05] transition-all inline-flex items-center gap-2">
                                <i class="fa-solid fa-clock-rotate-left w-3.5 h-3.5"></i> Lịch sử
                            </a>
                        @endauth
                    </nav>
                </div>
                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    @auth
                        <div class="hidden sm:flex items-center bg-white/[0.03] rounded-full border border-white/[0.08] p-1">
                            <a href="{{ route('wallet.index') }}" class="px-3 h-8 rounded-full inline-flex items-center justify-center gap-2 text-white/80 hover:bg-white/[0.05] transition-all">
                                <i class="fa-solid fa-gem text-cyan-400" style="font-size: 14px;"></i>
                                <span class="font-semibold text-sm text-white/95 leading-none">{{ number_format(auth()->user()->credits, 0) }}</span>
                            </a>
                            <a href="{{ route('wallet.index') }}" class="h-8 px-4 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium text-sm inline-flex items-center justify-center gap-1.5 hover:from-purple-400 hover:to-pink-400 transition-all leading-none">
                                <i class="fa-solid fa-plus" style="font-size: 11px;"></i>
                                <span>Nạp Xu</span>
                            </a>
                        </div>

                        <a href="{{ route('wallet.index') }}" class="sm:hidden h-9 px-3 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center gap-2 text-white/80">
                            <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                            <span class="font-semibold text-sm text-white/95">{{ number_format(auth()->user()->credits, 0) }}</span>
                        </a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex items-center justify-center h-9 px-4 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-sm font-medium hover:bg-cyan-500/20 transition-all leading-none">Admin</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center justify-center h-9 px-4 rounded-full bg-white/[0.03] border border-white/[0.1] text-white/80 text-sm font-medium hover:bg-white/[0.06] transition-all leading-none">Đăng nhập</a>
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
                    <a href="{{ route('history.index') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left w-4 h-4 text-purple-400"></i> Lịch sử</span>
                        <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                    </a>
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
                    <a href="{{ route('register') }}" class="w-full py-3 rounded-xl bg-white text-gray-900 font-medium inline-flex items-center justify-center gap-2 hover:bg-gray-100 transition-colors">
                        <i class="fa-solid fa-crown" style="font-size: 14px;"></i>
                        <span>Đăng ký miễn phí</span>
                    </a>
                    <a href="{{ route('login') }}" class="w-full py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium inline-flex items-center justify-center gap-2 hover:bg-white/[0.1] transition-colors mt-2">
                        <i class="fa-solid fa-right-to-bracket" style="font-size: 14px;"></i>
                        <span>Đăng nhập</span>
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
                    <a href="javascript:void(0)" class="hover:text-white/80 text-xs sm:text-sm" title="Sắp ra mắt">Điều khoản</a>
                    <a href="mailto:support@zdream.vn" class="hover:text-white/80 text-xs sm:text-sm">Liên hệ</a>
                </div>
            </div>
        </div>
    </footer>
    
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

    <!-- Global Lightbox Script -->
    <script>
        let lightboxOpen = false;
        let lightboxImages = [];
        let lightboxImageData = []; // For actions (download, delete)
        let lightboxIndex = 0;
        let lightboxHasActions = false;

        // Simple lightbox (just images)
        function openLightbox(index, images) {
            lightboxImages = images;
            lightboxImageData = [];
            lightboxIndex = index;
            lightboxOpen = true;
            lightboxHasActions = false;
            renderLightbox();
            document.body.style.overflow = 'hidden';
        }

        // Lightbox with actions (download, delete)
        function openLightboxWithActions(index, imageData) {
            lightboxImageData = imageData;
            lightboxImages = imageData.map(d => d.url);
            lightboxIndex = index;
            lightboxOpen = true;
            lightboxHasActions = true;
            renderLightbox();
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightboxOpen = false;
            const el = document.getElementById('global-lightbox');
            if (el) el.remove();
            document.body.style.overflow = '';
            document.removeEventListener('keydown', handleLightboxKeydown);
        }

        function lightboxPrev() {
            lightboxIndex = (lightboxIndex - 1 + lightboxImages.length) % lightboxImages.length;
            updateLightboxImage();
        }

        function lightboxNext() {
            lightboxIndex = (lightboxIndex + 1) % lightboxImages.length;
            updateLightboxImage();
        }

        function updateLightboxImage() {
            const img = document.getElementById('lightbox-main-image');
            const counter = document.getElementById('lightbox-counter');
            const downloadBtn = document.getElementById('lightbox-download-btn');
            const deleteBtn = document.getElementById('lightbox-delete-btn');
            
            if (img) img.src = lightboxImages[lightboxIndex];
            if (counter) counter.textContent = `${lightboxIndex + 1} / ${lightboxImages.length}`;
            
            // Update action buttons
            if (lightboxHasActions && lightboxImageData[lightboxIndex]) {
                if (downloadBtn) downloadBtn.href = lightboxImageData[lightboxIndex].download;
                if (deleteBtn) deleteBtn.onclick = () => deleteLightboxImage(lightboxImageData[lightboxIndex].delete);
            }
            
            updateThumbnails();
        }

        function deleteLightboxImage(deleteUrl) {
            if (confirm('Bạn có chắc muốn xóa ảnh này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function updateThumbnails() {
            document.querySelectorAll('.lightbox-thumb').forEach((thumb, idx) => {
                if (idx === lightboxIndex) {
                    thumb.style.transform = 'scale(1.1)';
                    thumb.style.opacity = '1';
                    thumb.style.boxShadow = '0 0 0 3px #a855f7';
                } else {
                    thumb.style.transform = 'scale(1)';
                    thumb.style.opacity = '0.6';
                    thumb.style.boxShadow = 'none';
                }
            });
        }

        function renderLightbox() {
            const existing = document.getElementById('global-lightbox');
            if (existing) existing.remove();
            
            const currentData = lightboxHasActions ? lightboxImageData[lightboxIndex] : null;
            
            const html = `
                <div id="global-lightbox" style="position: fixed; inset: 0; z-index: 999999; background: rgba(0,0,0,0.95); display: flex; flex-direction: column;">
                    <!-- Top Bar -->
                    <div style="height: 70px; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; flex-shrink: 0;">
                        <div id="lightbox-counter" style="background: white; color: black; padding: 8px 16px; border-radius: 9999px; font-weight: bold; font-size: 14px;">
                            ${lightboxIndex + 1} / ${lightboxImages.length}
                        </div>
                        
                        <div style="display: flex; gap: 10px; align-items: center;">
                            ${lightboxHasActions && currentData ? `
                                <a id="lightbox-download-btn" href="${currentData.download}" style="width: 44px; height: 44px; border-radius: 50%; background: #22c55e; color: white; border: none; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                    <i class="fa-solid fa-download"></i>
                                </a>
                                <button id="lightbox-delete-btn" onclick="deleteLightboxImage('${currentData.delete}')" style="width: 44px; height: 44px; border-radius: 50%; background: #ef4444; color: white; border: none; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            ` : ''}
                            <button onclick="closeLightbox()" style="width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Main Image Area -->
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; position: relative; padding: 20px; min-height: 0;" onclick="closeLightbox()">
                        ${lightboxImages.length > 1 ? `
                        <button onclick="event.stopPropagation(); lightboxPrev();" style="position: absolute; left: 20px; width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        ` : ''}
                        
                        <img 
                            id="lightbox-main-image"
                            src="${lightboxImages[lightboxIndex]}" 
                            style="max-height: 100%; max-width: calc(100% - 140px); object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);"
                            onclick="event.stopPropagation();"
                            onerror="this.src='/images/placeholder.svg'"
                        >
                        
                        ${lightboxImages.length > 1 ? `
                        <button onclick="event.stopPropagation(); lightboxNext();" style="position: absolute; right: 20px; width: 50px; height: 50px; border-radius: 50%; background: white; color: black; border: none; cursor: pointer; font-size: 20px; display: flex; align-items: center; justify-content: center; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                        ` : ''}
                    </div>
                    
                    ${lightboxImages.length > 1 ? `
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center; padding: 10px; flex-shrink: 0;">
                        <div style="display: flex; gap: 10px; padding: 10px; background: rgba(255,255,255,0.9); border-radius: 16px; max-width: 90vw; overflow-x: auto;">
                            ${lightboxImages.map((img, idx) => `
                                <button 
                                    onclick="event.stopPropagation(); lightboxIndex = ${idx}; updateLightboxImage();" 
                                    class="lightbox-thumb"
                                    style="width: 60px; height: 60px; border-radius: 8px; overflow: hidden; flex-shrink: 0; border: none; padding: 0; cursor: pointer; transition: all 0.2s; ${idx === lightboxIndex ? 'transform: scale(1.1); box-shadow: 0 0 0 3px #a855f7;' : 'opacity: 0.6;'}"
                                >
                                    <img src="${img}" style="width: 100%; height: 100%; object-fit: cover;">
                                </button>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', html);
            document.addEventListener('keydown', handleLightboxKeydown);
        }

        function handleLightboxKeydown(e) {
            if (!lightboxOpen) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') lightboxPrev();
            if (e.key === 'ArrowRight') lightboxNext();
        }
    </script>
    
    @stack('scripts')
    
    <!-- Livewire Scripts (REQUIRED for wire:click) -->
    @livewireScripts
</body>
</html>
