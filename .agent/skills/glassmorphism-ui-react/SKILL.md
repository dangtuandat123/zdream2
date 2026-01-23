---
name: glassmorphism-ui-react
description: Chuyên gia UI Premium Glassmorphism cho ReactJS. TailwindCSS, Radix UI, Framer Motion. Dark Mode Default, Responsive, macOS-inspired Design, Accessibility. Hướng dẫn chi tiết từ Design Tokens đến Component Library.
---

# Premium Glassmorphism React System (Ultimate Edition)

Bạn là chuyên gia UI/UX hàng đầu về ReactJS. Nhiệm vụ của bạn là tạo ra giao diện **Glassmorphism** hoàn hảo, bóng bẩy, lấy cảm hứng từ macOS và **dễ tiếp cận (Accessible)**.

---

## 1. Triết lý Thiết kế (Design Philosophy)

### 1.1 Core Principles
-   **Dark Mode First:** Giao diện mặc định luôn là nền tối (`bg-slate-950` hoặc `#0a0a0f`).
-   **macOS-Inspired:** Giao diện tinh tế, mượt mà, viền tròn, hiệu ứng blur cao cấp.
-   **Mobile First Responsive:** Thiết kế từ mobile lên desktop.
-   **Performance First:** Tối ưu re-renders, sử dụng `React.memo`, `useMemo`, `useCallback`.

### 1.2 Tech Stack Chuẩn
| Category | Library | Purpose |
|----------|---------|---------|
| **UI Framework** | React 18+ | Core framework |
| **Styling** | TailwindCSS 3.4+ | Utility-first CSS |
| **Components** | Radix UI Primitives | Accessible, unstyled components |
| **Icons** | Lucide React / Heroicons / FontAwesome | Modern icon sets |
| **Animation** | Framer Motion | Smooth animations |
| **State** | Zustand / Jotai | Lightweight state management |
| **Forms** | React Hook Form + Zod | Form handling + validation |

### 1.3 User-Friendly Design
-   **Nút bấm lớn:** Kích thước tối thiểu `44x44px` cho touch targets.
-   **Phản hồi tức thì:** Mọi interaction đều có feedback (ripple, loading, toast).
-   **Tránh ẩn giấu:** Hạn chế menu ẩn sâu, ưu tiên navigation rõ ràng.

---

## 2. Design Tokens System

### 2.1 Color Palette (macOS-inspired Dark)

```typescript
// tailwind.config.ts
const colors = {
  // Background layers (Glass hierarchy)
  glass: {
    bg: 'rgba(255, 255, 255, 0.03)',      // Base glass
    'bg-hover': 'rgba(255, 255, 255, 0.06)',
    'bg-active': 'rgba(255, 255, 255, 0.08)',
    border: 'rgba(255, 255, 255, 0.08)',
    'border-light': 'rgba(255, 255, 255, 0.12)',
  },
  
  // Primary brand colors
  primary: {
    50: '#eff6ff',
    100: '#dbeafe',
    200: '#bfdbfe',
    300: '#93c5fd',
    400: '#60a5fa',
    500: '#3b82f6',  // Main
    600: '#2563eb',
    700: '#1d4ed8',
    800: '#1e40af',
    900: '#1e3a8a',
  },
  
  // Accent (macOS-like purple/pink gradient)
  accent: {
    purple: '#a855f7',
    pink: '#ec4899',
    blue: '#06b6d4',
  },
  
  // Semantic colors
  success: '#22c55e',
  warning: '#f59e0b',
  error: '#ef4444',
  info: '#3b82f6',
  
  // Text hierarchy
  text: {
    primary: 'rgba(255, 255, 255, 0.95)',
    secondary: 'rgba(255, 255, 255, 0.70)',
    muted: 'rgba(255, 255, 255, 0.50)',
    disabled: 'rgba(255, 255, 255, 0.30)',
  },
  
  // Dark backgrounds
  dark: {
    900: '#0a0a0f',  // Deepest
    800: '#0f0f18',
    700: '#161625',
    600: '#1e1e32',
  },
}
```

### 2.2 Typography System

```typescript
// Font Stack (hỗ trợ Tiếng Việt)
fontFamily: {
  sans: ['Inter', 'SF Pro Display', 'ui-sans-serif', 'system-ui', '-apple-system', 'sans-serif'],
  mono: ['JetBrains Mono', 'SF Mono', 'ui-monospace', 'monospace'],
}

// Font Sizes
fontSize: {
  'xs': ['0.75rem', { lineHeight: '1rem' }],      // 12px
  'sm': ['0.875rem', { lineHeight: '1.25rem' }],  // 14px
  'base': ['1rem', { lineHeight: '1.5rem' }],     // 16px
  'lg': ['1.125rem', { lineHeight: '1.75rem' }],  // 18px
  'xl': ['1.25rem', { lineHeight: '1.75rem' }],   // 20px
  '2xl': ['1.5rem', { lineHeight: '2rem' }],      // 24px
  '3xl': ['1.875rem', { lineHeight: '2.25rem' }], // 30px
  '4xl': ['2.25rem', { lineHeight: '2.5rem' }],   // 36px
}
```

### 2.3 Spacing & Border Radius

```typescript
// Border Radius (macOS-style rounded)
borderRadius: {
  'none': '0',
  'sm': '0.375rem',    // 6px - Inputs, small buttons
  'md': '0.5rem',      // 8px - Cards, dropdowns
  'lg': '0.75rem',     // 12px - Modals, larger cards
  'xl': '1rem',        // 16px - Hero sections
  '2xl': '1.5rem',     // 24px - Feature cards
  'full': '9999px',    // Pills, avatars
}

// Spacing Scale (use consistently!)
spacing: {
  'px': '1px',
  '0.5': '0.125rem',  // 2px
  '1': '0.25rem',     // 4px
  '2': '0.5rem',      // 8px
  '3': '0.75rem',     // 12px
  '4': '1rem',        // 16px
  '5': '1.25rem',     // 20px
  '6': '1.5rem',      // 24px
  '8': '2rem',        // 32px
  '10': '2.5rem',     // 40px
  '12': '3rem',       // 48px
  '16': '4rem',       // 64px
}
```

