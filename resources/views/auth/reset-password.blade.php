<x-guest-layout>
    <x-slot name="title">Đặt lại mật khẩu - ZDream</x-slot>

    {{-- Header --}}
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-white mb-2">Đặt lại mật khẩu</h1>
        <p class="text-white/50 text-sm">Nhập mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-envelope w-4 h-4 text-white/40"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" 
                       class="glass-input pl-11" 
                       required autofocus autocomplete="username">
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-white/70 mb-2">Mật khẩu mới</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock w-4 h-4 text-white/40"></i>
                </div>
                <input id="password" type="password" name="password" 
                       class="glass-input pl-11" 
                       placeholder="Tối thiểu 8 ký tự"
                       required autocomplete="new-password">
            </div>
            @error('password')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-white/70 mb-2">Xác nhận mật khẩu</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock w-4 h-4 text-white/40"></i>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" 
                       class="glass-input pl-11" 
                       placeholder="Nhập lại mật khẩu"
                       required autocomplete="new-password">
            </div>
            @error('password_confirmation')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-primary w-full py-3.5 text-base">
            <i class="fa-solid fa-check w-4 h-4"></i>
            Đặt lại mật khẩu
        </button>
    </form>
</x-guest-layout>
