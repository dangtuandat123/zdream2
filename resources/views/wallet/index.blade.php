<x-app-layout>
    <x-slot name="title">Ví tiền - EZShot AI</x-slot>

    <div class="container mx-auto px-4 py-6 md:py-8">
        <div class="max-w-lg mx-auto space-y-4 md:space-y-6">
            
            {{-- Balance Card --}}
            <div class="glass-card p-5 md:p-6 bg-gradient-to-br from-primary-500/10 to-accent-purple/10 border-primary-500/20 animate-fade-up">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-white/50 text-sm">Số dư hiện tại</span>
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-accent-green/20 text-accent-green">
                        Active
                    </span>
                </div>
                
                <div class="flex items-baseline gap-2 mb-4">
                    <span class="text-4xl md:text-5xl font-extrabold text-white">{{ number_format($user->credits, 0) }}</span>
                    <span class="text-lg text-white/40">credits</span>
                </div>

                <div class="flex items-center gap-2 text-xs text-white/40">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>1.000 VND = 1 Credit</span>
                </div>
            </div>

            {{-- VietQR Section --}}
            <div class="glass-card overflow-hidden animate-fade-up" style="animation-delay: 0.1s;">
                <div class="p-4 md:p-5 border-b border-white/5">
                    <h2 class="text-lg font-semibold text-white/90 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Nạp tiền
                    </h2>
                    <p class="text-sm text-white/40 mt-1">Quét mã QR để chuyển khoản</p>
                </div>
                
                <div class="p-4 md:p-6">
                    {{-- QR Code --}}
                    <div class="flex justify-center mb-4">
                        <div class="p-3 bg-white rounded-2xl shadow-lg">
                            <img src="{{ $vietqrUrl }}" 
                                 alt="VietQR Code"
                                 class="w-44 h-44 md:w-52 md:h-52 object-contain">
                        </div>
                    </div>

                    {{-- Transfer Info --}}
                    <div class="space-y-2 text-center">
                        <p class="text-sm text-white/50">
                            Nội dung: 
                            <code class="px-2 py-0.5 rounded bg-white/5 text-accent-cyan font-mono text-xs">
                                EZSHOT {{ $user->id }} NAP
                            </code>
                        </p>
                        <p class="text-xs text-white/30">
                            Tiền sẽ được cộng tự động sau vài phút
                        </p>
                    </div>
                </div>
            </div>

            {{-- Transaction History --}}
            <div class="glass-card overflow-hidden animate-fade-up" style="animation-delay: 0.2s;">
                <div class="p-4 md:p-5 border-b border-white/5">
                    <h2 class="text-lg font-semibold text-white/90 flex items-center gap-2">
                        <svg class="w-5 h-5 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Lịch sử giao dịch
                    </h2>
                </div>
                
                <div class="divide-y divide-white/5">
                    @forelse($transactions as $tx)
                        <div class="flex items-center justify-between p-4 hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $tx->is_credit ? 'bg-accent-green/20' : 'bg-red-500/20' }}">
                                    @if($tx->is_credit)
                                        <svg class="w-4 h-4 text-accent-green" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm text-white/80 line-clamp-1">{{ $tx->reason }}</p>
                                    <p class="text-xs text-white/30">{{ $tx->created_at->format('d/m H:i') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold {{ $tx->is_credit ? 'text-accent-green' : 'text-red-400' }}">
                                    {{ $tx->signed_amount }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-white/5 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm text-white/40">Chưa có giao dịch nào</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
