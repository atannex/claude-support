---
name: laravel-testing
description: How to write fast, reliable tests in this Laravel project — use whenever adding, changing or fixing tests, or verifying a change with tests.
---

# Testing in this project

The test runner is listed under "Stack" in CLAUDE.md. Use the existing tests in `tests/` as your style guide: if they are Pest, write Pest; if PHPUnit, write PHPUnit classes. Never mix the two in a file.

## Choosing the test

- **Feature tests** (`tests/Feature`) for anything reached over HTTP, the console or a queue: they exercise routing, middleware, validation, authorization and persistence together. Most tests belong here.
- **Unit tests** (`tests/Unit`) for pure logic with many cases, such as calculations, parsers and state machines.

## Writing it well

- Build data with model factories and states, never hand-written inserts. Create only what the test needs.
- Use `RefreshDatabase` (or the project's existing database trait) so tests don't depend on each other.
- Act as a user with `actingAs($user)`, and test the denial path too (`assertForbidden()`), not only the happy path.
- Fake the outside world: `Http::fake()` with `Http::preventStrayRequests()`, `Mail::fake()`, `Notification::fake()`, `Queue::fake()`, `Event::fake([...])` (only the events you assert on), `Storage::fake()`, and `Carbon::setTestNow()` or `$this->travel()` for time.
- Assert outcomes rather than implementation: the response status and payload, `assertDatabaseHas()` or `assertModelExists()`, and the jobs, mail or notifications sent.
- Test validation with one case per rule that matters, checking the specific `assertInvalid(['field'])` key.
- Name tests after behaviour: `test_guests_cannot_view_invoices` or `it('rejects expired coupons')`.

## Running

- Run only the affected tests while iterating (`php artisan test --filter=InvoiceTest`), then the full suite before finishing.
- A test that passes on its first run without you ever seeing it fail proves little. For a bug fix, write the failing test first.
- Never weaken an assertion, skip a test or delete a failing test just to make the suite pass. Fix the cause or report it.
