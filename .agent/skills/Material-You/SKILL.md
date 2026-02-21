---
name: gemini-material-ui-coder
description: Generates UI code strictly adhering to the Modern Material Design 3 (Google Gemini) aesthetic. Use when you need to build flat, clean, and friendly interfaces without shadows or gradients.
---

# Gemini Material UI Coder

Detailed instructions for the agent to generate UI components using Tailwind CSS that match the Google Gemini / Material Design 3 visual style.

## When to use this skill

- Use this when generating, updating, or refactoring Frontend UI components (HTML, React, Vue, Svelte, etc.) using Tailwind CSS.
- This is helpful for creating modern, flat, and clean interfaces that require solid colors, large border-radii, and generous whitespace.
- Use this when the user specifically requests a design with "no blur", "no shadows", "no gradients", or explicitly asks for the "Gemini style".

## How to use it

When writing or modifying UI code, you MUST strictly follow these step-by-step conventions and patterns:

### 1. Core Visual Constraints (CRITICAL)
- **NO BLUR & NO SHADOWS:** NEVER use drop shadows (`shadow-*`, `box-shadow`) or glassmorphism/backdrop blur (`backdrop-blur`). To separate overlapping layers, use a subtle 1px border (`border border-gray-200`) or contrasting solid background colors (Tonal Elevation).
- **NO GRADIENTS:** NEVER use linear or radial gradients. Use ONLY solid background colors (`bg-white`, `bg-gray-50`, `bg-blue-100`).
- **COLOR PALETTE:** Use solid, light colors for app backgrounds (`bg-gray-50`, `bg-white`). Use high-contrast solid colors for text (`text-gray-900`, `text-gray-600`). Avoid pure black (`#000000`).

### 2. Component Patterns
- **Buttons & Chips:** - Shape: MUST be pill-shaped (`rounded-full`). 
  - Style: Use solid colors (e.g., `bg-blue-600 text-white` for primary, `bg-blue-50 text-blue-700` for secondary). 
  - Interaction: Hover states should only change background brightness (`hover:bg-blue-700`), NEVER add elevation or shadows.
- **Modals & Dialogs:** - Shape: Large rounded corners (`rounded-2xl` or `rounded-3xl`). 
  - Backdrop: MUST be a flat, semi-transparent dark color (e.g., `bg-black/50` or `bg-gray-900/40`) with absolutely NO blur. 
  - Separation: Use a subtle border on the modal box instead of a shadow.
- **Inputs & Chatboxes:** - Shape: Single-line inputs are `rounded-full`. Multi-line textareas (like a chat input) are `rounded-2xl` or `rounded-3xl`. 
  - Style: Use solid, light tonal backgrounds (`bg-gray-100`). 
  - Interaction: Use solid colored rings for focus states (`focus:ring-2 focus:ring-blue-500`). NO inset shadows (`shadow-inner`).
- **Cards & Containers:** - Shape: Use `rounded-2xl` or `rounded-3xl`. 
  - Separation: Separate from the background using a 1px solid border (`border-gray-200`) OR a different solid background color. 
  - Hierarchy: Inner elements must have proportionately smaller border-radii than their parent containers.

### 3. Spacing & Typography
- **Whitespace:** Use generous padding (`p-4`, `p-6`) and gaps (`gap-4`, `gap-6`) to let the UI breathe.
- **Typography:** Use clean, sans-serif fonts. Differentiate text hierarchy logically using font weights (`font-medium` for buttons/headings, `font-normal` for body text).

### 4. Output Format
- Write clean, semantic HTML/JSX.
- Apply styling EXCLUSIVELY via Tailwind CSS utility classes.
- Ensure responsive design (mobile-first approach).
- Output functional code directly. Do not include markdown explanations or thought processes unless explicitly requested.