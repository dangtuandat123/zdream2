{{-- Header - ZDream Style --}}
<header id="header" class="fixed top-0 left-0 right-0 z-50 bg-[#0a0a0f]/80 backdrop-blur-[12px] border-b border-white/[0.03] transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-14 sm:h-16">
            {{-- Left: Logo + Nav --}}
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2 group flex-shrink-0">
                    <i class="fa-solid fa-wand-magic-sparkles w-5 h-5 text-purple-400 transition-transform duration-300 group-hover:rotate-12"></i>
                    <span class="text-lg sm:text-xl font-bold gradient-text">ZDream</span>
                </a>
                <nav class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="nav-link">
                        <i class="fa-solid fa-house w-3.5 h-3.5"></i> Trang chủ
                    </a>
                    <a href="{{ route('home') }}#styles" class="nav-link">
                        <i class="fa-solid fa-palette w-3.5 h-3.5"></i> Styles
                    </a>
                    @auth
                        <a href="#" class="nav-link">
                            <i class="fa-solid fa-clock-rotate-left w-3.5 h-3.5"></i> Lịch sử
                        </a>
                    @endauth
                </nav>
            </div>
            
            {{-- Right: Actions --}}
            <div class="flex items-center gap-2">
                @auth
                    {{-- Credits Badge (Desktop) --}}
                    <div class="hidden sm:flex credits-badge">
                        <a href="{{ route('wallet.index') }}" class="credits-badge-inner text-white/80 hover:bg-white/[0.05] transition-all">
                            <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                            <span class="font-semibold text-sm text-white/95">{{ number_format(auth()->user()->credits, 0) }}</span>
                        </a>
                        <a href="{{ route('wallet.index') }}" class="credits-badge-btn">
                            <i class="fa-solid fa-plus w-3 h-3"></i> Nạp Xu
                        </a>
                    </div>
                    
                    {{-- Credits Badge (Mobile) --}}
                    <a href="{{ route('wallet.index') }}" class="sm:hidden h-9 px-3 rounded-full bg-white/[0.03] border border-white/[0.08] flex items-center gap-2 text-white/80">
                        <i class="fa-solid fa-gem w-4 h-4 text-cyan-400"></i>
                        <span class="font-semibold text-sm text-white/95">{{ number_format(auth()->user()->credits, 0) }}</span>
                    </a>
                    
                    {{-- User Menu (Desktop) --}}
                    <div class="hidden sm:block relative" x-data="{ open: false }">
                        <button @click="open = !open" class="h-9 px-4 rounded-full bg-white/[0.03] border border-white/[0.1] text-white/80 text-sm hover:bg-white/[0.06] transition-all flex items-center gap-2">
                            <span>{{ auth()->user()->name }}</span>
                            <i class="fa-solid fa-chevron-down w-3 h-3 text-white/50"></i>
                        </button>
                        
                        <div x-show="open" 
                             x-transition
                             @click.away="open = false"
                             class="absolute right-0 mt-2 w-48 rounded-xl bg-[#0f0f18]/98 backdrop-blur-xl border border-white/[0.1] shadow-xl overflow-hidden">
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm text-cyan-400 hover:bg-white/[0.05] transition-colors">
                                    <i class="fa-solid fa-crown w-4 h-4 mr-2"></i> Admin Panel
                                </a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-white/70 hover:bg-white/[0.05] hover:text-white transition-colors">
                                <i class="fa-solid fa-user w-4 h-4 mr-2"></i> Hồ sơ
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-400 hover:bg-white/[0.05] transition-colors">
                                    <i class="fa-solid fa-right-from-bracket w-4 h-4 mr-2"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:block h-9 px-4 rounded-full bg-white/[0.03] border border-white/[0.1] text-white/80 text-sm hover:bg-white/[0.06] transition-all">
                        Đăng nhập
                    </a>
                @endauth
                
                {{-- Mobile Menu Button --}}
                <button id="menu-btn" class="md:hidden w-10 h-10 rounded-xl bg-white/[0.03] border border-white/[0.08] flex items-center justify-center text-white/80 hover:text-white hover:bg-white/[0.06] transition-all">
                    <i class="fa-solid fa-bars w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>
</header>
