<x-app-layout>
    <x-slot name="title">Ví tiền - ZDream</x-slot>

    <div class="max-w-lg mx-auto px-4 sm:px-6 py-6 sm:py-8">
        
        <!-- Balance Card -->
        <div class="relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 border border-purple-500/20 rounded-2xl p-5 md:p-6 mb-6">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-purple-500/20 rounded-full blur-3xl"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white/50 text-sm">Số dư hiện tại</span>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-green-500/20 text-green-400">Active</span>
                </div>
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-4xl md:text-5xl font-extrabold text-white">{{ number_format($user->credits, 0) }}</span>
                    <span class="text-lg text-white/40">Xu</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-white/40">
                    <i class="fa-solid fa-info-circle w-4 h-4"></i>
                    <span>1.000 VND = 1 Xu</span>
                </div>
            </div>
        </div>

        <!-- VietQR Section -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden mb-6">
            <div class="p-4 md:p-5 border-b border-white/[0.05]">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-qrcode w-5 h-5 text-purple-400"></i>
                    Nạp Xu
                </h2>
                <p class="text-sm text-white/40 mt-1">Quét mã QR để chuyển khoản</p>
            </div>
            <div class="p-4 md:p-6">
                <div class="flex justify-center mb-4" x-data="{ loaded: false }">
                    <div class="p-3 bg-white rounded-2xl shadow-lg relative">
                        <!-- Skeleton loader -->
                        <div x-show="!loaded" class="w-44 h-44 md:w-52 md:h-52 bg-gray-200 animate-pulse rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-qrcode text-4xl text-gray-400"></i>
                        </div>
                        <!-- Actual QR image -->
                        <img 
                            src="{{ $vietqrUrl }}" 
                            alt="VietQR Code" 
                            class="w-44 h-44 md:w-52 md:h-52 object-contain"
                            :class="{ 'hidden': !loaded }"
                            x-on:load="loaded = true"
                            x-on:error="loaded = true">
                    </div>
                </div>
                <div class="space-y-2 text-center">
                    <p class="text-sm text-white/50">
                        Nội dung: 
                        <code class="px-2 py-0.5 rounded bg-white/[0.05] text-cyan-400 font-mono text-xs">EZSHOT {{ $user->id }} NAP</code>
                    </p>
                    <p class="text-xs text-white/30">Xu sẽ được cộng tự động sau vài phút</p>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
            <div class="p-4 md:p-5 border-b border-white/[0.05]">
                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left w-5 h-5 text-white/40"></i>
                    Lịch sử giao dịch
                </h2>
            </div>
            <div class="divide-y divide-white/[0.05]">
                @forelse($transactions as $tx)
                    <div class="flex items-center justify-between p-4 hover:bg-white/[0.02] transition-colors">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tx->is_credit ? 'bg-green-500/20' : 'bg-red-500/20' }}">
                                @if($tx->is_credit)
                                    <i class="fa-solid fa-plus w-4 h-4 text-green-400"></i>
                                @else
                                    <i class="fa-solid fa-minus w-4 h-4 text-red-400"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-white/80 line-clamp-1">{{ $tx->reason }}</p>
                                <p class="text-xs text-white/30">{{ $tx->created_at->format('d/m H:i') }}</p>
                            </div>
                        </div>
                        <p class="text-sm font-semibold {{ $tx->is_credit ? 'text-green-400' : 'text-red-400' }}">
                            {{ $tx->signed_amount }}
                        </p>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <i class="fa-solid fa-receipt text-3xl text-white/20 mb-3"></i>
                        <p class="text-sm text-white/40">Chưa có giao dịch nào</p>
                    </div>
                @endforelse
            </div>
            @if($transactions->hasPages())
                <div class="p-4 border-t border-white/[0.05]">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
