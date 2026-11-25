# Layout and navigation

## The app shell

```text
┌──────────────┬─────────────────────────────────────────────────────┐
│ Logo / app   │ Top bar: search ⌘K · environment badge · notif · user│
│──────────────├─────────────────────────────────────────────────────┤
│ Dashboard    │ Breadcrumbs                                         │
│              │ Page title            [Secondary]  [Primary action] │
│ SALES        │ Short description or key facts                      │
│  Orders   12 │─────────────────────────────────────────────────────│
│  Customers   │ Tabs / filters / toolbar                            │
│              │                                                     │
│ CATALOG      │ Content (table, form, cards)                        │
│  Products    │                                                     │
│              │                                                     │
│──────────────│                                                     │
│ Settings     │                                                     │
│ « Collapse   │                                                     │
└──────────────┴─────────────────────────────────────────────────────┘
```

Build it once as the admin layout (a Blade layout or component, a Livewire layout, or an Inertia persistent layout so the sidebar does not re-render on navigation). Pages only provide the header and content.

## Sidebar

- 256px wide, collapsible to a 64px icon rail on desktop; the collapsed state is remembered. Below 1024px it becomes an off-canvas drawer opened from a menu button, closed with Escape and by tapping outside, with focus trapped while open.
- Every item has an icon (one icon set, one size, outline style) and a label. In the icon rail, labels show as tooltips.
- The current item uses `accent-soft` background and `accent` text, plus `aria-current="page"`. Parent groups of the current page stay expanded.
- Optional counts on items (open tickets, pending orders) as small neutral badges; use `danger` only when it needs action.
- Pin "Settings" and account-level items to the bottom.

## Navigation architecture

- Group items by the user's domain (Sales, Catalog, People, Finance), not by database table or technical layer. Use short uppercase or `fg-subtle` group labels.
- Keep 5–9 items per group and at most two levels. Deeper structure goes into tabs or sub-navigation on the page, not into the sidebar.
- Order groups by frequency of use, with the dashboard first.
- One noun per item, plural for lists ("Invoices"). Names match page titles and breadcrumbs exactly.
- Hide items the user is not authorised to see; never show a link that leads to a 403.
- Multi-tenant or multi-team apps put the tenant switcher at the top of the sidebar.

## Top bar

- 56–64px tall, `surface` with a bottom border, sticky.
- Left: the mobile menu button and, if useful, global search. Right: environment badge (show "Staging" or "Local" prominently; never on production), notifications, help, theme toggle, user menu (profile, settings, sign out).
- A command palette (⌘K / Ctrl+K) for jumping to pages and records pays off in any panel with more than a handful of sections.

## Page header

- Breadcrumbs above the title for anything below the top level. The last crumb is the current page and not a link.
- Title on the left (`text-2xl`, 600), with the record's status badge beside it on detail pages. One line of description or key facts below when it helps.
- Actions on the right: the single primary button last, secondary buttons before it, rarely used actions in a "More" (⋯) menu. On small screens they collapse into that menu, keeping the primary visible.

## Page types

- **Index (list):** header, optional tabs for saved views ("All", "Active", "Archived"), a toolbar with search, filters and view options, then the table and pagination.
- **Detail:** header with status and actions; a main column (about two thirds) with sectioned cards for the record's content, and a side column with metadata, related records and an activity timeline. Tabs for large records.
- **Create / edit:** a form in a constrained width (about 720px) with sections; or a two-column layout with section titles and descriptions on the left and fields on the right. Save and cancel in a sticky footer bar for long forms.
- **Settings:** a secondary vertical nav (General, Users, Billing, Integrations) beside a settings form.
- **Dashboard:** stat cards in a row, then a 12-column grid of widgets (trends, "needs attention", recent activity).

## Content width and grid

- Tables and dashboards use the full available width with 24–32px page padding (16px on mobile).
- Forms and reading content are capped (about 720px) so lines stay readable.
- Use a 12-column grid with 24px gaps for dashboards; cards in a row share the same height.

## Responsive behaviour

- Breakpoints: below 640px phone, 640–1023px tablet, 1024px and up desktop with the sidebar visible.
- Tables on small screens: keep the key columns and move the rest into an expandable row or a stacked card layout; never squash ten columns. Horizontal scroll inside the table container is acceptable as a last resort, with the first column sticky.
- Stat cards wrap from one row to a 2×2 grid to a single column.
- Touch targets are at least 40×40px on touch screens.
