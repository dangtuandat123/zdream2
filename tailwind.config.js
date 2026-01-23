import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            // =======================================
            // TYPOGRAPHY
            // =======================================
            fontFamily: {
                sans: ['Inter', 'SF Pro Display', 'Figtree', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', 'SF Mono', ...defaultTheme.fontFamily.mono],
            },

            // =======================================
            // COLORS - GLASSMORPHISM PALETTE
            // =======================================
            colors: {
                // Dark backgrounds
                dark: {
                    950: '#0a0a0f',
                    900: '#0f0f18',
                    800: '#161625',
                    700: '#1e1e32',
                    600: '#2a2a45',
                },
                // Primary (Electric Blue)
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                // Accent colors (Neon)
                accent: {
                    purple: '#a855f7',
                    pink: '#ec4899',
                    cyan: '#06b6d4',
                    green: '#22c55e',
                },
                // Glass colors
                glass: {
                    DEFAULT: 'rgba(255, 255, 255, 0.03)',
                    light: 'rgba(255, 255, 255, 0.05)',
                    medium: 'rgba(255, 255, 255, 0.08)',
                    heavy: 'rgba(255, 255, 255, 0.12)',
                    border: 'rgba(255, 255, 255, 0.08)',
                    'border-light': 'rgba(255, 255, 255, 0.12)',
                    'border-heavy': 'rgba(255, 255, 255, 0.18)',
                },
            },

            // =======================================
            // BACKGROUND COLORS
            // =======================================
            backgroundColor: {
                'glass': 'rgba(255, 255, 255, 0.03)',
                'glass-light': 'rgba(255, 255, 255, 0.05)',
                'glass-medium': 'rgba(255, 255, 255, 0.08)',
                'glass-heavy': 'rgba(255, 255, 255, 0.12)',
            },

            // =======================================
            // BORDER COLORS
            // =======================================
            borderColor: {
                'glass': 'rgba(255, 255, 255, 0.08)',
                'glass-light': 'rgba(255, 255, 255, 0.12)',
                'glass-heavy': 'rgba(255, 255, 255, 0.18)',
            },

            // =======================================
            // BACKDROP BLUR
            // =======================================
            backdropBlur: {
                'xs': '2px',
                'glass': '12px',
                'glass-heavy': '24px',
                'glass-ultra': '40px',
            },

            // =======================================
            // BOX SHADOWS
            // =======================================
            boxShadow: {
                'glass': 'inset 0 1px 1px rgba(255, 255, 255, 0.05)',
                'glass-lg': '0 8px 32px rgba(0, 0, 0, 0.4)',
                'glass-glow': '0 0 40px rgba(59, 130, 246, 0.15)',
                'neon-blue': '0 0 20px rgba(59, 130, 246, 0.5)',
                'neon-purple': '0 0 20px rgba(168, 85, 247, 0.5)',
                'neon-pink': '0 0 20px rgba(236, 72, 153, 0.5)',
                'neon-cyan': '0 0 20px rgba(6, 182, 212, 0.5)',
            },

            // =======================================
            // ANIMATIONS
            // =======================================
            animation: {
                'fade-in': 'fadeIn 0.3s ease-out',
                'slide-up': 'slideUp 0.4s ease-out',
                'scale-in': 'scaleIn 0.2s ease-out',
                'pulse-slow': 'pulse 3s infinite',
                'shimmer': 'shimmer 2s infinite',
                'glow': 'glow 2s ease-in-out infinite alternate',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.95)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
                shimmer: {
                    '0%': { backgroundPosition: '-200% 0' },
                    '100%': { backgroundPosition: '200% 0' },
                },
                glow: {
                    '0%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.3)' },
                    '100%': { boxShadow: '0 0 40px rgba(59, 130, 246, 0.6)' },
                },
            },

            // =======================================
            // SPACING & SIZING
            // =======================================
            borderRadius: {
                '4xl': '2rem',
            },
        },
    },

    plugins: [forms],
};
