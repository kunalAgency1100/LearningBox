# LearningBox UI & Design Guidelines for AI Agents

This README provides a comprehensive overview of the **LearningBox** web application's UI architecture, color scheme, typography, and layout patterns. It is designed to help AI agents understand the existing project structure and maintain design consistency across all pages.

## Frameworks & Technologies

*   **HTML Structure:** Semantic HTML5.
*   **CSS Framework:** Tailwind CSS (via CDN: `https://cdn.tailwindcss.com?plugins=forms,typography`).
*   **Custom Styling:** Standard `<style>` blocks for custom CSS properties (like text-shadows, intricate gradients, and media queries like `.mobile-img` vs `.desktop-img`).
*   **Interactivity:** Vanilla JavaScript handles simple logic (e.g., scroll-based navbar transitions and mobile menu toggling).

## Color Palette

The project uses a custom Tailwind configuration injected directly in the HTML `<head>`.

```javascript
colors: {
  primary: "#F4BD18",          // Brand Yellow
  "primary-hover": "#FF731E",  // Brand Orange (used for buttons and highlights)
  "background-light": "#F3F4F6",// Light mode primary background
  "background-dark": "#000976", // Dark mode / Brand Navy background
  "text-light": "#1F2937",     // Default text color for light mode
  "text-dark": "#F9FAFB",      // Default text color for dark mode
  "accent-blue": "#26AADC",    // Brand Blue
  "accent-orange": "#FF731E",  // Brand Orange
  "brand-navy": "#000976",     // Brand Navy
}
```

### Gradients and Glows
There is a heavy reliance on gradients to add depth:
*   **Buttons:** Frequently employ `bg-primary` but occasionally use `linear-gradient(135deg, #F4BD18, #FF731E)` for a punchy effect.
*   **Text Excerpts:** Key phrases use `-webkit-background-clip: text` with linear gradients (e.g., `#26AADC` to `#000976` or `#F4BD18` to `#FF731E`).
*   **Ambient Background Glows:** Circular `radial-gradient` backgrounds with low opacity (`transparent 70%`) are used behind main content blocks to create floating, ambient color accents.

## Typography

*   **Display / Headings Font:** `Montserrat` (Weights: 600, 700, 800, 900). Applied via tailwind class `font-display`.
*   **Body / Sans Font:** System UI (`sans-serif`).
*   **Iconography:** Google's `Material Symbols Outlined`.

## Layout & Components

### 1. Navigation (Navbar)
*   **Positioning:** Fixed to the top left (`fixed top-0 left-0 w-full z-50` or `z-20`).
*   **Style:** Glassmorphism (`bg-white/5 backdrop-blur-sm border-b border-white/10`).
*   **Dynamic Behavior:** Vanilla JS listens to scrolls and adjusts the background color towards a solid `rgba(0, 9, 118, opacity)` to prevent contrast issues when scrolling past the hero section.
*   **Mobile:** Hidden links on mobile, toggled via a hamburger menu which opens a slide-out sidemenu (`#mobile-nav`) from the right side with a modal overlay background (`#mobile-overlay`).

### 2. Hero Sections
*   **Structure:** Uses `min-h-screen` (or `min-h-[60vh]` for secondary pages like Manager) with relative positioning.
*   **Background:** An absolute positioned background image (`object-cover object-center`) with a dark overlay gradient (`hero-overlay` class) on top to ensure text remains perfectly readable.
*   **Heading:** Large padding, flexbox centering, with bold display fonts (`6xl` to `7xl`) and subtle dropshadows (`drop-shadow-xl`).

### 3. Buttons (Call to Actions)
*   **Styling:** Brand yellow/orange buttons with dark text, rounded corners (`rounded-lg` or `rounded-full`), and drop shadows (`shadow-lg`).
*   **Interactions:** Hover states often employ translate effects (`hover:-translate-y-0.5` or `-y-1`), transition attributes, and sometimes inner icon animations (e.g., `group-hover:animate-pulse`, `group-hover:rotate-12`).

### 4. Info Cards & Feature Blocks
*   **Card Styling:** White or lightly tinted background, surrounded by a faint border picking up the accent colors (e.g., `border-[color]/30`).
*   **Hover Styles:** Subtle lift effect via `hover:-translate-y-1 transition-transform duration-300`.
*   **Icons:** Usually housed within a colored square/circle with an accompanying gradient background to match the product identity.

### 5. Badges/Chips
*   Used to flag tags like "For Leaders" or "Educate, Empower and Transform."
*   Uses `bg-white/20 backdrop-blur-lg border border-white/30 rounded-full px-5 py-2 text-sm uppercase font-semibold`.

## Development Rules for AI

1.  **Tailwind Utility Classes:** Always rely on standard Tailwind classes coupled with the custom theme variables configured in the `<head>`.
2.  **Colors:** When writing styles, do not use random hex codes. Stick to `text-primary`, `bg-brand-navy`, `text-accent-blue`, and the default Tailwind gray scale.
3.  **UI Effects:** Apply glassmorphism effectively (`bg-white/10 backdrop-blur-md`) for modern depth on overlays and cards. Ensure ambient glows use absolute positioning within an `overflow-hidden` container so it doesn't break page scroll width.
4.  **Responsive Design:** Use standard `md:`, `lg:` prefixes. Keep padding generous (`px-4 sm:px-6 lg:px-8`) matching typical `max-w-7xl` centered container layouts.
