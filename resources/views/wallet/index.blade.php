<x-app-layout>
    <x-slot name="title">Ví tiền - {{ App\Models\Setting::get('site_name', 'ZDream') }}</x-slot>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="grid gap-6 lg:grid-cols-12 lg:items-stretch">
            <!-- Topup Card -->
            <div class="lg:col-span-8 h-full">
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden h-full flex flex-col">
                    <div class="p-4 sm:p-5 border-b border-white/[0.05]">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                                    <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-purple-500/15">
                                        <i class="fa-solid fa-qrcode text-purple-400 text-[14px] leading-none"></i>
                                    </span>
                                    Nạp Xu bằng VietQR
                                </h2>
                                <p class="text-sm text-white/40 mt-1">Tỉ lệ: 1.000 VND = 1 Xu • Tự động cộng sau vài phút</p>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/[0.04] border border-white/[0.08]">
                                <span class="inline-flex w-5 h-5 items-center justify-center rounded-full bg-cyan-500/15">
                                    <i class="fa-solid fa-gem text-cyan-400 text-[12px] leading-none"></i>
                                </span>
                                <span class="text-xs text-white/60">Số dư:</span>
                                <span class="text-sm font-semibold text-white">{{ number_format($user->credits, 0) }} Xu</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 grid gap-6 lg:grid-cols-2 items-stretch flex-1">
                        <!-- QR Card -->
                        <div class="bg-white/[0.02] border border-cyan-500/20 ring-1 ring-cyan-400/20 shadow-[0_0_40px_rgba(34,211,238,0.08)] rounded-2xl p-5 flex flex-col items-center text-center h-full" x-data="{ loaded: false }">
                            <div class="flex items-center gap-2 text-sm text-white/70 mb-4">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold">1</span>
                                Quét mã QR (quan trọng)
                            </div>
                            <div class="p-3 bg-white rounded-2xl shadow-lg relative">
                                <div x-show="!loaded" class="w-60 h-60 md:w-72 md:h-72 bg-gray-200 animate-pulse rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-qrcode text-4xl text-gray-400"></i>
                                </div>
                                <img
                                    src="{{ $vietqrUrl }}"
                                    alt="VietQR Code"
                                    class="w-60 h-60 md:w-72 md:h-72 object-contain"
                                    :class="{ 'hidden': !loaded }"
                                    x-on:load="loaded = true"
                                    x-on:error="loaded = true"
                                >
                            </div>
                            <button
                                type="button"
                                onclick="downloadQr('{{ $vietqrUrl }}')"
                                class="mt-4 px-4 py-2 rounded-lg bg-cyan-500/20 border border-cyan-500/30 text-cyan-100 text-sm hover:bg-cyan-500/30 transition-colors inline-flex items-center gap-2"
                            >
                                <span class="inline-flex w-4 h-4 items-center justify-center">
                                    <i class="fa-solid fa-download text-white/60 text-[12px] leading-none"></i>
                                </span>
                                Tải mã QR
                            </button>
                        </div>

                        <!-- Transfer Info Card -->
                        <div class="bg-white/[0.02] border border-white/[0.06] rounded-2xl p-5 flex flex-col h-full">
                            <div class="flex items-center gap-2 text-sm text-white/70 mb-4">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold">2</span>
                                Thông tin chuyển khoản
                            </div>

                            <div class="space-y-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-white/40">Ngân hàng</p>
                                        <p class="text-sm text-white/90 font-medium mt-1">{{ $bankInfo['bank_name'] }}</p>
                                    </div>
                                    <span class="inline-flex w-9 h-9 items-center justify-center rounded-xl bg-white/[0.05]">
                                        <i class="fa-solid fa-building-columns text-white/60 text-[13px] leading-none"></i>
                                    </span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-white/40">Số tài khoản</p>
                                        <p class="text-sm text-white/90 font-mono mt-1">{{ $bankInfo['account_number'] }}</p>
                                    </div>
                                    <button onclick="copyToClipboard('{{ $bankInfo['account_number'] }}', this)" class="px-3 py-2 rounded-lg bg-white/[0.05] hover:bg-white/[0.1] text-white/70 hover:text-white transition-colors inline-flex items-center gap-2" aria-label="Copy số tài khoản">
                                        <i class="fa-solid fa-copy text-[12px] leading-none"></i>
                                        <span class="text-xs">Copy</span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-white/40">Tên tài khoản</p>
                                        <p class="text-sm text-white/90 mt-1">{{ $bankInfo['account_name'] }}</p>
                                    </div>
                                    <button onclick="copyToClipboard('{{ $bankInfo['account_name'] }}', this)" class="px-3 py-2 rounded-lg bg-white/[0.05] hover:bg-white/[0.1] text-white/70 hover:text-white transition-colors inline-flex items-center gap-2" aria-label="Copy tên tài khoản">
                                        <i class="fa-solid fa-copy text-[12px] leading-none"></i>
                                        <span class="text-xs">Copy</span>
                                    </button>
                                </div>

                                <div class="flex items-center justify-between rounded-xl border border-cyan-500/30 bg-cyan-500/10 px-3 py-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-white/50">Nội dung CK</span>
                                            <span class="text-[10px] font-semibold uppercase tracking-wider text-cyan-300 bg-cyan-500/20 px-2 py-0.5 rounded-full">Quan trọng</span>
                                        </div>
                                        <p class="text-sm text-cyan-200 font-mono mt-1">{{ $bankInfo['transfer_content'] }}</p>
                                    </div>
                                    <button onclick="copyToClipboard('{{ $bankInfo['transfer_content'] }}', this)" class="px-3 py-2 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-100 transition-colors inline-flex items-center gap-2" aria-label="Copy nội dung chuyển khoản">
                                        <i class="fa-solid fa-copy text-[12px] leading-none"></i>
                                        <span class="text-xs">Copy</span>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-white/35 flex items-center gap-2">
                                <span class="inline-flex w-4 h-4 items-center justify-center rounded-full bg-white/5">
                                    <i class="fa-solid fa-clock text-white/50 text-[11px] leading-none"></i>
                                </span>
                                <span>Xu sẽ được cộng tự động sau vài phút, không cần thao tác thêm.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="lg:col-span-4 h-full flex flex-col gap-4">
                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl p-4">
                    <h3 class="text-sm font-semibold text-white mb-3">Hướng dẫn nhanh</h3>
                    <div class="space-y-2 text-sm text-white/70">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold">1</span>
                            Quét mã QR để chuyển khoản
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-purple-500/20 text-purple-300 text-xs font-bold">2</span>
                            Dùng đúng “Nội dung CK” để nhận xu tự động
                        </div>
                    </div>
                </div>

                <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden lg:sticky lg:top-24 flex flex-col flex-1 min-h-0">
                    <div class="p-4 sm:p-5 border-b border-white/[0.05] flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-full bg-white/5">
                                <i class="fa-solid fa-clock-rotate-left text-white/60 text-[13px] leading-none"></i>
                            </span>
                            Lịch sử giao dịch
                        </h2>
                        <span class="text-xs text-white/40">5 giao dịch gần nhất</span>
                    </div>
                    <div class="divide-y divide-white/[0.05] overflow-y-auto flex-1 min-h-0">
                    @forelse($transactions as $tx)
                        <div class="flex items-center justify-between p-4 hover:bg-white/[0.02] transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $tx->is_credit ? 'bg-green-500/20' : 'bg-red-500/20' }}">
                                    @if($tx->is_credit)
                                        <i class="fa-solid fa-plus text-green-400 text-[12px] leading-none"></i>
                                    @else
                                        <i class="fa-solid fa-minus text-red-400 text-[12px] leading-none"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm text-white/85 line-clamp-1">{{ $tx->reason }}</p>
                                    <p class="text-xs text-white/35">{{ $tx->created_at->format('d/m H:i') }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold {{ $tx->is_credit ? 'text-green-400' : 'text-red-400' }}">
                                {{ $tx->signed_amount }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <i class="fa-solid fa-receipt text-3xl text-white/20 mb-3"></i>
                            <p class="text-sm text-white/50">Chưa có giao dịch nào</p>
                            <p class="text-xs text-white/35 mt-1">Hãy nạp Xu để bắt đầu tạo ảnh</p>
                        </div>
                    @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(() => {
                const icon = button.querySelector('i');
                icon.classList.remove('fa-copy');
                icon.classList.add('fa-check');
                button.classList.add('text-green-400');

                setTimeout(() => {
                    icon.classList.remove('fa-check');
                    icon.classList.add('fa-copy');
                    button.classList.remove('text-green-400');
                }, 2000);
            });
        }

        function downloadQr(url) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'vietqr.png';
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    URL.revokeObjectURL(link.href);
                })
                .catch(() => {
                    window.open(url, '_blank');
                });
        }
    </script>
    @endpush
</x-app-layout>