---

## 3. Glassmorphism Core Styles

### 3.1 Glass Effect Formula

```css
/* === CORE GLASS EFFECT === */
.glass {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(12px) saturate(180%);
  -webkit-backdrop-filter: blur(12px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* Glass variants */
.glass-light {
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(16px) saturate(180%);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.glass-heavy {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(24px) saturate(200%);
  border: 1px solid rgba(255, 255, 255, 0.12);
}

/* Inner glow (macOS effect) */
.glass-glow {
  box-shadow: 
    inset 0 1px 1px rgba(255, 255, 255, 0.05),
    0 0 40px rgba(59, 130, 246, 0.1);
}
```

### 3.2 TailwindCSS Custom Classes (tailwind.config.ts)

```typescript
// Add to tailwind.config.ts
module.exports = {
  theme: {
    extend: {
      // Glass backgrounds
      backgroundColor: {
        'glass': 'rgba(255, 255, 255, 0.03)',
        'glass-light': 'rgba(255, 255, 255, 0.05)',
        'glass-heavy': 'rgba(255, 255, 255, 0.08)',
      },
      // Glass borders
      borderColor: {
        'glass': 'rgba(255, 255, 255, 0.08)',
        'glass-light': 'rgba(255, 255, 255, 0.12)',
      },
      // Backdrop blur
      backdropBlur: {
        'xs': '2px',
        'glass': '12px',
        'glass-heavy': '24px',
      },
      // Box shadows
      boxShadow: {
        'glass': 'inset 0 1px 1px rgba(255,255,255,0.05)',
        'glass-glow': '0 0 40px rgba(59, 130, 246, 0.15)',
        'glass-lg': '0 8px 32px rgba(0, 0, 0, 0.4)',
      },
    },
  },
  plugins: [],
}
```

### 3.3 Reusable Glass Classes

```tsx
// utils/cn.ts (classnames utility)
import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs));
}

// styles/glass.ts
export const glassStyles = {
  base: 'bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08]',
  light: 'bg-white/[0.05] backdrop-blur-[16px] border border-white/[0.1]',
  heavy: 'bg-white/[0.08] backdrop-blur-[24px] border border-white/[0.12]',
  card: 'bg-white/[0.03] backdrop-blur-[12px] border border-white/[0.08] rounded-xl shadow-glass-lg',
  hover: 'hover:bg-white/[0.06] hover:border-white/[0.12] transition-all duration-200',
};
```

### 3.4 Icon Libraries Setup

#### FontAwesome (React)

```bash
# Install FontAwesome
npm install @fortawesome/fontawesome-svg-core \
            @fortawesome/free-solid-svg-icons \
            @fortawesome/free-regular-svg-icons \
            @fortawesome/free-brands-svg-icons \
            @fortawesome/react-fontawesome
```

```tsx
// lib/fontawesome.ts - Setup file (import once in _app.tsx hoặc layout.tsx)
import { library } from '@fortawesome/fontawesome-svg-core';
import { config } from '@fortawesome/fontawesome-svg-core';
import '@fortawesome/fontawesome-svg-core/styles.css';

// Prevent FA from adding CSS (we import it manually)
config.autoAddCss = false;

// Import icons bạn cần (tree-shaking friendly)
import { 
  faHome, faUser, faCog, faBell, faSearch,
  faChevronDown, faCheck, faTimes, faPlus,
  faEdit, faTrash, faSave, faSpinner
} from '@fortawesome/free-solid-svg-icons';

import { 
  faHeart as faHeartRegular,
  faBell as faBellRegular 
} from '@fortawesome/free-regular-svg-icons';

import { 
  faGithub, faTwitter, faFacebook, faLinkedin 
} from '@fortawesome/free-brands-svg-icons';

// Add to library (global access)
library.add(
  faHome, faUser, faCog, faBell, faSearch,
  faChevronDown, faCheck, faTimes, faPlus,
  faEdit, faTrash, faSave, faSpinner,
  faHeartRegular, faBellRegular,
  faGithub, faTwitter, faFacebook, faLinkedin
);
```

```tsx
// Usage in components
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHome, faUser, faSpinner } from '@fortawesome/free-solid-svg-icons';
import { faGithub } from '@fortawesome/free-brands-svg-icons';

// Basic usage
<FontAwesomeIcon icon={faHome} className="h-5 w-5 text-white/70" />

// With sizing (use className for consistency with Tailwind)
<FontAwesomeIcon icon={faUser} className="h-4 w-4" />
<FontAwesomeIcon icon={faUser} className="h-6 w-6" />

// Spinning (for loading states)
<FontAwesomeIcon icon={faSpinner} className="h-5 w-5 animate-spin" />

// Brand icons
<FontAwesomeIcon icon={faGithub} className="h-5 w-5" />

// In buttons (glassmorphism style)
<button className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/[0.05] text-white/80 hover:bg-white/[0.08] transition-colors">
  <FontAwesomeIcon icon={faHome} className="h-4 w-4" />
  <span>Trang chủ</span>
</button>
```

#### Lucide React (Alternative - Recommended)

```bash
npm install lucide-react
```

```tsx
// Lucide có tree-shaking tốt hơn và icons nhẹ hơn
import { Home, User, Settings, Bell, Search, ChevronDown } from 'lucide-react';

// Usage - same as any React component
<Home className="h-5 w-5 text-white/70" />
<User className="h-4 w-4" />
<Settings className="h-6 w-6 text-primary-400" />
```

