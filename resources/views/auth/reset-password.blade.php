<x-guest-layout>
    <x-slot name="title">Đặt lại mật khẩu - ZDream</x-slot>

    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-[#d3d6db] mb-2">Đặt lại mật khẩu</h1>
        <p class="text-white/50 text-sm">Nhập mật khẩu mới cho tài khoản của bạn</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-envelope w-4 h-4 text-white/40"></i>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" 
                       class="w-full px-4 py-3 pl-11 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all"
                       required autofocus autocomplete="username">
            </div>
            @error('email')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-white/70 mb-2">Mật khẩu mới</label>
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

        <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-semibold text-base flex items-center justify-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
            <i class="fa-solid fa-check w-4 h-4"></i> Đặt lại mật khẩu
        </button>
    </form>
</x-guest-layout>
