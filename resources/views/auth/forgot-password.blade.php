<x-guest-layout>
    <x-slot name="title">Quên mật khẩu - ZDream</x-slot>

    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
            <i class="fa-solid fa-key w-7 h-7 text-purple-400"></i>
        </div>
        <h1 class="text-2xl font-bold text-white mb-2">Quên mật khẩu?</h1>
        <p class="text-white/50 text-sm">Nhập email để nhận link đặt lại mật khẩu</p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-4 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check-circle w-4 h-4"></i>
                {{ session('status') }}
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-envelope w-4 h-4 text-white/40"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" 
                       class="glass-input pl-11" 
                       placeholder="you@example.com"
                       required autofocus>
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary w-full py-3.5 text-base">
            <i class="fa-solid fa-paper-plane w-4 h-4"></i>
            Gửi link đặt lại
        </button>
    </form>

    {{-- Back to Login --}}
    <div class="text-center mt-6">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-white/50 hover:text-white text-sm transition-colors">
            <i class="fa-solid fa-arrow-left w-3 h-3"></i>
            Quay lại đăng nhập
        </a>
    </div>
</x-guest-layout>