#### Icon Component Wrapper (cho cả 2 libraries)

```tsx
// components/ui/Icon.tsx
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { type IconDefinition } from '@fortawesome/fontawesome-svg-core';
import { type LucideIcon } from 'lucide-react';
import { cn } from '@/utils/cn';

type IconProps = {
  className?: string;
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
} & (
  | { fa: IconDefinition; lucide?: never }
  | { lucide: LucideIcon; fa?: never }
);

const sizeClasses = {
  xs: 'h-3 w-3',
  sm: 'h-4 w-4',
  md: 'h-5 w-5',
  lg: 'h-6 w-6',
  xl: 'h-8 w-8',
};

export function Icon({ fa, lucide, size = 'md', className }: IconProps) {
  const sizeClass = sizeClasses[size];
  
  if (fa) {
    return <FontAwesomeIcon icon={fa} className={cn(sizeClass, className)} />;
  }
  
  if (lucide) {
    const LucideIcon = lucide;
    return <LucideIcon className={cn(sizeClass, className)} />;
  }
  
  return null;
}

// Usage
import { faHome } from '@fortawesome/free-solid-svg-icons';
import { Settings } from 'lucide-react';

<Icon fa={faHome} size="md" className="text-white/70" />
<Icon lucide={Settings} size="lg" className="text-primary-400" />
```

---

## 4. Component Library

### 4.1 Button Components

```tsx
// components/ui/Button.tsx
import { forwardRef, type ButtonHTMLAttributes } from 'react';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/utils/cn';

const buttonVariants = cva(
  // Base styles
  `inline-flex items-center justify-center gap-2 
   font-medium transition-all duration-200 
   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/50
   disabled:opacity-50 disabled:cursor-not-allowed
   active:scale-[0.98]`,
  {
    variants: {
      variant: {
        // Primary - Gradient glass
        primary: `
          bg-gradient-to-r from-primary-500 to-primary-600
          text-white shadow-lg shadow-primary-500/25
          hover:from-primary-400 hover:to-primary-500
          hover:shadow-primary-500/40
        `,
        // Secondary - Glass effect
        secondary: `
          bg-white/[0.05] backdrop-blur-[12px]
          border border-white/[0.1]
          text-white/90
          hover:bg-white/[0.08] hover:border-white/[0.15]
        `,
        // Ghost - Minimal
        ghost: `
          bg-transparent text-white/70
          hover:bg-white/[0.05] hover:text-white
        `,
        // Danger
        danger: `
          bg-red-500/80 text-white
          hover:bg-red-500
          shadow-lg shadow-red-500/25
        `,
        // Outline
        outline: `
          bg-transparent border border-white/[0.15]
          text-white/80
          hover:bg-white/[0.05] hover:border-white/[0.25]
        `,
      },
      size: {
        sm: 'h-8 px-3 text-sm rounded-md',
        md: 'h-10 px-4 text-sm rounded-lg',
        lg: 'h-12 px-6 text-base rounded-lg',
        xl: 'h-14 px-8 text-lg rounded-xl',
        icon: 'h-10 w-10 rounded-lg',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'md',
    },
  }
);

export interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  isLoading?: boolean;
}

export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, isLoading, children, disabled, ...props }, ref) => {
    return (
      <button
        ref={ref}
        className={cn(buttonVariants({ variant, size }), className)}
        disabled={disabled || isLoading}
        {...props}
      >
        {isLoading && (
          <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" fill="none" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
        )}
        {children}
      </button>
    );
  }
);
Button.displayName = 'Button';
```

### 4.2 Card Components

```tsx
// components/ui/Card.tsx
import { cn } from '@/utils/cn';
import { type HTMLAttributes, forwardRef } from 'react';

interface CardProps extends HTMLAttributes<HTMLDivElement> {
  variant?: 'glass' | 'solid' | 'outline';
  interactive?: boolean;
}

export const Card = forwardRef<HTMLDivElement, CardProps>(
  ({ className, variant = 'glass', interactive = false, ...props }, ref) => {
    const variants = {
      glass: `
        bg-white/[0.03] backdrop-blur-[12px] saturate-[180%]
        border border-white/[0.08]
        shadow-[inset_0_1px_1px_rgba(255,255,255,0.05)]
      `,
      solid: `
        bg-dark-800/80
        border border-white/[0.05]
      `,
      outline: `
        bg-transparent
        border border-white/[0.1]
      `,
    };

    return (
      <div
        ref={ref}
        className={cn(
          'rounded-xl p-6',
          variants[variant],
          interactive && 'hover:bg-white/[0.06] hover:border-white/[0.12] transition-all duration-200 cursor-pointer',
          className
        )}
        {...props}
      />
    );
  }
);
Card.displayName = 'Card';

// Sub-components
export const CardHeader = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => (
  <div className={cn('mb-4', className)} {...props} />
);

export const CardTitle = ({ className, ...props }: HTMLAttributes<HTMLHeadingElement>) => (
  <h3 className={cn('text-lg font-semibold text-white/95', className)} {...props} />
);

export const CardDescription = ({ className, ...props }: HTMLAttributes<HTMLParagraphElement>) => (
  <p className={cn('text-sm text-white/60 mt-1', className)} {...props} />
);

export const CardContent = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => (
  <div className={cn('', className)} {...props} />
);

export const CardFooter = ({ className, ...props }: HTMLAttributes<HTMLDivElement>) => (
  <div className={cn('mt-6 flex items-center gap-3', className)} {...props} />
);
```

### 4.3 Input Components

