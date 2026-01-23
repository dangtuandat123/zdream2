<x-guest-layout>
    <x-slot name="title">Đăng nhập - ZDream</x-slot>

    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-2">Chào mừng trở lại!</h1>
        <p class="text-white/50 text-sm">Đăng nhập để tiếp tục tạo ảnh AI</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-3 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-2">
            <i class="fa-solid fa-check-circle w-4 h-4"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-envelope w-4 h-4 text-white/40"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                       placeholder="you@example.com"
                       required autofocus autocomplete="username">
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
                       placeholder="••••••••"
                       required autocomplete="current-password">
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" 
                       class="w-4 h-4 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50">
                <span class="text-sm text-white/60">Ghi nhớ</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm text-purple-400 hover:text-purple-300 transition-colors">
                    Quên mật khẩu?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base inline-flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
            <i class="fa-solid fa-right-to-bracket" style="font-size: 16px;"></i>
            <span>Đăng nhập</span>
        </button>
    </form>

    <div class="flex items-center gap-4 my-6">
        <div class="flex-1 h-px bg-white/[0.1]"></div>
        <span class="text-white/40 text-sm">hoặc</span>
        <div class="flex-1 h-px bg-white/[0.1]"></div>
    </div>

    <div class="text-center">
        <p class="text-white/50 text-sm">
            Chưa có tài khoản? 
            <a href="{{ route('register') }}" class="text-purple-400 hover:text-purple-300 font-medium transition-colors">Đăng ký ngay</a>
        </p>
    </div>
</x-guest-layout>
