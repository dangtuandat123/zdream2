<x-app-layout>
    <x-slot name="title">Sửa {{ $user->name }} - Admin | ZDream</x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.users.show', $user) }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Sửa thông tin User</h1>
                <p class="text-white/50 text-sm">{{ $user->email }}</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-user text-purple-400"></i>
                    Thông tin cơ bản
                </h2>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white/70 mb-2">Họ tên</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-white/70 mb-2">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-4 py-3 rounded-xl bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40 transition-all">
                        @error('email')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="is_admin" name="is_admin" value="1" 
                               {{ $user->is_admin ? 'checked' : '' }}
                               class="w-5 h-5 rounded bg-white/[0.03] border-white/[0.15] text-purple-500 focus:ring-purple-500/50">
                        <label for="is_admin" class="text-sm text-white/70">
                            Quyền Admin
                            <span class="text-white/40">(có thể truy cập Admin Panel)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-info-circle text-cyan-400"></i>
                    Thông tin khác (chỉ đọc)
                </h2>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-white/50">ID:</span>
                        <span class="text-white ml-2">#{{ $user->id }}</span>
                    </div>
                    <div>
                        <span class="text-white/50">Credits:</span>
                        <span class="text-cyan-400 ml-2 font-semibold">{{ number_format($user->credits, 0) }} Xu</span>
                    </div>
                    <div>
                        <span class="text-white/50">Status:</span>
                        @if($user->is_active)
                            <span class="ml-2 text-green-400">Active</span>
                        @else
                            <span class="ml-2 text-red-400">Banned</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-white/50">Ngày tạo:</span>
                        <span class="text-white ml-2">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <p class="text-white/30 text-xs mt-4">
                    * Để điều chỉnh Credits hoặc Ban/Unban user, sử dụng các chức năng tại trang Chi tiết User.
                </p>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold flex items-center gap-2 hover:shadow-lg transition-all">
                    <i class="fa-solid fa-save" style="font-size: 14px;"></i>
                    Lưu thay đổi
                </button>
                <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-3 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 font-medium hover:bg-white/[0.1] transition-all">
                    Hủy
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
