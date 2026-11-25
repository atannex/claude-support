# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.2.1] — 2026-11-25

### Security

- The manifest (`.claude/claude-support.json`) is now validated before use. A crafted entry such as `../file` could make `claude:install --force` or `claude:uninstall` delete files outside `.claude/`; such manifests are now rejected.
- Files reached through a symlink that points outside the project (for example a cloned repository whose `.claude/` links elsewhere) are skipped instead of written or deleted.

### Fixed

- A CLAUDE.md whose start marker had lost its end marker could have project text between the markers overwritten on a later install. Unpaired markers are now reported and the file is left alone.
- Malformed `composer.json`, `composer.lock` or `package.json` (non-array sections, non-string versions) no longer crash stack detection.
- A manifest with non-string hashes is reported clearly instead of failing with a PHP error.
- `.claude/settings.json` keeps non-ASCII characters as written instead of escaping them, and is never written empty if encoding fails.

## [1.2.0] — 2026-11-25

### Added

- `admin-dashboard-design` skill: a design system for admin panels and dashboards, with design tokens (Tailwind CSS v4 and plain CSS, light and dark), layout and navigation rules, component guidance and a quality checklist. It adapts to Filament, Nova or panels built from scratch.
- `/admin-panel` command to build or redesign an admin panel or dashboard on that design system and verify it.
- `ui-reviewer` agent that reviews admin screens against the design system.

## [1.1.0] — 2026-11-25

### Changed

- Stack detection now uses a catalog of 237 real packages (88 Composer, 149 npm), all checked against Packagist and npm. They're grouped by category: auth, UI, infrastructure, search, data, tenancy, payments, APIs and AI, observability and quality on the PHP side; frameworks, Laravel bridges, styling, state, routing, forms, i18n, realtime, charts, build, testing and linting on the JavaScript side. `CLAUDE.md` lists them one line per category.
- Scoped families such as `@radix-ui/*` are matched by prefix.

### Fixed

- Frontend entries with incorrect npm names (for example `angular` instead of `@angular/core`) no longer report the wrong package or nothing at all.

## [1.0.1] — 2026-11-25

### Changed

- Require Laravel 12 or 13. Every Laravel 11 release is now blocked by Composer's security-advisory policy, and Laravel 11 is past end of life.

### Fixed

- CI: enable the `fileinfo` extension on Windows runners, and install the dev-only tools (Pint, Larastan) only in the quality job, so the PHP 8.2 jobs can install dependencies.

## [1.0.0] — 2026-11-25

### Added

- `claude:install`, `claude:status` (`--check`, `--json`) and `claude:uninstall` Artisan commands, each with `--dry-run` and `--force` where relevant.
- Slash commands: `/enterprise-redesign`, `/audit`, `/feature`, `/upgrade-laravel`.
- Subagents: `migration-reviewer`, `query-auditor`.
- Skill: `laravel-testing`.
- A managed block in `CLAUDE.md`, generated from the stack: PHP and Laravel versions, test runner, database driver, frontend tooling, notable packages, verification commands, the team's working language and conventions.
- Additive merge of verification permissions into `.claude/settings.json`.
- A hash manifest (`.claude/claude-support.json`), so updates never overwrite local edits and uninstall removes only the package's own work. Line endings are ignored, so Windows checkouts don't show false "edited locally" states.
- Project sources (`resources/claude/`) and package registration (`ClaudeSupport::source()`, `::command()`, `::agent()`), with toggles and precedence.
- Validation of names, paths and required front matter before anything is written.
