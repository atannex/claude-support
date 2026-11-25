---
description: Build a feature end to end the Laravel way — schema, model, authorization, validation, business logic, UI or API, and tests — then verify it.
argument-hint: "<what the feature should do>"
---

# Build a Feature

Feature to build: $ARGUMENTS

If that is empty or too vague to act on, ask me one focused question before starting.

## 1. Understand before writing

- Find how similar features are already built here and follow those patterns: folder layout, naming, action or service classes, API resources, Livewire/Inertia/Blade conventions, test style.
- Identify who may use the feature and what they may do. Authorization is part of the design, not an afterthought.
- State your plan in a few lines (files to add or change, data changes, risks) before implementing anything non-trivial.

## 2. Implement in layers

1. **Data**: a new migration (never edit one that has run), with foreign keys, the indexes its queries need, and sensible nullability and defaults. Add a factory for every new model.
2. **Model**: fillable or guarded, casts, relationships, and query scopes for the access patterns you introduce.
3. **Authorization**: a policy or gate for each ability, applied in the controller or route.
4. **Validation**: a Form Request with explicit rules and messages. Never pass `$request->all()` into a model.
5. **Business logic**: an action or service class. Wrap multi-step writes in `DB::transaction()`. Fire events or dispatch queued jobs for side effects, after commit.
6. **Interface**: routes, controller, then views or API resources. User-facing strings go through `__()` and `lang/`. Handle empty, error and success states.

## 3. Prove it works

- Feature tests for the happy path, validation failures, authorization denials (403) and the important edge cases. Add unit tests for non-trivial logic.
- Run the project's tests, formatter and static analysis (see "Verify your work" in CLAUDE.md) and fix what you broke.

Finish with a short summary: what was added, how to use it, anything left deliberately out, and the test results.
