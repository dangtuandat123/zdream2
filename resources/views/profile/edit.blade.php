<x-app-layout>
    <x-slot name="title">Hồ sơ - ZDream</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-12 pt-20 sm:pt-24">
        <h1 class="text-2xl font-bold text-white mb-8">Hồ sơ của bạn</h1>

        {{-- Profile Info --}}
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user w-5 h-5 text-purple-400"></i>
                Thông tin cá nhân
            </h2>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="block text-sm font-medium text-white/70 mb-2">Họ tên</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="glass-input" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="glass-input" required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-primary py-2.5 px-6">
                        <i class="fa-solid fa-save w-4 h-4"></i> Lưu thay đổi
                    </button>
                    @if (session('status') === 'profile-updated')
                        <p class="text-sm text-green-400 flex items-center gap-1">
                            <i class="fa-solid fa-check w-3 h-3"></i> Đã lưu!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Update Password --}}
        <div class="glass-card p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-lock w-5 h-5 text-purple-400"></i>
                Đổi mật khẩu
            </h2>

            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('put')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-white/70 mb-2">Mật khẩu hiện tại</label>
                    <input id="current_password" type="password" name="current_password" 
                           class="glass-input" autocomplete="current-password">
                    @error('current_password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-white/70 mb-2">Mật khẩu mới</label>
                    <input id="password" type="password" name="password" 
                           class="glass-input" autocomplete="new-password">
                    @error('password', 'updatePassword')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-white/70 mb-2">Xác nhận mật khẩu</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" 
                           class="glass-input" autocomplete="new-password">
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="btn-secondary py-2.5 px-6">
                        <i class="fa-solid fa-key w-4 h-4"></i> Đổi mật khẩu
                    </button>
                    @if (session('status') === 'password-updated')
                        <p class="text-sm text-green-400 flex items-center gap-1">
                            <i class="fa-solid fa-check w-3 h-3"></i> Đã cập nhật!
                        </p>
                    @endif
                </div>
            </form>
        </div>

        {{-- Delete Account --}}
        <div class="glass-card p-6 border-red-500/20" x-data="{ showConfirm: false }">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation w-5 h-5 text-red-400"></i>
                Xóa tài khoản
            </h2>
            <p class="text-white/50 text-sm mb-4">
                Sau khi xóa, tất cả dữ liệu của bạn sẽ bị mất vĩnh viễn.
            </p>

            <button @click="showConfirm = true" class="px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 font-medium text-sm hover:bg-red-500/20 transition-colors">
                <i class="fa-solid fa-trash w-4 h-4 mr-1"></i> Xóa tài khoản
            </button>

            {{-- Confirm Modal --}}
            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                <div class="glass-card-lg p-6 w-full max-w-md" @click.away="showConfirm = false">
                    <h3 class="text-lg font-semibold text-white mb-2">Xác nhận xóa tài khoản</h3>
                    <p class="text-white/50 text-sm mb-4">Nhập mật khẩu để xác nhận:</p>
                    
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        
                        <input type="password" name="password" class="glass-input mb-4" placeholder="Mật khẩu" required>
                        
                        <div class="flex items-center gap-3">
                            <button type="button" @click="showConfirm = false" class="btn-secondary flex-1 py-2.5">
                                Hủy
                            </button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-500 text-white font-medium hover:bg-red-600 transition-colors">
                                Xóa vĩnh viễn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
