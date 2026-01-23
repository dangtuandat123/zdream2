<x-app-layout>
    <x-slot name="title">Admin Dashboard - EZShot AI</x-slot>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-white/90 mb-8">Admin Dashboard</h1>
        
        <div class="grid md:grid-cols-3 gap-6">
            {{-- Styles Card --}}
            <a href="{{ route('admin.styles.index') }}" 
               class="group p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08] hover:border-primary-500/30 transition-all">
                <div class="w-12 h-12 mb-4 rounded-xl bg-primary-500/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white/90 group-hover:text-primary-300 transition-colors">
                    Quản lý Styles
                </h3>
                <p class="text-sm text-white/50 mt-1">Tạo và chỉnh sửa các Style AI</p>
            </a>

            {{-- Users Card --}}
            <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08] opacity-50 cursor-not-allowed">
                <div class="w-12 h-12 mb-4 rounded-xl bg-accent-purple/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m3 5.197v-1"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white/90">Quản lý Users</h3>
                <p class="text-sm text-white/50 mt-1">Sắp ra mắt...</p>
            </div>

            {{-- Stats Card --}}
            <div class="p-6 rounded-2xl bg-white/[0.03] border border-white/[0.08] opacity-50 cursor-not-allowed">
                <div class="w-12 h-12 mb-4 rounded-xl bg-accent-cyan/10 flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-cyan" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-white/90">Thống kê</h3>
                <p class="text-sm text-white/50 mt-1">Sắp ra mắt...</p>
            </div>
        </div>
    </div>
</x-app-layout>
