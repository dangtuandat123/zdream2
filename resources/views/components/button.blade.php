@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, ghost
    'size' => 'md', // sm, md, lg
    'loading' => false,
    'disabled' => false,
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-semibold rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-[#0a0a0f] disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match($variant) {
        'primary' => 'bg-gradient-to-r from-purple-500 to-pink-500 text-white hover:shadow-[0_8px_30px_rgba(168,85,247,0.5)] focus:ring-purple-500',
        'secondary' => 'bg-white/[0.05] border border-white/[0.1] text-white hover:bg-white/[0.1] focus:ring-white/30',
        'danger' => 'bg-red-500/20 border border-red-500/30 text-red-400 hover:bg-red-500/30 focus:ring-red-500',
        'ghost' => 'bg-transparent text-white/60 hover:text-white hover:bg-white/[0.05] focus:ring-white/20',
        default => '',
    };
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-6 py-3.5 text-base',
        default => 'px-4 py-2.5 text-sm',
    };
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses"]) }}
    @if($disabled || $loading) disabled @endif
>
    @if($loading)
        <i class="fa-solid fa-spinner animate-spin"></i>
    @elseif($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $slot }}
</button>
