<x-app-layout>
    <x-slot name="title">{{ $user->name }} - Admin | ZDream</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-xl bg-white/[0.05] border border-white/[0.1] flex items-center justify-center text-white/60 hover:text-white hover:bg-white/[0.1] transition-all">
                <i class="fa-solid fa-arrow-left" style="font-size: 14px;"></i>
            </a>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                    {{ $user->name }}
                    @if($user->is_admin)
                        <span class="px-2 py-0.5 rounded text-xs bg-cyan-500/20 text-cyan-400">Admin</span>
                    @endif
                    @if(!$user->is_active)
                        <span class="px-2 py-0.5 rounded text-xs bg-red-500/20 text-red-400">Banned</span>
                    @endif
                </h1>
                <p class="text-white/50 text-sm">{{ $user->email }} • Tham gia {{ $user->created_at->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 rounded-xl bg-white/[0.05] border border-white/[0.1] text-white/80 hover:bg-white/[0.1] transition-all inline-flex items-center gap-2">
                <i class="fa-solid fa-pen" style="font-size: 12px;"></i>
                Sửa
            </a>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Stats & Credit Adjustment -->
            <div class="space-y-6">
                <!-- User Stats Card -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-chart-bar text-purple-400"></i>
                        Thống kê
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Credits hiện tại</span>
                            <span class="text-2xl font-bold text-cyan-400">{{ number_format($user->credits, 0) }} Xu</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Ảnh đã tạo</span>
                            <span class="text-lg font-semibold text-white">{{ $user->generated_images_count ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-white/60">Giao dịch</span>
                            <span class="text-lg font-semibold text-white">{{ $transactions->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Credit Adjustment Card -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-coins text-yellow-400"></i>
                        Điều chỉnh Credits
                    </h3>
                    <form method="POST" action="{{ route('admin.users.adjust-credits', $user) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-2">Số lượng (+ cộng / - trừ)</label>
                            <input type="number" name="amount" step="0.01" required
                                   placeholder="VD: 100 hoặc -50"
                                   class="w-full px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-white/70 mb-2">Lý do</label>
                            <input type="text" name="reason" required
                                   placeholder="VD: Refund lỗi API, Bonus khuyến mãi..."
                                   class="w-full px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 text-white font-medium hover:shadow-lg transition-all">
                            Thực hiện
                        </button>
                    </form>
                </div>

                <!-- Ban/Unban Card -->
                @if($user->id !== auth()->id())
                    <div class="bg-white/[0.03] border {{ $user->is_active ? 'border-red-500/20' : 'border-green-500/20' }} rounded-2xl p-6">
                        <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <i class="fa-solid {{ $user->is_active ? 'fa-ban text-red-400' : 'fa-check text-green-400' }}"></i>
                            {{ $user->is_active ? 'Vô hiệu hóa tài khoản' : 'Kích hoạt tài khoản' }}
                        </h3>
                        <p class="text-white/50 text-sm mb-4">
                            {{ $user->is_active 
                                ? 'User sẽ không thể đăng nhập hoặc sử dụng dịch vụ.' 
                                : 'User sẽ được phép đăng nhập và sử dụng dịch vụ bình thường.' }}
                        </p>
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl {{ $user->is_active ? 'bg-red-500/20 border border-red-500/30 text-red-400 hover:bg-red-500/30' : 'bg-green-500/20 border border-green-500/30 text-green-400 hover:bg-green-500/30' }} font-medium transition-all">
                                {{ $user->is_active ? 'Ban User' : 'Unban User' }}
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Right Column: Transactions & Images -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Recent Transactions -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.05] flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-purple-400"></i>
                            Giao dịch gần đây
                        </h3>
                    </div>
                    <div class="divide-y divide-white/[0.05]">
                        @forelse($transactions as $tx)
                            <div class="px-6 py-3 flex items-center justify-between">
                                <div>
                                    <div class="text-white/80 text-sm">{{ $tx->reason }}</div>
                                    <div class="text-white/40 text-xs">{{ $tx->created_at->format('d/m/Y H:i') }} • {{ $tx->source }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="{{ $tx->type === 'credit' ? 'text-green-400' : 'text-red-400' }} font-semibold">
                                        {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 0) }} Xu
                                    </div>
                                    <div class="text-white/30 text-xs">Còn {{ number_format($tx->balance_after, 0) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-8 text-center text-white/40">
                                Chưa có giao dịch nào
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Images -->
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-white/[0.05]">
                        <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                            <i class="fa-solid fa-images text-pink-400"></i>
                            Ảnh đã tạo gần đây
                        </h3>
                    </div>
                    @if($recentImages->isEmpty())
                        <div class="px-6 py-8 text-center text-white/40">
                            Chưa có ảnh nào
                        </div>
                    @else
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 p-4">
                            @foreach($recentImages as $image)
                                <div class="aspect-square rounded-lg overflow-hidden bg-black/20">
                                    @if($image->status === 'completed' && $image->image_url)
                                        <img src="{{ $image->image_url }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            @if($image->status === 'failed')
                                                <i class="fa-solid fa-exclamation-triangle text-red-400"></i>
                                            @else
                                                <i class="fa-solid fa-spinner fa-spin text-white/30"></i>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
