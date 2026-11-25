---
description: Build or redesign an admin panel or dashboard on a consistent design system — on Filament or Nova, or from scratch — then verify it.
argument-hint: "[optional: the area or screens to build, e.g. \"orders dashboard\" or \"whole panel\"]"
---

# Build an Admin Panel or Dashboard

Area to work on: $ARGUMENTS

If that is empty, treat it as the whole admin panel. If it names something too vague to act on, ask me one focused question before starting.

Follow the `admin-dashboard-design` skill throughout (`.claude/skills/admin-dashboard-design/`), and use CLAUDE.md for the stack, verification commands and conventions.

## 1. Understand what exists

- Find the current admin area: routes, layouts, Filament panels or Nova resources, Livewire components or Inertia pages, CSS entry points and any existing tokens (`@theme`, `tailwind.config.*`, CSS variables).
- List who uses the panel and their main tasks, the models and workflows it must cover, and the roles and policies that limit access.
- Note what is inconsistent or missing compared with the skill: tokens, shell, navigation structure, tables, forms, states, accessibility, dark mode.

## 2. Choose the path and plan

- Pick the path from the skill (Filament, Nova, or from scratch with the installed frontend stack). Do not add a new UI framework or admin package unless I ask for it.
- Present a short plan before building anything non-trivial:
  - the navigation map: groups, items and which roles see them
  - the screens to add or change, each with its main task, primary action and data
  - the tokens and shared components to create or reuse
  - anything that changes existing behaviour or URLs

Wait for my confirmation when the plan restructures existing screens or navigation; otherwise continue.

## 3. Build foundations first

1. **Tokens:** extend the existing tokens, or add the set from `references/tokens.md` in one place, with light and dark mode.
2. **Shell:** one admin layout with sidebar, top bar and page header per `references/layout.md`, including the mobile drawer and the dark-mode toggle. On Filament or Nova, configure the panel instead.
3. **Shared components:** buttons, badges, table, filters, form fields, stat card, modal or slide-over, toasts, empty and error states, unless the stack already provides them.

## 4. Build the screens

- Lists, details, forms and dashboards per `references/components.md`, with server-side pagination, sorting and filtering kept in the URL.
- Every screen protected by authentication and the right policy or gate; navigation and actions hidden for users who cannot use them.
- Dashboard widgets backed by database aggregates, cached deliberately, linking to the records behind them.
- All user-facing strings through `__()`.

## 5. Verify

- Feature tests for each admin route: an allowed user, a forbidden user (403) and a guest. Test bulk and destructive actions, filters and validation.
- Run the project's tests, formatter and static analysis (see "Verify your work" in CLAUDE.md) and fix what you broke.
- Go through `references/checklist.md`. If the frontend can be run and viewed, check the screens at phone and desktop width in light and dark mode.
- For screens that list or aggregate a lot of data, run the `query-auditor` agent; for the finished UI, run the `ui-reviewer` agent and fix what it reports.

Finish with a short summary: the navigation map, screens added or changed, shared components and tokens introduced, test results, and anything left for later.
