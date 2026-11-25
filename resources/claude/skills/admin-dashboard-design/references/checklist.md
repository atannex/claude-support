# Quality and review checklist

Go through this before calling an admin screen done. The same list is what the `ui-reviewer` agent checks.

## Accessibility (WCAG 2.2 AA)

- [ ] Text contrast at least 4.5:1 (3:1 for large text, icons and input borders) in both light and dark mode.
- [ ] Every interactive element is reachable and usable with the keyboard alone, in a logical order, with a visible focus ring (2px `accent`, offset from the element).
- [ ] Inputs have real `<label>`s; errors are announced and linked with `aria-describedby`; required state is in `aria-required` or `required`.
- [ ] Icon-only buttons have an `aria-label`; decorative icons have `aria-hidden="true"`.
- [ ] Modals, drawers and menus trap focus while open, close on Escape and return focus to their trigger.
- [ ] Status is never conveyed by colour alone: badges have text, chart series have labels or patterns.
- [ ] Landmarks are present: `<nav>` for the sidebar, `<main>` for content, `<header>` for the top bar, with a "Skip to content" link.
- [ ] Tables use `<th scope>` and `aria-sort`; the current navigation item has `aria-current="page"`.
- [ ] Movement is removed under `prefers-reduced-motion: reduce`.
- [ ] The page is usable at 200% zoom and at 320px width.

## Consistency

- [ ] Only design tokens are used: no raw hex values, arbitrary pixel sizes or one-off shadows in components.
- [ ] One accent colour; status colours used only for status.
- [ ] Same shell, page header and action placement as the other screens; one primary action per page.
- [ ] Same table toolbar, row action column, pagination and empty states as the other lists.
- [ ] Domain statuses map to the same badge colours everywhere.
- [ ] Copy is sentence case, buttons are verbs, terms match the navigation and the rest of the panel, strings go through `__()`.
- [ ] Light and dark mode both checked.

## States

- [ ] Loading (skeletons), empty (first use and no results), error, no permission and success feedback are all designed.
- [ ] Long text, very large numbers, missing optional data, and lists of 0, 1 and 1,000+ items look right.
- [ ] Buttons disable and show progress while a request runs; double submission is impossible.
- [ ] Unsaved changes are protected on long forms.

## Responsive

- [ ] Works at 375px, 768px, 1280px and 1920px.
- [ ] Sidebar becomes a drawer below 1024px; tables keep their key columns or stack; actions collapse into a menu while the primary stays visible.

## Performance

- [ ] Lists are paginated, sorted and filtered in the database; no unbounded queries (run the `query-auditor` agent on data-heavy screens).
- [ ] Relationships shown in tables are eager-loaded; counts use `withCount()`.
- [ ] Dashboard aggregates are computed in SQL, cached deliberately, and slow widgets load lazily.
- [ ] Search inputs are debounced; heavy exports run as queued jobs and notify when ready.
- [ ] Assets are built with the project's bundler; no unused component libraries or icon sets added.

## Security and authorization

- [ ] Every admin route sits behind authentication and the right policy, gate or panel access check (`canAccessPanel()` in Filament).
- [ ] Navigation items and actions the user cannot use are hidden, and the server still rejects them.
- [ ] Bulk and destructive actions authorise each affected record, not just the first.
- [ ] Feature tests cover an allowed user, a forbidden user (403) and a guest (redirect to login) for each admin screen.
- [ ] Sensitive values (tokens, keys, personal data) are masked by default and revealed deliberately.
