# Components

Build each of these once and reuse it. With a component kit already in the project (Flux, Filament, Reka UI, Radix, Headless UI, shadcn-style components), use and style its primitives rather than writing new ones.

## Buttons

- Variants: **primary** (`accent` fill, `on-accent` text), **secondary** (`surface` with `border`), **ghost** (text only, for toolbars and row actions), **danger** (red, only in destructive confirmations).
- Sizes follow the control heights in `tokens.md`. Icon-only buttons need an `aria-label` and a tooltip.
- Label with a verb and object: "Create invoice", "Export CSV". Show a spinner and disable the button while its request is in flight, so it cannot be submitted twice.

## Data tables

The most used component in any admin panel. Get these right:

- **Columns:** the identifying column first (name, number) and linked to the record; left-align text, right-align numbers and money in tabular figures; dates as relative ("3 h ago") with the exact timestamp in a tooltip, or as a consistent absolute format; status as a badge; row actions in the last column.
- **Header:** `surface-muted` background, `text-xs`/`text-sm` 500 in `fg-muted`, sticky when the table scrolls. Sortable columns show their direction and use `aria-sort`.
- **Rows:** subtle hover (`surface-muted`); the whole row can be clickable when it opens the record, while inner buttons and links keep working. Truncate long text with a tooltip instead of wrapping rows to different heights.
- **Toolbar:** search on the left (debounced about 300ms), filters next to it, then view options (columns, density) and export on the right. Active filters appear as removable chips with "Clear all".
- **Bulk actions:** checkboxes in the first column; selecting rows replaces the toolbar with a bar showing "3 selected", the available bulk actions and "Select all 1,240 matching". Destructive bulk actions are confirmed with the count.
- **Server-side everything:** pagination, sorting, filtering and search run in the database, and the state lives in the query string so views can be shared, bookmarked and survive a refresh. Default page size 25, with options 10/25/50/100. Show "Showing 26–50 of 1,240".
- **Row actions:** one or two frequent ones as ghost icon buttons; the rest in a ⋯ menu. The same actions appear in the same order on every table.
- **States:** skeleton rows while loading; an empty state that distinguishes "No invoices yet" (explain, primary action to create one) from "No results" (show the active filters, offer to clear them).

## Filters

- Up to about four frequent filters inline (selects, date range, status); the rest in a "Filters" popover or panel.
- Date ranges offer presets (Today, Last 7 days, This month, Custom).
- Saved views as tabs above the table when users return to the same filtered lists.

## Forms

- Labels above fields, always visible: never use the placeholder as the label. Mark optional fields "(optional)" rather than starring required ones, when most fields are required.
- Help text below the field in `fg-subtle`; errors replace it in `danger` with an icon, are linked with `aria-describedby`, and say how to fix the problem ("Enter a date after the start date").
- Group fields in titled sections. Lay out related short fields side by side (first and last name), never unrelated ones.
- Validate on the server (Form Requests) and show errors inline next to each field, plus a summary at the top for long forms, then move focus to the first error.
- Choose the right control: switch for an immediate on/off setting, checkbox for a choice saved with the form, radio cards for 2–5 options worth explaining, select or combobox with search for long lists, a date picker that also accepts typed dates.
- Long forms get a sticky footer with Cancel and Save; warn before leaving with unsaved changes.
- After saving, show a success toast and either stay on the form or return to the record, consistently across the panel.

## Stat cards (KPIs)

```text
┌─────────────────────────────┐
│ Revenue this month       ⓘ  │  label in fg-muted, tooltip says how it is calculated
│ $48,290                     │  value in text-3xl, tabular figures
│ ▲ 12.4% vs last month       │  change in success/danger with an arrow and the period
│ ╱╲╱‾╲╱                      │  optional sparkline in the accent colour
└─────────────────────────────┘
```

- 3–5 in a row, equal width and height, each linking to the filtered list behind it.
- Whether "up" is good depends on the metric (refunds going up is bad): colour by meaning, not by direction.
- Abbreviate big numbers consistently (48.3k, 1.2M) and give the exact value in a tooltip.

## Charts

- Line or area for trends over time, bars for comparing categories, stacked bars for parts of a whole over time. Avoid pie charts beyond 3–4 slices, 3D, and dual axes.
- Start bar charts at zero. Label axes with units, keep gridlines faint (`border`), and label series directly or with a legend above the chart.
- Tooltips show the exact value and period. Provide an accessible summary or a "View as table" option.
- Use the chart library already in the stack (see CLAUDE.md); theme it from the tokens so it follows light and dark mode.

## Badges and status

- Small (`text-xs`, 500), `-soft` background with the strong status colour as text, `radius-sm` or a pill shape, optionally a leading dot or icon.
- Map each domain status to one colour once (for example `paid` success, `pending` warning, `failed` danger, `draft` neutral) in a single place such as an enum's `color()` method, and reuse it everywhere.

## Overlays

- **Modal** for short confirmations and small focused tasks (up to about 5 fields). Title, short body, actions bottom right with the primary last. Closes on Escape, traps focus and returns focus to the trigger.
- **Slide-over** (from the right, 400–640px) for quick create, edit or preview without losing the list context.
- **Full page** for anything long, complex or worth linking to.
- **Destructive confirmations** name the thing and the consequence ("Delete invoice INV-1042? This cannot be undone."); for high-impact actions require typing the name. Prefer soft deletes with an "Undo" toast where the domain allows it.
- Menus and popovers close on Escape and outside click and support arrow keys.

## Feedback

- **Toasts** bottom right (top on mobile) for the result of an action: short, specific, auto-dismiss after about 5s for success, stay until dismissed for errors, and offer "Undo" where possible. Announce them with `role="status"` (or `alert` for errors).
- **Inline alerts** (banners) in the page for persistent conditions: trial ending, failed sync, read-only mode.
- **Loading:** skeletons that match the final layout for content; spinners only inside buttons and small areas. Never block the whole page with a spinner.

## Empty, error and permission states

- **Empty:** a simple icon or illustration, one sentence explaining what will appear here, and the primary action ("Create your first product"). Optionally a link to documentation.
- **Error:** what went wrong in plain words, what the user can do (retry, contact support), and a reference id when one exists. Keep the shell visible so the user is not stranded.
- **No permission:** explain that access is missing and who can grant it, rather than a bare 403.
- Custom 404, 403, 419, 500 and 503 pages use the same visual language (`php artisan vendor:publish --tag=laravel-errors`).

## Activity and timelines

- Detail pages for important records show who changed what and when, newest first, with relative times. Reuse the project's audit log package when one is listed in CLAUDE.md.
