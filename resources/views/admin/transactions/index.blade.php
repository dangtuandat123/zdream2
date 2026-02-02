<x-app-layout>
    <x-slot name="title">Lịch sử giao dịch - Admin | ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[#d3d6db]">Lịch sử giao dịch</h1>
                <p class="text-white/50 text-sm">Tất cả giao dịch credits trong hệ thống</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Tổng cộng (Credit)</div>
                <div class="text-2xl font-bold text-green-400">+{{ number_format($stats['total_credits'], 0) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Tổng trừ (Debit)</div>
                <div class="text-2xl font-bold text-red-400">-{{ number_format($stats['total_debits'], 0) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Hôm nay (+)</div>
                <div class="text-xl font-bold text-green-400">+{{ number_format($stats['today_credits'], 0) }}</div>
            </div>
            <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4">
                <div class="text-white/50 text-sm mb-1">Hôm nay (-)</div>
                <div class="text-xl font-bold text-red-400">-{{ number_format($stats['today_debits'], 0) }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 mb-6">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Tìm theo user..."
                           class="w-full px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                </div>
                <select name="type" class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    <option value="">Tất cả loại</option>
                    <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>Cộng (Credit)</option>
                    <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>Trừ (Debit)</option>
                </select>
                <select name="source" class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                    <option value="">Tất cả nguồn</option>
                    @foreach($sources as $key => $label)
                        <option value="{{ $key }}" {{ request('source') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="date" name="from" value="{{ request('from') }}" 
                       class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                <input type="date" name="to" value="{{ request('to') }}" 
                       class="px-4 py-2 rounded-lg bg-white/[0.03] border border-white/[0.08] text-white/90 focus:outline-none focus:ring-2 focus:ring-purple-500/40">
                <button type="submit" class="px-4 py-2 rounded-lg bg-purple-500/20 border border-purple-500/30 text-purple-400 hover:bg-purple-500/30 transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
                @if(request()->hasAny(['search', 'type', 'source', 'from', 'to']))
                    <a href="{{ route('admin.transactions.index') }}" class="px-4 py-2 rounded-lg bg-white/[0.05] border border-white/[0.1] text-white/60 hover:text-[#d3d6db] transition-colors">
                        Xóa filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-white/[0.02] border-b border-white/[0.05]">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Thời gian</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Loại</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Số tiền</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Nguồn</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-white/50 uppercase">Lý do</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-white/50 uppercase">Số dư sau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/[0.05]">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-white/60 text-sm whitespace-nowrap">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($tx->user)
                                        <a href="{{ route('admin.users.show', $tx->user) }}" class="text-[#d3d6db] hover:text-purple-400 transition-colors">
                                            {{ $tx->user->name }}
                                        </a>
                                        <div class="text-white/40 text-xs">{{ $tx->user->email }}</div>
                                    @else
                                        <span class="text-white/30">User đã xóa</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($tx->type === 'credit')
                                        <span class="px-2 py-0.5 rounded text-xs bg-green-500/20 text-green-400">Credit</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs bg-red-500/20 text-red-400">Debit</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold {{ $tx->type === 'credit' ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs bg-white/[0.05] text-white/60">
                                        {{ $sources[$tx->source] ?? $tx->source }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-white/70 text-sm max-w-[200px] truncate" title="{{ $tx->reason }}">
                                    {{ $tx->reason }}
                                </td>
                                <td class="px-4 py-3 text-right text-white/50">
                                    {{ number_format($tx->balance_after, 0) }} Xu
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-white/40">
                                    <i class="fa-solid fa-receipt text-3xl mb-2"></i>
                                    <p>Không có giao dịch nào</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</x-app-layout>
