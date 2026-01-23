<x-app-layout>
    <x-slot name="title">Quản lý Styles - Admin | EZShot AI</x-slot>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-white/90">Quản lý Styles</h1>
            <a href="{{ route('admin.styles.create') }}" 
               class="px-4 py-2 text-sm font-medium rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-400 hover:to-primary-500 transition-all">
                + Tạo Style mới
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-300">
                {{ session('success') }}
            </div>
        @endif

        @if($styles->isEmpty())
            <div class="text-center py-16">
                <p class="text-white/50">Chưa có Style nào. Hãy tạo Style đầu tiên!</p>
            </div>
        @else
            <div class="overflow-hidden rounded-2xl bg-white/[0.03] border border-white/[0.08]">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/[0.08]">
                            <th class="px-4 py-3 text-left text-sm font-medium text-white/50">Style</th>
                            <th class="px-4 py-3 text-left text-sm font-medium text-white/50">Model</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-white/50">Giá</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-white/50">Options</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-white/50">Trạng thái</th>
                            <th class="px-4 py-3 text-right text-sm font-medium text-white/50">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($styles as $style)
                            <tr class="border-b border-white/[0.05] hover:bg-white/[0.02]">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $style->thumbnail }}" 
                                             alt="{{ $style->name }}"
                                             class="w-10 h-14 rounded object-cover">
                                        <div>
                                            <p class="font-medium text-white/90">{{ $style->name }}</p>
                                            <p class="text-xs text-white/40">/studio/{{ $style->slug }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono text-white/50 bg-white/5 px-2 py-1 rounded">
                                        {{ Str::limit($style->openrouter_model_id, 30) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-accent-cyan">
                                    {{ number_format($style->price, 0) }}
                                </td>
                                <td class="px-4 py-3 text-center text-white/60">
                                    {{ $style->options_count }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $style->is_active ? 'bg-green-500/20 text-green-400' : 'bg-white/10 text-white/40' }}">
                                        {{ $style->is_active ? 'Active' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.styles.edit', $style) }}" 
                                           class="px-3 py-1.5 text-sm rounded-lg bg-white/5 text-white/70 hover:bg-white/10 transition-colors">
                                            Sửa
                                        </a>
                                        <form action="{{ route('admin.styles.destroy', $style) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Bạn chắc chắn muốn xóa Style này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="px-3 py-1.5 text-sm rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $styles->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
