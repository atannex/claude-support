# atannex/claude-support

Claude Code support for Laravel projects. This package adds a set of Laravel-focused slash commands, subagents, skills, and project instructions so your team can work with Claude Code using a consistent setup.

It also keeps track of the files it installs. When you update or remove the package, your own edits are protected.

- PHP 8.2+
- Laravel 12 or 13
- Linux, macOS, and Windows

## Installation

Install the package as a development dependency:

```bash
composer require atannex/claude-support --dev
php artisan claude:install
```

Commit `.claude/` and `CLAUDE.md` to your repository so the rest of your team can use the same setup.

To refresh the Claude Code files after package updates, add this to your application's `composer.json`:

```json
{
    "scripts": {
        "post-update-cmd": [
            "@php artisan claude:install --ansi"
        ]
    }
}
```

## What's included

### Slash commands

| Command                        | What it does                                                                                                 |
| ------------------------------ | ------------------------------------------------------------------------------------------------------------ |
| `/enterprise-redesign [focus]` | Reviews and improves the project with a focus on architecture, security, data, user experience, and quality. |
| `/audit [scope]`               | Audits the selected area and writes a prioritised report to `docs/audit/`.                                   |
| `/feature <description>`       | Helps implement a feature, including its backend, interface, and tests.                                      |
| `/upgrade-laravel [version]`   | Guides a Laravel upgrade through small, verifiable steps.                                                    |

### Subagents

| Agent                | What it does                                                                  |
| -------------------- | ----------------------------------------------------------------------------- |
| `migration-reviewer` | Reviews migrations for deployment risks, data loss, indexes, and constraints. |
| `query-auditor`      | Looks for N+1 queries, missing indexes, and inefficient database queries.     |

### Skill

- `laravel-testing` — project-focused guidance for writing and running Laravel tests.

### Project instructions

The package adds a managed section to `CLAUDE.md` based on your project setup. It includes your PHP and Laravel versions, database, test runner, the commands Claude should use to verify its work, and your project conventions.

It also lists the packages your project uses, read from `composer.json`, `composer.lock` and `package.json`. Detection uses a catalog of over 230 widely used Composer and npm packages, grouped by category so Claude knows how the application is built:

| Source   | Categories                                                                                                                                                              |
| -------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Composer | Auth & access, UI & admin, Infrastructure, Search, Domain & data, Auditing & backups, Multi-tenancy, Payments, APIs & AI, Observability, Quality & testing               |
| npm      | Frameworks, Meta-frameworks, Laravel bridges, Styling & components, State & data, Routing, Forms & validation, Internationalisation, Realtime, Charts, graphics & motion, Build, Testing, Language & linting |

For example:

```markdown
### Stack

- PHP ^8.3, Laravel 13.44.1
- Tests: Pest
- Default database connection driver: mysql
- Frontend — Frameworks: Vue
- Frontend — Laravel bridges: Inertia (Vue), Ziggy
- Frontend — Styling & components: Tailwind CSS, Reka UI
- Frontend — Build: Vite, laravel-vite-plugin
- Packages — Auth & access: Fortify (1.25.0), spatie/laravel-permission (6.21.0)
- Packages — Infrastructure: Horizon (5.33.1)
```

Package families such as `@radix-ui/*` are matched by prefix, and any `atannex/*` packages are always listed.

## Commands

| Command                        | Description                                                                       |
| ------------------------------ | --------------------------------------------------------------------------------- |
| `php artisan claude:install`   | Installs or updates the package resources.                                        |
| `php artisan claude:status`    | Shows which installed files are current, outdated, missing, or have been changed. |
| `php artisan claude:uninstall` | Removes the resources managed by the package.                                     |

Useful options:

```bash
php artisan claude:install --dry-run
php artisan claude:status --check
php artisan claude:status --json
php artisan claude:uninstall --dry-run
```

## Your own commands, agents, and skills

You can add project-specific resources under `resources/claude/`:

```text
resources/claude/
├── commands/
│   └── release-notes.md
├── agents/
│   └── domain-expert.md
└── skills/
    └── deploy/
        └── SKILL.md
```

Run the installer to sync them:

```bash
php artisan claude:install
```

Project resources take precedence over resources supplied by the package.

## Configuration

Publish the configuration file if you need to change the install path, enable or disable resources, add your own resource directories, or set team conventions:

```bash
php artisan vendor:publish --tag=claude-support-config
```

You can also publish the package's starter resources:

```bash
php artisan vendor:publish --tag=claude-support-resources
```

## Keeping your changes safe

The package checks installed files before updating them:

- New files are installed.
- Unchanged files can be updated when a new package version is released.
- Files you've edited are left alone unless you use `--force`.
- Files no longer provided by the package are removed only if they haven't been edited.

The package manages only its own section of `CLAUDE.md` and the permission entries it added to `.claude/settings.json`. Your other instructions and settings are preserved.

## License

MIT. See [LICENSE](LICENSE).