```tsx
// components/ui/Input.tsx
import { forwardRef, type InputHTMLAttributes } from 'react';
import { cn } from '@/utils/cn';

export interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  error?: string;
  leftIcon?: React.ReactNode;
  rightIcon?: React.ReactNode;
}

export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ className, error, leftIcon, rightIcon, ...props }, ref) => {
    return (
      <div className="relative">
        {leftIcon && (
          <div className="absolute left-3 top-1/2 -translate-y-1/2 text-white/40">
            {leftIcon}
          </div>
        )}
        <input
          ref={ref}
          className={cn(
            // Base
            `w-full h-11 px-4 rounded-lg
             bg-white/[0.03] backdrop-blur-[8px]
             border border-white/[0.08]
             text-white/90 placeholder:text-white/40
             text-sm`,
            // Focus
            `focus:outline-none focus:ring-2 focus:ring-primary-500/40
             focus:border-primary-500/50`,
            // Error
            error && 'border-red-500/50 focus:ring-red-500/40',
            // Icons
            leftIcon && 'pl-10',
            rightIcon && 'pr-10',
            // Transition
            'transition-all duration-200',
            className
          )}
          {...props}
        />
        {rightIcon && (
          <div className="absolute right-3 top-1/2 -translate-y-1/2 text-white/40">
            {rightIcon}
          </div>
        )}
        {error && (
          <p className="text-red-400 text-xs mt-1.5">{error}</p>
        )}
      </div>
    );
  }
);
Input.displayName = 'Input';
```

### 4.4 Dropdown với Radix UI

```tsx
// components/ui/Dropdown.tsx
'use client';

import * as DropdownMenuPrimitive from '@radix-ui/react-dropdown-menu';
import { forwardRef, type ComponentPropsWithoutRef } from 'react';
import { cn } from '@/utils/cn';
import { Check, ChevronRight } from 'lucide-react';

const DropdownMenu = DropdownMenuPrimitive.Root;
const DropdownMenuTrigger = DropdownMenuPrimitive.Trigger;
const DropdownMenuGroup = DropdownMenuPrimitive.Group;
const DropdownMenuPortal = DropdownMenuPrimitive.Portal;
const DropdownMenuSub = DropdownMenuPrimitive.Sub;
const DropdownMenuRadioGroup = DropdownMenuPrimitive.RadioGroup;

const DropdownMenuContent = forwardRef<
  React.ElementRef<typeof DropdownMenuPrimitive.Content>,
  ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Content>
>(({ className, sideOffset = 4, ...props }, ref) => (
  <DropdownMenuPrimitive.Portal>
    <DropdownMenuPrimitive.Content
      ref={ref}
      sideOffset={sideOffset}
      className={cn(
        // Glass effect
        `bg-dark-800/95 backdrop-blur-[20px] saturate-[180%]
         border border-white/[0.1]
         rounded-xl shadow-2xl shadow-black/50`,
        // Size & spacing
        'min-w-[180px] p-1.5',
        // Animation
        `data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95
         data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95
         data-[side=bottom]:slide-in-from-top-2 data-[side=top]:slide-in-from-bottom-2`,
        // Z-index
        'z-50',
        className
      )}
      {...props}
    />
  </DropdownMenuPrimitive.Portal>
));
DropdownMenuContent.displayName = 'DropdownMenuContent';

const DropdownMenuItem = forwardRef<
  React.ElementRef<typeof DropdownMenuPrimitive.Item>,
  ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Item> & { inset?: boolean }
>(({ className, inset, ...props }, ref) => (
  <DropdownMenuPrimitive.Item
    ref={ref}
    className={cn(
      // Base
      `relative flex items-center gap-2
       px-3 py-2.5 rounded-lg
       text-sm text-white/80
       cursor-pointer select-none outline-none`,
      // Hover/Focus
      `hover:bg-white/[0.06] focus:bg-white/[0.06]
       hover:text-white focus:text-white`,
      // Disabled
      'data-[disabled]:opacity-40 data-[disabled]:pointer-events-none',
      // Transition
      'transition-colors duration-150',
      inset && 'pl-8',
      className
    )}
    {...props}
  />
));
DropdownMenuItem.displayName = 'DropdownMenuItem';

const DropdownMenuSeparator = forwardRef<
  React.ElementRef<typeof DropdownMenuPrimitive.Separator>,
  ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Separator>
>(({ className, ...props }, ref) => (
  <DropdownMenuPrimitive.Separator
    ref={ref}
    className={cn('h-px my-1.5 bg-white/[0.08]', className)}
    {...props}
  />
));
DropdownMenuSeparator.displayName = 'DropdownMenuSeparator';

const DropdownMenuLabel = forwardRef<
  React.ElementRef<typeof DropdownMenuPrimitive.Label>,
  ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Label> & { inset?: boolean }
>(({ className, inset, ...props }, ref) => (
  <DropdownMenuPrimitive.Label
    ref={ref}
    className={cn(
      'px-3 py-2 text-xs font-medium text-white/50 uppercase tracking-wide',
      inset && 'pl-8',
      className
    )}
    {...props}
  />
));
DropdownMenuLabel.displayName = 'DropdownMenuLabel';

export {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
  DropdownMenuGroup,
  DropdownMenuPortal,
  DropdownMenuSub,
  DropdownMenuRadioGroup,
};
```

### 4.5 Modal/Dialog với Radix UI

