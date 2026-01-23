<x-guest-layout>
    <x-slot name="title">Đăng ký - ZDream</x-slot>

    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-2">Tạo tài khoản mới</h1>
        <p class="text-white/50 text-sm">Đăng ký để nhận <span class="text-cyan-400 font-semibold">5 Xu miễn phí</span>!</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-white/70 mb-2">Họ tên</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-user w-4 h-4 text-white/40"></i>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                       placeholder="Nguyễn Văn A"
                       required autofocus autocomplete="name">
            </div>
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-envelope w-4 h-4 text-white/40"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                       placeholder="you@example.com"
                       required autocomplete="username">
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-white/70 mb-2">Mật khẩu</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock w-4 h-4 text-white/40"></i>
                </div>
                <input id="password" type="password" name="password" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                       placeholder="Tối thiểu 8 ký tự"
                       required autocomplete="new-password">
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-white/70 mb-2">Xác nhận mật khẩu</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock w-4 h-4 text-white/40"></i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                       placeholder="Nhập lại mật khẩu"
                       required autocomplete="new-password">
            </div>
        </div>

        <div class="p-4 rounded-xl bg-gradient-to-r from-purple-500/10 to-pink-500/10 border border-purple-500/20">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-gift w-5 h-5 text-white"></i>
                </div>
                <div>
                    <p class="text-white font-medium text-sm">Quà tặng đăng ký!</p>
                    <p class="text-white/50 text-xs">Nhận ngay 5 Xu miễn phí khi tạo tài khoản</p>
                </div>
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
            <i class="fa-solid fa-crown" style="font-size: 16px;"></i>
            <span>Đăng ký miễn phí</span>
        </button>
    </form>

    <div class="flex items-center gap-4 my-6">
        <div class="flex-1 h-px bg-white/[0.1]"></div>
        <span class="text-white/40 text-sm">hoặc</span>
        <div class="flex-1 h-px bg-white/[0.1]"></div>
    </div>

    <div class="text-center">
        <p class="text-white/50 text-sm">
            Đã có tài khoản? 
            <a href="{{ route('login') }}" class="text-purple-400 hover:text-purple-300 font-medium transition-colors">Đăng nhập</a>
        </p>
    </div>
</x-guest-layout>
