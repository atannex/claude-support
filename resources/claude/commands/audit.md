---
description: Read-only enterprise audit of this Laravel project — architecture, data, security, performance and debt — written up as a prioritised report.
argument-hint: "[optional scope: a module, directory or concern]"
---

# Enterprise Audit

Scope for this run (empty means the whole project): $ARGUMENTS

Audit this project the way a senior architect would before a major investment decision. **Do not change application code.** The only file you write is the report.

## Method

1. Map the system first: `routes/`, `app/` structure, `config/`, `database/migrations/`, service providers, queues, scheduled tasks, external integrations and the test suite. Use `php artisan route:list --except-vendor` and `php artisan about` where helpful.
2. Read the code that carries the core business workflows end to end, from route to persistence and back.
3. Verify claims. When you suspect a problem (an N+1 query, a missing authorization check, a race condition), point to the file and line, and confirm it by reading the code paths involved rather than inferring from names.
4. Run the test suite once and record the result honestly.

## What to assess

- **Architecture**: boundaries between domains, where business logic lives, coupling, duplication, dead code.
- **Data**: schema design, relationships, foreign keys, unique constraints, indexes against real query patterns, soft deletes, migration safety, transaction boundaries.
- **Security**: authorization on every route and action (policies, gates, middleware), mass-assignment exposure, validation, file uploads, secrets handling, rate limiting, CSRF/CORS, SQL built from input, multi-tenant data isolation.
- **Reliability**: error handling, idempotency of jobs and webhooks, retries, failure visibility, logging.
- **Performance**: N+1 queries, unbounded queries, missing pagination or chunking, synchronous slow work, cache use.
- **Quality**: test coverage of critical paths, flaky or missing tests, static analysis, consistency of conventions.
- **Product and UX**: friction in core workflows, inconsistent patterns, missing empty, error and loading states.

## Report

Write `docs/audit/<YYYY-MM-DD>-audit.md` (create the directory if needed) with:

1. **Summary**: what the system is, its overall health in a few sentences, and the three most important actions.
2. **Findings**: a table of `ID · Severity (Critical/High/Medium/Low) · Area · Finding · Evidence (file:line) · Recommendation · Effort (S/M/L)`, ordered by severity.
3. **Strengths to preserve**: what is working and should not be disturbed.
4. **Roadmap**: findings grouped into sequenced stages, each independently shippable.

Prefer fewer, verified findings over many speculative ones. Mark anything you could not verify as such. Finish by telling me the report's path and the top three findings.