```tsx
// components/ui/Dialog.tsx
'use client';

import * as DialogPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import { forwardRef, type ComponentPropsWithoutRef } from 'react';
import { cn } from '@/utils/cn';

const Dialog = DialogPrimitive.Root;
const DialogTrigger = DialogPrimitive.Trigger;
const DialogPortal = DialogPrimitive.Portal;
const DialogClose = DialogPrimitive.Close;

const DialogOverlay = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Overlay>,
  ComponentPropsWithoutRef<typeof DialogPrimitive.Overlay>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Overlay
    ref={ref}
    className={cn(
      'fixed inset-0 z-50 bg-black/60 backdrop-blur-sm',
      'data-[state=open]:animate-in data-[state=open]:fade-in-0',
      'data-[state=closed]:animate-out data-[state=closed]:fade-out-0',
      className
    )}
    {...props}
  />
));
DialogOverlay.displayName = 'DialogOverlay';

const DialogContent = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Content>,
  ComponentPropsWithoutRef<typeof DialogPrimitive.Content>
>(({ className, children, ...props }, ref) => (
  <DialogPortal>
    <DialogOverlay />
    <DialogPrimitive.Content
      ref={ref}
      className={cn(
        // Position
        'fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-50',
        // Glass effect
        `bg-dark-800/95 backdrop-blur-[24px] saturate-[180%]
         border border-white/[0.1]
         rounded-2xl shadow-2xl shadow-black/50`,
        // Size
        'w-full max-w-lg max-h-[90vh] overflow-y-auto',
        // Padding
        'p-6',
        // Animation
        `data-[state=open]:animate-in data-[state=open]:fade-in-0 data-[state=open]:zoom-in-95
         data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95`,
        className
      )}
      {...props}
    >
      {children}
      <DialogPrimitive.Close className="absolute right-4 top-4 p-1.5 rounded-lg text-white/50 hover:text-white hover:bg-white/[0.05] transition-colors">
        <X className="h-4 w-4" />
        <span className="sr-only">Close</span>
      </DialogPrimitive.Close>
    </DialogPrimitive.Content>
  </DialogPortal>
));
DialogContent.displayName = 'DialogContent';

const DialogHeader = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div className={cn('mb-4', className)} {...props} />
);

const DialogTitle = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Title>,
  ComponentPropsWithoutRef<typeof DialogPrimitive.Title>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Title
    ref={ref}
    className={cn('text-xl font-semibold text-white', className)}
    {...props}
  />
));
DialogTitle.displayName = 'DialogTitle';

const DialogDescription = forwardRef<
  React.ElementRef<typeof DialogPrimitive.Description>,
  ComponentPropsWithoutRef<typeof DialogPrimitive.Description>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Description
    ref={ref}
    className={cn('text-sm text-white/60 mt-1.5', className)}
    {...props}
  />
));
DialogDescription.displayName = 'DialogDescription';

const DialogFooter = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div className={cn('mt-6 flex justify-end gap-3', className)} {...props} />
);

export {
  Dialog,
  DialogTrigger,
  DialogPortal,
  DialogClose,
  DialogOverlay,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
};
```

---

## 5. Layout Components

### 5.1 Header/Navbar Component

```tsx
// components/layout/Header.tsx
'use client';

import { useState, useEffect } from 'react';
import { cn } from '@/utils/cn';
import { Menu, X, Search, Bell, User } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from '@/components/ui/Dropdown';

interface NavItem {
  label: string;
  href: string;
  active?: boolean;
}

interface HeaderProps {
  logo?: React.ReactNode;
  navItems?: NavItem[];
  user?: { name: string; avatar?: string } | null;
}

export function Header({ logo, navItems = [], user }: HeaderProps) {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  // Scroll detection for glass effect enhancement
  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <header
      className={cn(
        // Fixed positioning
        'fixed top-0 left-0 right-0 z-50',
        // Glass effect
        'bg-dark-900/80 backdrop-blur-[20px] saturate-[180%]',
        'border-b border-white/[0.05]',
        // Transition for scroll state
        'transition-all duration-300',
        isScrolled && 'bg-dark-900/95 shadow-lg shadow-black/20'
      )}
    >
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div className="flex-shrink-0">
            {logo || (
              <span className="text-xl font-bold bg-gradient-to-r from-primary-400 to-accent-purple bg-clip-text text-transparent">
                Logo
              </span>
            )}
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center gap-1">
            {navItems.map((item) => (
              <a
                key={item.href}
                href={item.href}
                className={cn(
                  'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                  item.active
                    ? 'bg-white/[0.08] text-white'
                    : 'text-white/70 hover:text-white hover:bg-white/[0.05]'
                )}
              >
                {item.label}
              </a>
            ))}
          </nav>

          {/* Right section */}
          <div className="flex items-center gap-2">
            {/* Search */}
            <Button variant="ghost" size="icon" className="hidden sm:flex">
              <Search className="h-5 w-5" />
            </Button>

            {/* Notifications */}
            <Button variant="ghost" size="icon" className="relative">
              <Bell className="h-5 w-5" />
              <span className="absolute top-1.5 right-1.5 h-2 w-2 bg-red-500 rounded-full" />
            </Button>

            {/* User dropdown */}
            {user ? (
              <DropdownMenu>
                <DropdownMenuTrigger asChild>
                  <Button variant="ghost" size="icon">
                    {user.avatar ? (
                      <img
                        src={user.avatar}
                        alt={user.name}
                        className="h-8 w-8 rounded-full object-cover ring-2 ring-white/[0.1]"
                      />
                    ) : (
                      <div className="h-8 w-8 rounded-full bg-gradient-to-br from-primary-500 to-accent-purple flex items-center justify-center text-white text-sm font-medium">
                        {user.name.charAt(0)}
                      </div>
                    )}
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                  <DropdownMenuLabel>Xin chào, {user.name}</DropdownMenuLabel>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem>Hồ sơ</DropdownMenuItem>
                  <DropdownMenuItem>Cài đặt</DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem className="text-red-400">
                    Đăng xuất
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            ) : (
              <Button variant="primary" size="sm">
                Đăng nhập
              </Button>
            )}

            {/* Mobile menu button */}
            <Button
              variant="ghost"
              size="icon"
              className="md:hidden"
              onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
            >
              {isMobileMenuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </Button>
          </div>
        </div>
      </div>

      {/* Mobile Navigation */}
      <div
        className={cn(
          'md:hidden overflow-hidden transition-all duration-300',
          isMobileMenuOpen ? 'max-h-96' : 'max-h-0'
        )}
      >
        <nav className="px-4 pb-4 space-y-1">
          {navItems.map((item) => (
            <a
              key={item.href}
              href={item.href}
              className={cn(
                'block px-4 py-3 rounded-lg text-sm font-medium transition-colors',
                item.active
                  ? 'bg-white/[0.08] text-white'
                  : 'text-white/70 hover:text-white hover:bg-white/[0.05]'
              )}
            >
              {item.label}
            </a>
          ))}
        </nav>
      </div>
    </header>
  );
}
```

