# Design tokens

Use these when the project has no tokens of its own. Name tokens by role (`--color-surface`, `--color-fg-muted`), not by value, so dark mode and rebranding only change values.

## Colour

A neutral scale carries almost the whole interface. Change the accent to the brand colour, but keep it a single hue and check its contrast (white text on `accent` needs 4.5:1).

| Role | Light | Dark | Use |
| --- | --- | --- | --- |
| `bg` | `#f8fafc` | `#0b0f17` | Page background behind content |
| `surface` | `#ffffff` | `#111827` | Cards, tables, sidebar, inputs |
| `surface-muted` | `#f1f5f9` | `#1a2232` | Table headers, hover rows, subtle fills |
| `border` | `#e2e8f0` | `#263045` | Dividers, card and input borders |
| `border-strong` | `#cbd5e1` | `#36425a` | Input borders on hover, separators that must show |
| `fg` | `#0f172a` | `#e5e9f0` | Primary text |
| `fg-muted` | `#475569` | `#a3adbf` | Secondary text, labels, table headers |
| `fg-subtle` | `#64748b` | `#8590a6` | Hints, placeholders, timestamps |
| `accent` | `#4f46e5` | `#818cf8` | Primary buttons, active nav, links, focus ring |
| `accent-hover` | `#4338ca` | `#a5b4fc` | Hover of the above |
| `accent-soft` | `#eef2ff` | `#1e1b4b` | Active nav background, selected rows |
| `on-accent` | `#ffffff` | `#0b0f17` | Text on `accent` |
| `success` / `-soft` | `#15803d` / `#dcfce7` | `#4ade80` / `#052e16` | Completed, paid, healthy |
| `warning` / `-soft` | `#b45309` / `#fef3c7` | `#fbbf24` / `#422006` | Pending, expiring, degraded |
| `danger` / `-soft` | `#b91c1c` / `#fee2e2` | `#f87171` / `#450a0a` | Failed, overdue, destructive |
| `info` / `-soft` | `#0369a1` / `#e0f2fe` | `#38bdf8` / `#082f49` | Neutral notices, in progress |

Status badges use the `-soft` background with the strong colour as text, plus an icon or word: colour is never the only signal.

For charts, use the accent for the primary series and the neutral scale for comparison series; for categories, pick at most 6 distinct hues with similar lightness.

## Typography

One sans-serif family (the system stack, Inter, or the brand font) with tabular figures for numbers: `font-variant-numeric: tabular-nums`.

| Token | Size / line height | Weight | Use |
| --- | --- | --- | --- |
| `text-xs` | 12 / 16 | 500 | Badges, table meta, captions |
| `text-sm` | 14 / 20 | 400 | **Default for admin UI**: tables, forms, nav |
| `text-base` | 16 / 24 | 400 | Long-form text, empty-state copy |
| `text-lg` | 18 / 28 | 600 | Card and section titles |
| `text-xl` | 20 / 28 | 600 | Page titles in dense panels |
| `text-2xl` | 24 / 32 | 600 | Page titles |
| `text-3xl` | 30 / 36 | 600 | Stat card values |

Use weights 400, 500 and 600 only. Hierarchy comes from size, weight and the `fg`/`fg-muted` contrast, not from colour.

## Spacing, sizing and radius

- 4px base grid: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64. Nothing in between.
- Controls (buttons, inputs, selects): 36px tall by default, 32px in compact tables, 40px for touch-first screens.
- Table rows: 44px comfortable, 36px compact. Card padding 20–24px. Gap between page sections 24–32px.
- Radius: `sm` 4px (badges, checkboxes), `md` 6px (buttons, inputs), `lg` 8px (cards, menus), `xl` 12px (modals). Pick one scale and use it everywhere.

## Elevation and layers

- Prefer borders to shadows on flat surfaces. Shadows are for things that float.
- `shadow-sm` for cards that need lift, `shadow-md` for dropdowns and popovers, `shadow-lg` for modals and slide-overs.
- z-index scale: sticky headers 10, sidebar 20, dropdowns 30, overlays 40, modals 50, toasts 60.

## Motion

- 150ms for hover and colour changes, 200ms for menus and popovers, 250ms for modals and drawers; `ease-out` on enter, `ease-in` on exit.
- Animate opacity and transform only. Respect `prefers-reduced-motion: reduce` by removing movement.

## Tailwind CSS v4

Put this in the main stylesheet (for example `resources/css/app.css`). Utilities such as `bg-surface`, `text-fg-muted`, `border-border` and `bg-accent` then work everywhere.

```css
@import "tailwindcss";

@custom-variant dark (&:where(.dark, .dark *));

@theme {
  --font-sans: "Inter", ui-sans-serif, system-ui, sans-serif;

  --color-bg: #f8fafc;
  --color-surface: #ffffff;
  --color-surface-muted: #f1f5f9;
  --color-border: #e2e8f0;
  --color-border-strong: #cbd5e1;
  --color-fg: #0f172a;
  --color-fg-muted: #475569;
  --color-fg-subtle: #64748b;
  --color-accent: #4f46e5;
  --color-accent-hover: #4338ca;
  --color-accent-soft: #eef2ff;
  --color-on-accent: #ffffff;
  --color-success: #15803d;
  --color-success-soft: #dcfce7;
  --color-warning: #b45309;
  --color-warning-soft: #fef3c7;
  --color-danger: #b91c1c;
  --color-danger-soft: #fee2e2;
  --color-info: #0369a1;
  --color-info-soft: #e0f2fe;

  --radius-sm: 4px;
  --radius-md: 6px;
  --radius-lg: 8px;
  --radius-xl: 12px;
}

@layer base {
  .dark {
    --color-bg: #0b0f17;
    --color-surface: #111827;
    --color-surface-muted: #1a2232;
    --color-border: #263045;
    --color-border-strong: #36425a;
    --color-fg: #e5e9f0;
    --color-fg-muted: #a3adbf;
    --color-fg-subtle: #8590a6;
    --color-accent: #818cf8;
    --color-accent-hover: #a5b4fc;
    --color-accent-soft: #1e1b4b;
    --color-on-accent: #0b0f17;
    --color-success: #4ade80;
    --color-success-soft: #052e16;
    --color-warning: #fbbf24;
    --color-warning-soft: #422006;
    --color-danger: #f87171;
    --color-danger-soft: #450a0a;
    --color-info: #38bdf8;
    --color-info-soft: #082f49;
  }

  body {
    @apply bg-bg text-fg text-sm antialiased;
    font-variant-numeric: tabular-nums;
  }
}
```

Toggle dark mode by setting the `dark` class on `<html>`: follow `prefers-color-scheme` by default, let the user override it, and remember the choice. Set the class in an inline script in `<head>` so the page does not flash.

On Tailwind v3, put the same values in `theme.extend.colors` as `rgb(var(--color-…) / <alpha-value>)` and define the variables in `:root` and `.dark`.

## Without Tailwind

Define the same names as plain custom properties on `:root` and `.dark` (for example `--color-surface`) and reference them with `var()`. With Bootstrap, map them onto its `--bs-*` variables instead of adding a parallel set.
