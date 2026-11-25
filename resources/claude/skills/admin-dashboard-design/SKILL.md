---
name: admin-dashboard-design
description: Design system and layout rules for admin panels, dashboards and back-office screens — use whenever building, redesigning or reviewing admin, dashboard, settings or internal-tool UI, whether on Filament or Nova or built from scratch.
---

# Admin panels and dashboards

Admin screens are tools people use for hours. Optimise for scanning, speed and confidence: calm neutral surfaces, one accent colour, dense but readable data, and predictable placement of everything. Decoration that does not help someone find, compare or act on data does not belong.

## 1. Pick the path from the stack

Read "Stack" in CLAUDE.md, then look at the existing admin code before writing any.

- **Filament** (`filament/filament`): build with Filament's own resources, tables, forms, infolists, widgets and clusters. Theme through the panel provider (`->colors()`, `->font()`, `->sidebarCollapsibleOnDesktop()`, `->navigationGroups()`) and a custom theme stylesheet (`php artisan make:filament-theme`). Never hand-roll HTML that a Filament component already provides. Apply this skill's tokens, navigation grouping, table and state rules through those APIs.
- **Nova** (`laravel/nova`): use resources, lenses, filters, actions, metrics and dashboards. Brand through `config/nova.php` and `Nova::brandColors()` (or the version's equivalent). Custom tools and cards follow the tokens below.
- **From scratch** (Livewire/Flux, Inertia with Vue or React, or Blade): use the full design system in `references/`. Build the shell once as a layout, and every screen from a small set of reusable components. With Flux, Reka UI, Radix, Headless UI or a similar kit already installed, use its primitives for menus, dialogs, comboboxes and popovers instead of writing your own focus and keyboard handling.

If the project already has design tokens (a Tailwind `@theme` block, `tailwind.config.*` theme, CSS variables or a theme file), extend those. Introduce `references/tokens.md` only where nothing exists, and never run two token systems side by side.

## 2. The rules that matter most

1. **Tokens only.** Every colour, size, radius and shadow comes from a token. No raw hex values or one-off pixel sizes in components.
2. **One accent.** The accent marks the primary action, the current navigation item, focus rings and links. Everything else is neutral. Status colours (success, warning, danger, info) are for status only, never decoration.
3. **Same shell everywhere.** Sidebar, top bar, page header, content. A user should never wonder where the primary action or the filters are. See `references/layout.md`.
4. **One primary action per page,** top right in the page header. Destructive actions are secondary styled, red only in the confirmation step, and always confirmed.
5. **Tables are the core.** Server-side pagination, sorting and filtering; filters reflected in the URL; numbers right-aligned in tabular figures; row actions in a consistent last column. See `references/components.md`.
6. **Design every state:** loading (skeletons), empty (explain and offer the next action), error (say what happened and how to recover), no permission, and success feedback.
7. **Accessible by default:** WCAG AA contrast, visible focus, full keyboard use, labels on every input, colour never the only signal. See `references/checklist.md`.
8. **Light and dark** both work, through tokens, from the first screen rather than as a later retrofit.
9. **Authorization is visible.** Hide navigation and actions a user cannot use, and still enforce the policy on the server.
10. **Copy is short and specific.** Sentence case, verbs on buttons ("Create invoice", not "Submit"), real labels instead of jargon, user-facing strings through `__()`.

## 3. Dashboards specifically

- Lead with the 3–5 numbers people check daily, as stat cards: label, value, change against a stated period, and a link to the underlying list.
- Below them, one or two trend charts and a "needs attention" list (overdue, failed, pending approval). Each widget answers one question; name it as that question's answer ("Revenue this month"), not the chart type.
- Every widget links somewhere actionable. A number nobody can drill into is decoration.
- Compute aggregates in the database, cache them deliberately and say how fresh they are ("Updated 5 min ago"). Load slow widgets lazily so the page renders immediately.
- Respect one global date range control when the dashboard has one; do not give every widget its own.

## 4. Working method

1. Inventory existing screens and components; reuse before creating.
2. Settle tokens and the shell first, then build pages from components.
3. For each page decide: the user's main task, the primary action, the data shown and its states.
4. Check the result against `references/checklist.md` before calling it done, and run the project's tests. Admin routes need feature tests for both allowed and forbidden users.

## References

- `references/tokens.md` — colour, type, spacing, radius, elevation and motion tokens, with a Tailwind v4 `@theme` block and a plain CSS version.
- `references/layout.md` — app shell, navigation architecture, page header, content widths and responsive behaviour.
- `references/components.md` — tables, filters, forms, stat cards, charts, overlays, feedback and states.
- `references/checklist.md` — accessibility, responsiveness, performance and the review checklist.