### 5.2 Footer Component

```tsx
// components/layout/Footer.tsx
import { cn } from '@/utils/cn';

interface FooterLink {
  label: string;
  href: string;
}

interface FooterSection {
  title: string;
  links: FooterLink[];
}

interface FooterProps {
  logo?: React.ReactNode;
  sections?: FooterSection[];
  copyright?: string;
  socials?: { icon: React.ReactNode; href: string; label: string }[];
}

export function Footer({
  logo,
  sections = [],
  copyright = `© ${new Date().getFullYear()} Your Company. All rights reserved.`,
  socials = [],
}: FooterProps) {
  return (
    <footer className="relative mt-auto">
      {/* Top border gradient */}
      <div className="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-white/[0.1] to-transparent" />
      
      <div className="bg-dark-900/50 backdrop-blur-sm">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
          <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 lg:gap-12">
            {/* Logo & Description */}
            <div className="col-span-2 md:col-span-4 lg:col-span-2">
              {logo || (
                <span className="text-xl font-bold bg-gradient-to-r from-primary-400 to-accent-purple bg-clip-text text-transparent">
                  Logo
                </span>
              )}
              <p className="mt-4 text-white/50 text-sm max-w-sm">
                Mô tả ngắn về công ty hoặc sản phẩm của bạn. Giúp người dùng hiểu
                rõ hơn về những gì bạn cung cấp.
              </p>
              
              {/* Social links */}
              {socials.length > 0 && (
                <div className="flex items-center gap-3 mt-6">
                  {socials.map((social, idx) => (
                    <a
                      key={idx}
                      href={social.href}
                      aria-label={social.label}
                      className="p-2 rounded-lg bg-white/[0.03] border border-white/[0.05] text-white/60 hover:text-white hover:bg-white/[0.06] transition-all duration-200"
                    >
                      {social.icon}
                    </a>
                  ))}
                </div>
              )}
            </div>

            {/* Link sections */}
            {sections.map((section) => (
              <div key={section.title}>
                <h3 className="text-sm font-semibold text-white/90 uppercase tracking-wide">
                  {section.title}
                </h3>
                <ul className="mt-4 space-y-3">
                  {section.links.map((link) => (
                    <li key={link.href}>
                      <a
                        href={link.href}
                        className="text-sm text-white/50 hover:text-white transition-colors"
                      >
                        {link.label}
                      </a>
                    </li>
                  ))}
                </ul>
              </div>
            ))}
          </div>

          {/* Bottom bar */}
          <div className="mt-12 pt-8 border-t border-white/[0.05]">
            <p className="text-center text-xs text-white/40">
              {copyright}
            </p>
          </div>
        </div>
      </div>
    </footer>
  );
}
```

### 5.3 Sidebar Component

```tsx
// components/layout/Sidebar.tsx
'use client';

import { useState } from 'react';
import { cn } from '@/utils/cn';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface SidebarItem {
  icon: React.ReactNode;
  label: string;
  href: string;
  active?: boolean;
  badge?: string | number;
}

interface SidebarSection {
  title?: string;
  items: SidebarItem[];
}

interface SidebarProps {
  sections: SidebarSection[];
  collapsed?: boolean;
  onToggle?: (collapsed: boolean) => void;
}

export function Sidebar({ sections, collapsed = false, onToggle }: SidebarProps) {
  const [isCollapsed, setIsCollapsed] = useState(collapsed);

  const handleToggle = () => {
    const newState = !isCollapsed;
    setIsCollapsed(newState);
    onToggle?.(newState);
  };

  return (
    <aside
      className={cn(
        // Glass effect
        'bg-dark-900/50 backdrop-blur-[12px]',
        'border-r border-white/[0.05]',
        // Size
        'h-screen sticky top-0',
        'transition-all duration-300 ease-in-out',
        isCollapsed ? 'w-[72px]' : 'w-64'
      )}
    >
      {/* Toggle button */}
      <button
        onClick={handleToggle}
        className={cn(
          'absolute -right-3 top-6 z-10',
          'h-6 w-6 rounded-full',
          'bg-dark-700 border border-white/[0.1]',
          'flex items-center justify-center',
          'text-white/60 hover:text-white',
          'transition-colors'
        )}
      >
        {isCollapsed ? (
          <ChevronRight className="h-3 w-3" />
        ) : (
          <ChevronLeft className="h-3 w-3" />
        )}
      </button>

      {/* Content */}
      <div className="p-4 h-full overflow-y-auto">
        {sections.map((section, sectionIdx) => (
          <div key={sectionIdx} className={cn(sectionIdx > 0 && 'mt-6')}>
            {/* Section title */}
            {section.title && !isCollapsed && (
              <h3 className="px-3 mb-2 text-xs font-medium text-white/40 uppercase tracking-wide">
                {section.title}
              </h3>
            )}

            {/* Items */}
            <nav className="space-y-1">
              {section.items.map((item) => (
                <a
                  key={item.href}
                  href={item.href}
                  className={cn(
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg',
                    'transition-all duration-200',
                    item.active
                      ? 'bg-primary-500/20 text-primary-400'
                      : 'text-white/60 hover:text-white hover:bg-white/[0.05]',
                    isCollapsed && 'justify-center px-0'
                  )}
                  title={isCollapsed ? item.label : undefined}
                >
                  <span className="flex-shrink-0">{item.icon}</span>
                  
                  {!isCollapsed && (
                    <>
                      <span className="flex-1 text-sm font-medium truncate">
                        {item.label}
                      </span>
                      {item.badge && (
                        <span className="px-2 py-0.5 text-xs font-medium bg-primary-500/20 text-primary-400 rounded-full">
                          {item.badge}
                        </span>
                      )}
                    </>
                  )}
                </a>
              ))}
            </nav>
          </div>
        ))}
      </div>
    </aside>
  );
}
```

