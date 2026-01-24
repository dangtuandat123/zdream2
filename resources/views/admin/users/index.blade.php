<x-app-layout>
    <x-slot name="title">Quản lý Users - Admin | ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-white">Quản lý Users</h1>
                <p class="text-white/50 text-sm">{{ $stats['total'] }} users | {{ $stats['active'] }} active | {{ $stats['banned'] }} banned</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-2">
                <i class="fa-solid fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-2">
                <i class="fa-solid fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Filter & Search -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tìm theo tên hoặc email..."
                           class="w-full px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                </div>
                <select name="status" class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    <option value="">Tất cả status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                    <option value="admin" {{ request('status') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 hover:bg-purple-500/30 transition-colors">
                    <i class="fa-solid fa-search mr-1"></i> Tìm
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/60 hover:text-white transition-colors">
                        Xóa filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/[0.02] border-b border-white/[0.05]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Credits</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Ảnh</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase tracking-wider">Ngày tạo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($users as $user)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium flex items-center gap-2">
                                                {{ $user->name }}
                                                @if($user->is_admin)
                                                    <span class="px-1.5 py-0.5 rounded text-xs bg-cyan-500/20 text-cyan-400">Admin</span>
                                                @endif
                                            </div>
                                            <div class="text-white/40 text-sm">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-white font-semibold">{{ number_format($user->credits, 0) }}</span>
                                    <span class="text-white/40 text-sm">Xu</span>
                                </td>
                                <td class="px-4 py-4 text-white/70">
                                    {{ $user->generated_images_count ?? 0 }}
                                </td>
                                <td class="px-4 py-4">
                                    @if($user->is_active)
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400">Active</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400">Banned</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-white/50 text-sm">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="p-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/60 hover:text-white hover:bg-white/[0.1] transition-all"
                                           title="Xem chi tiết">
                                            <i class="fa-solid fa-eye" style="font-size: 12px;"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="p-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/60 hover:text-white hover:bg-white/[0.1] transition-all"
                                           title="Sửa">
                                            <i class="fa-solid fa-pen" style="font-size: 12px;"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="p-2 rounded-lg {{ $user->is_active ? 'bg-red-500/10 border-red-500/30 text-red-400 hover:bg-red-500/20' : 'bg-green-500/10 border-green-500/30 text-green-400 hover:bg-green-500/20' }} border transition-all"
                                                        title="{{ $user->is_active ? 'Ban user' : 'Unban user' }}">
                                                    <i class="fa-solid {{ $user->is_active ? 'fa-ban' : 'fa-check' }}" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-white/40">
                                    <i class="fa-solid fa-users text-3xl mb-2"></i>
                                    <p>Không tìm thấy user nào</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
