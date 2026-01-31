<x-app-layout>
    <x-slot name="title">Admin Dashboard - ZDream</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                    <i class="fa-solid fa-crown w-6 h-6 text-cyan-400"></i>
                    Admin Dashboard
                </h1>
                <p class="text-white/50 text-sm mt-1">Quản lý hệ thống ZDream</p>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <a href="{{ route('admin.users.index') }}"
                class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 hover:border-purple-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-users w-5 h-5 text-purple-400"></i>
                    </div>
                    <span class="text-white/50 text-sm">Users</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ \App\Models\User::count() }}</p>
            </a>
            <a href="{{ route('admin.styles.index') }}"
                class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 hover:border-pink-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-pink-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-palette w-5 h-5 text-pink-400"></i>
                    </div>
                    <span class="text-white/50 text-sm">Styles</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ \App\Models\Style::count() }}</p>
            </a>
            <a href="{{ route('admin.images.index') }}"
                class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 hover:border-cyan-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-image w-5 h-5 text-cyan-400"></i>
                    </div>
                    <span class="text-white/50 text-sm">Ảnh đã tạo</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ \App\Models\GeneratedImage::count() }}</p>
            </a>
            <a href="{{ route('admin.transactions.index') }}"
                class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 hover:border-green-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-coins w-5 h-5 text-green-400"></i>
                    </div>
                    <span class="text-white/50 text-sm">Tổng Xu</span>
                </div>
                <p class="text-2xl font-bold text-white">{{ number_format(\App\Models\User::sum('credits'), 0) }}</p>
            </a>
        </div>

        <!-- BFL API Status -->
        @php
            $bfl = app(\App\Services\BflService::class);
            $credits = $bfl->checkCredits();
        @endphp
        <div class="bg-white/[0.03] border border-white/[0.08] rounded-xl p-4 mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-robot w-5 h-5 text-orange-400"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-medium">BFL API</h3>
                        <p class="text-white/40 text-xs">Black Forest Labs</p>
                    </div>
                </div>
            </div>
            @if(isset($credits['error']))
                <div class="text-red-400 text-sm">
                    <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                    {{ $credits['error'] }}
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-white/40 text-xs mb-1">Credits Balance</p>
                        <p class="text-lg font-bold text-white">
                            @if(isset($credits['credits']) && $credits['credits'] !== null)
                                {{ number_format($credits['credits'], 4) }}
                            @else
                                <span class="text-white/40">N/A</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-white/40 text-xs mb-1">Info</p>
                        <p class="text-sm text-white/60">Kiểm tra thêm tại dashboard BFL</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('admin.users.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-purple-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                        <i class="fa-solid fa-users w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-purple-300 transition-colors">Quản
                            lý Users</h3>
                        <p class="text-white/50 text-sm">Xem, ban, cộng credits</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-purple-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.styles.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-pink-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                        <i class="fa-solid fa-palette w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-pink-300 transition-colors">Quản lý
                            Styles</h3>
                        <p class="text-white/50 text-sm">Thêm, sửa, xóa styles</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-pink-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.tags.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-orange-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center">
                        <i class="fa-solid fa-tags w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-orange-300 transition-colors">Quản
                            lý Tags</h3>
                        <p class="text-white/50 text-sm">HOT, MỚI, SALE...</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-orange-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.images.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-cyan-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                        <i class="fa-solid fa-images w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-cyan-300 transition-colors">Quản lý
                            Ảnh</h3>
                        <p class="text-white/50 text-sm">Xem, moderate ảnh</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-cyan-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.transactions.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-green-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <i class="fa-solid fa-receipt w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-green-300 transition-colors">Lịch
                            sử giao dịch</h3>
                        <p class="text-white/50 text-sm">Audit tất cả transactions</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-green-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.edit-studio.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-teal-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center">
                        <i class="fa-solid fa-wand-magic-sparkles w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-teal-300 transition-colors">Edit
                            Studio Settings</h3>
                        <p class="text-white/50 text-sm">Models & Prompts</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-teal-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('admin.settings.index') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-yellow-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center">
                        <i class="fa-solid fa-cog w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-yellow-300 transition-colors">Cài
                            đặt</h3>
                        <p class="text-white/50 text-sm">API keys, defaults</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-yellow-400 transition-colors"></i>
                </div>
            </a>
            <a href="{{ route('home') }}"
                class="group bg-white/[0.03] border border-white/[0.08] rounded-xl p-6 hover:border-indigo-500/30 hover:bg-white/[0.05] transition-all">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                        <i class="fa-solid fa-eye w-6 h-6 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white group-hover:text-indigo-300 transition-colors">Xem
                            trang chủ</h3>
                        <p class="text-white/50 text-sm">Xem như người dùng</p>
                    </div>
                    <i
                        class="fa-solid fa-chevron-right w-4 h-4 text-white/30 ml-auto group-hover:text-indigo-400 transition-colors"></i>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>