---

## 6. Responsive Design System

### 6.1 Breakpoints

```typescript
// TailwindCSS default breakpoints
const breakpoints = {
  'sm': '640px',   // Mobile landscape
  'md': '768px',   // Tablet portrait
  'lg': '1024px',  // Tablet landscape / Small laptop
  'xl': '1280px',  // Desktop
  '2xl': '1536px', // Large desktop
};
```

### 6.2 Mobile-First Patterns

```tsx
// Ví dụ: Card Grid responsive
<div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
  {items.map(item => <Card key={item.id} {...item} />)}
</div>

// Ví dụ: Typography responsive
<h1 className="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-bold">
  Tiêu đề chính
</h1>

// Ví dụ: Spacing responsive
<section className="px-4 py-8 sm:px-6 sm:py-12 lg:px-8 lg:py-16">
  {/* Content */}
</section>

// Ví dụ: Hide/Show elements
<nav className="hidden md:flex">Desktop Nav</nav>
<button className="md:hidden">Mobile Menu</button>
```

### 6.3 Container Component

```tsx
// components/layout/Container.tsx
import { cn } from '@/utils/cn';

interface ContainerProps extends React.HTMLAttributes<HTMLDivElement> {
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full';
}

export function Container({ 
  className, 
  size = 'lg', 
  children, 
  ...props 
}: ContainerProps) {
  const sizes = {
    sm: 'max-w-2xl',
    md: 'max-w-4xl',
    lg: 'max-w-6xl',
    xl: 'max-w-7xl',
    full: 'max-w-full',
  };

  return (
    <div
      className={cn(
        'w-full mx-auto px-4 sm:px-6 lg:px-8',
        sizes[size],
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
}
```

---

## 7. Animation với Framer Motion

### 7.1 Animation Presets

```tsx
// utils/animations.ts
import { type Variants } from 'framer-motion';

export const fadeIn: Variants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { duration: 0.3 } },
};

export const slideUp: Variants = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.4, ease: 'easeOut' } },
};

export const slideDown: Variants = {
  hidden: { opacity: 0, y: -20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.4, ease: 'easeOut' } },
};

export const scaleIn: Variants = {
  hidden: { opacity: 0, scale: 0.95 },
  visible: { opacity: 1, scale: 1, transition: { duration: 0.2, ease: 'easeOut' } },
};

export const staggerContainer: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.1,
    },
  },
};

// Hover animations
export const hoverScale = {
  scale: 1.02,
  transition: { duration: 0.2 },
};

export const tapScale = {
  scale: 0.98,
};
```

### 7.2 Animated Components

```tsx
// components/motion/AnimatedCard.tsx
'use client';

import { motion } from 'framer-motion';
import { slideUp } from '@/utils/animations';
import { Card, type CardProps } from '@/components/ui/Card';

export function AnimatedCard({ children, ...props }: CardProps) {
  return (
    <motion.div
      variants={slideUp}
      initial="hidden"
      animate="visible"
      whileHover={{ y: -4, transition: { duration: 0.2 } }}
    >
      <Card {...props}>{children}</Card>
    </motion.div>
  );
}

// Staggered list example
export function AnimatedList({ children }: { children: React.ReactNode }) {
  return (
    <motion.div
      variants={staggerContainer}
      initial="hidden"
      animate="visible"
      className="space-y-4"
    >
      {children}
    </motion.div>
  );
}
```

---

## 8. Accessibility (A11y)

### 8.1 ARIA Guidelines

```tsx
// LUÔN đảm bảo các yếu tố:
// 1. Focus visible: Mọi interactive element phải có focus ring
// 2. Semantic HTML: Dùng đúng tag (button, a, nav, main, aside, etc.)
// 3. Labels: Mọi input phải có label
// 4. Alt text: Mọi image phải có alt
// 5. Color contrast: Text phải có contrast ratio >= 4.5:1

// Ví dụ: Accessible Button
<button
  className="focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:outline-none"
  aria-label="Close dialog"
  aria-pressed={isPressed}
>
  <X className="h-5 w-5" />
</button>

// Ví dụ: Screen reader only text
<span className="sr-only">Thông báo mới</span>

// Ví dụ: Skip link
<a
  href="#main-content"
  className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary-500 focus:text-white focus:rounded-lg"
>
  Skip to main content
</a>
```

### 8.2 Keyboard Navigation

```tsx
// Sử dụng Radix UI đã có sẵn keyboard navigation chuẩn
// Dropdown: Arrow Up/Down để navigate, Enter để select, Escape để close
// Dialog: Tab để focus trap, Escape để close
// Focus trapping tự động cho Modal
```

---

## 9. Performance Guidelines

### 9.1 React Optimization

```tsx
// 1. Sử dụng React.memo cho pure components
const Card = React.memo(function Card({ title, description }) {
  return (
    <div className="card">
      <h3>{title}</h3>
      <p>{description}</p>
    </div>
  );
});

// 2. useMemo cho expensive calculations
const filteredItems = useMemo(() => {
  return items.filter(item => item.name.includes(search));
}, [items, search]);

// 3. useCallback cho event handlers passed to children
const handleClick = useCallback((id: string) => {
  setSelectedId(id);
}, []);

// 4. Lazy loading components
const HeavyComponent = lazy(() => import('./HeavyComponent'));

// 5. Virtualization cho long lists (use react-window hoặc @tanstack/virtual)
import { useVirtualizer } from '@tanstack/react-virtual';
```

