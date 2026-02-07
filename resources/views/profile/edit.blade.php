<x-app-layout>
    <x-slot name="title">Hồ sơ - ZDream</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <h1 class="text-2xl font-bold text-[#d3d6db] mb-8 flex items-center gap-3">
            <i class="fa-solid fa-user-circle w-6 h-6 text-purple-400"></i>
            Hồ sơ của bạn
        </h1>

        <!-- Profile Info -->
        <div class="bg-[#1b1c21] border border-[#2a2b30] rounded-2xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-[#d3d6db] mb-4 flex items-center gap-2">
                <i class="fa-solid fa-user w-5 h-5 text-purple-400"></i>
                Thông tin cá nhân
            </h2>

            <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('patch')

                <div>
                    <label for="name" class="block text-sm font-medium text-white/70 mb-2">Họ tên</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" 
                           class="w-full px-4 py-3 rounded-xl bg-[#1b1c21] border border-[#2a2b30] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" 
                           class="w-full px-4 py-3 rounded-xl bg-[#1b1c21] border border-[#2a2b30] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 focus:border-purple-500/40 transition-all" 
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-[#d3d6db] font-medium flex items-center gap-2 hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] transition-all">
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


        <!-- Delete Account -->
        <div class="bg-[#1b1c21] border border-red-500/20 rounded-2xl p-6" x-data="{ showConfirm: false }">
            <h2 class="text-lg font-semibold text-[#d3d6db] mb-4 flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation w-5 h-5 text-red-400"></i>
                Xóa tài khoản
            </h2>
            <p class="text-white/50 text-sm mb-4">
                Sau khi xóa, tất cả dữ liệu của bạn sẽ bị mất vĩnh viễn.
            </p>

            <button @click="showConfirm = true" class="px-4 py-2.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 font-medium text-sm hover:bg-red-500/20 transition-colors">
                <i class="fa-solid fa-trash w-4 h-4 mr-1"></i> Xóa tài khoản
            </button>

            <!-- Confirm Modal -->
            <div x-show="showConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 ">
                <div class="bg-[#0f0f18] border border-white/[0.1] rounded-2xl p-6 w-full max-w-md" @click.away="showConfirm = false">
                    <h3 class="text-lg font-semibold text-[#d3d6db] mb-2">Xác nhận xóa tài khoản</h3>
                    <p class="text-white/50 text-sm mb-4">Nhap "DELETE" de xac nhan:</p>
                    
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        
                        <input type="text" name="confirm_delete" 
                               class="w-full px-4 py-3 rounded-xl bg-[#1b1c21] border border-[#2a2b30] text-white/90 mb-4" 
                               placeholder="Nhap DELETE" required>
                        
                        <div class="flex items-center gap-3">
                            <button type="button" @click="showConfirm = false" class="flex-1 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.1] text-[#d3d6db] font-medium">
                                Hủy
                            </button>
                            <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-500 text-[#d3d6db] font-medium hover:bg-red-600 transition-colors">
                                Xóa vĩnh viễn
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
