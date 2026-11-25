---
description: Upgrade this project to the next Laravel major version safely, using the official upgrade guide, a test-backed plan and small verified steps.
argument-hint: "[optional target version, e.g. 13]"
---

# Upgrade Laravel

Target version (empty means the next major after the installed one): $ARGUMENTS

## 1. Establish the baseline

- Read the installed `laravel/framework` version from `composer.lock` and the PHP constraint from `composer.json`.
- Run the full test suite and record the result. If it is already failing, report this and ask whether to continue: an upgrade on a red suite cannot be verified.
- Check that the working tree is clean, so the upgrade diff stays reviewable.

## 2. Plan from the official guide

- Read the official upgrade guide for the target version (`laravel.com/docs/<version>/upgrade`). Do not rely on memory for breaking changes.
- For each change listed there, search this codebase for affected usage. Produce a checklist marked *affects us* / *does not affect us*, with the evidence for each.
- Check every first- and third-party package in `composer.json` for a release that supports the target version. List any blockers before changing anything.

## 3. Upgrade in small steps

1. Update the PHP constraint if the target needs it.
2. Update `laravel/framework` and the dependent packages in `composer.json`, then `composer update` with the flags needed for the minimum set of packages.
3. Apply the code changes from the checklist one area at a time, running the relevant tests after each.
4. Compare `config/`, `bootstrap/` and the skeleton files against the new Laravel skeleton, and adopt only the changes that matter.

## 4. Verify and report

- Full test suite, static analysis and formatter. Then `php artisan about`, `php artisan route:list` and `php artisan config:cache && php artisan config:clear` as smoke checks.
- Report: versions before and after, changes made per checklist item, packages bumped, anything deferred, and follow-ups for deployment (PHP version on servers, queue restarts, cache clears).