### 9.2 CSS Performance

```tsx
// 1. Tránh backdrop-filter trên quá nhiều elements chồng nhau
// 2. Sử dụng will-change cho animations (nhưng đừng lạm dụng)
// 3. Prefer transform và opacity cho animations (GPU accelerated)
// 4. Tránh animating layout properties (width, height, top, left)

// Tốt:
<div className="transform hover:-translate-y-1 transition-transform" />

// Tránh:
<div className="hover:mt-[-4px] transition-all" /> // Triggers layout
```

---

## 10. Critical Design Protocol

### 10.1 Quy trình Tư duy Thiết kế

**BẮT BUỘC:** Trước khi code bất kỳ component nào, thực hiện các bước sau:

#### Bước 1: Suy luận Thiết kế (Design Reasoning)
1. **"Thiết kế như vậy có hợp lý không?"**
   - Modal hay inline form?
   - Dropdown hay radio buttons?
   - Nút đặt ở đâu thuận tay người dùng?

2. **"Component này có đúng mục đích không?"**
   - Dropdown > 10 items? → Cần có Search
   - Chỉ 2-3 options? → Radio buttons thay vì dropdown

#### Bước 2: Pre-flight Check
- **Z-index conflict:** Component có đè lên Header không?
- **Interaction overlap:** Modal mở → Dropdown bên dưới có bị lòi?
- **Data overflow:** Text dài quá → UI có vỡ không?
- **Mobile UX:** Bàn phím ảo có che nút quan trọng không?

#### Bước 3: Giải quyết & Sáng tạo
- Thêm `z-50`, `truncate`, `overflow-y-auto` để handle edge cases
- Thêm glass glow effect, subtle animations cho Premium feel

#### Bước 4: Self-Review
- Nhìn lại code, tìm 1 lý do có thể cause bug
- Fix preventively

---

## 11. Complete Example: Dashboard Layout

```tsx
// app/dashboard/layout.tsx
'use client';

import { Header } from '@/components/layout/Header';
import { Sidebar } from '@/components/layout/Sidebar';
import { Footer } from '@/components/layout/Footer';
import { 
  Home, Users, Settings, BarChart3, 
  MessageSquare, FileText, Bell 
} from 'lucide-react';

const navItems = [
  { label: 'Trang chủ', href: '/dashboard', active: true },
  { label: 'Analytics', href: '/dashboard/analytics' },
  { label: 'Users', href: '/dashboard/users' },
  { label: 'Settings', href: '/dashboard/settings' },
];

const sidebarSections = [
  {
    title: 'Menu',
    items: [
      { icon: <Home className="h-5 w-5" />, label: 'Dashboard', href: '/dashboard', active: true },
      { icon: <BarChart3 className="h-5 w-5" />, label: 'Analytics', href: '/analytics' },
      { icon: <Users className="h-5 w-5" />, label: 'Users', href: '/users', badge: 12 },
      { icon: <MessageSquare className="h-5 w-5" />, label: 'Messages', href: '/messages' },
      { icon: <FileText className="h-5 w-5" />, label: 'Reports', href: '/reports' },
    ],
  },
  {
    title: 'System',
    items: [
      { icon: <Bell className="h-5 w-5" />, label: 'Notifications', href: '/notifications' },
      { icon: <Settings className="h-5 w-5" />, label: 'Settings', href: '/settings' },
    ],
  },
];

export default function DashboardLayout({ 
  children 
}: { 
  children: React.ReactNode 
}) {
  return (
    <div className="min-h-screen bg-dark-900 text-white">
      {/* Background gradient */}
      <div className="fixed inset-0 bg-gradient-to-br from-primary-900/20 via-dark-900 to-accent-purple/10 -z-10" />
      
      {/* Header */}
      <Header 
        navItems={navItems}
        user={{ name: 'Nguyen Van A' }}
      />
      
      <div className="flex pt-16">
        {/* Sidebar */}
        <Sidebar sections={sidebarSections} />
        
        {/* Main content */}
        <main id="main-content" className="flex-1 p-4 md:p-6 lg:p-8">
          {children}
        </main>
      </div>
      
      {/* Footer (optional for dashboard) */}
      <Footer 
        copyright="© 2024 Dashboard. All rights reserved."
      />
    </div>
  );
}
```

---

## 12. Quick Reference Card

### Glassmorphism Classes
```
bg-white/[0.03]         → Glass background
backdrop-blur-[12px]    → Blur effect
border-white/[0.08]     → Glass border
rounded-xl              → macOS-like corners
shadow-glass-lg         → Depth shadow
```

### Button Sizes
```
h-8 px-3 text-sm rounded-md    → Small
h-10 px-4 text-sm rounded-lg   → Medium (default)
h-12 px-6 text-base rounded-lg → Large
h-14 px-8 text-lg rounded-xl   → Extra Large
```

### Focus States (Accessibility)
```
focus-visible:outline-none
focus-visible:ring-2
focus-visible:ring-primary-500/50
```

### Responsive Patterns
```
grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4
text-sm md:text-base lg:text-lg
px-4 sm:px-6 lg:px-8
hidden md:flex (show on desktop)
md:hidden (show on mobile only)
```

---

> **Lưu ý cuối:** Skill này được thiết kế để tạo ra giao diện Premium, lấy cảm hứng từ macOS. Luôn tuân thủ Design Tokens, sử dụng consistent spacing, và đảm bảo Accessibility. Khi có thắc mắc về thiết kế, hãy tự hỏi: "macOS làm như thế nào?"
