<x-guest-layout>
    <x-slot name="title">Quên mật khẩu - ZDream</x-slot>

    <div class="text-center mb-6">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 border border-purple-500/20 flex items-center justify-center">
            <i class="fa-solid fa-key w-7 h-7 text-purple-400"></i>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Quên mật khẩu?</h1>
        <p class="text-white/50 text-sm">Nhập email để nhận link đặt lại mật khẩu</p>
    </div>

    @if (session('status'))
        <div class="mb-4 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm flex items-center gap-2">
            <i class="fa-solid fa-check-circle w-4 h-4"></i>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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
                       required autofocus>
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold text-base flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
            <i class="fa-solid fa-paper-plane w-4 h-4"></i> Gửi link đặt lại
        </button>
    </form>

    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors">
            <i class="fa-solid fa-arrow-left w-3 h-3"></i>
            Quay lại đăng nhập
        </a>
    </div>
</x-guest-layout>
