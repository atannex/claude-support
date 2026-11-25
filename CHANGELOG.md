# Changelog

All notable changes to this project are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

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
