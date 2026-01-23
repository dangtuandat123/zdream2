{{-- Mobile Menu Overlay --}}
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
                {{-- Balance Card --}}
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
            
            {{-- Nav Links --}}
            <a href="{{ route('home') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                <span class="flex items-center gap-3"><i class="fa-solid fa-house w-4 h-4 text-purple-400"></i> Trang chủ</span>
                <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
            </a>
            <a href="{{ route('home') }}#styles" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                <span class="flex items-center gap-3"><i class="fa-solid fa-palette w-4 h-4 text-purple-400"></i> Styles</span>
                <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
            </a>
            @auth
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-white/[0.02] hover:bg-white/[0.05] border border-white/[0.05] text-white/80 hover:text-white transition-all">
                    <span class="flex items-center gap-3"><i class="fa-solid fa-clock-rotate-left w-4 h-4 text-purple-400"></i> Lịch sử</span>
                    <i class="fa-solid fa-chevron-right w-3 h-3 text-white/30"></i>
                </a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 hover:bg-cyan-500/15 transition-all">
                        <span class="flex items-center gap-3"><i class="fa-solid fa-crown w-4 h-4"></i> Admin Panel</span>
                        <i class="fa-solid fa-chevron-right w-3 h-3 text-cyan-400/50"></i>
                    </a>
                @endif
            @endauth
            
            <div class="h-px bg-white/[0.05] my-4"></div>
            
            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 font-medium flex items-center justify-center gap-2 hover:bg-red-500/15 transition-colors">
                        <i class="fa-solid fa-right-from-bracket w-4 h-4"></i> Đăng xuất
                    </button>
                </form>
            @else
                <a href="{{ route('register') }}" class="w-full py-3 rounded-xl bg-white text-gray-900 font-medium flex items-center justify-center gap-2 hover:bg-gray-100 transition-colors">
                    <i class="fa-solid fa-crown w-4 h-4"></i> Đăng ký miễn phí
                </a>
                <a href="{{ route('login') }}" class="w-full py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium flex items-center justify-center gap-2 hover:bg-white/[0.1] transition-colors">
                    <i class="fa-solid fa-right-to-bracket w-4 h-4"></i> Đăng nhập
                </a>
            @endauth
        </div>
    </div>
</div>
