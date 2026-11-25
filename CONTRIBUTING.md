# Contributing

Thanks for helping. Issues and pull requests are welcome.

## Development

```bash
composer install
composer test       # PHPUnit
composer analyse    # PHPStan, level 8
composer format     # Pint
```

All three must pass. CI runs them across PHP 8.2–8.5 and Laravel 11–13, on Linux and Windows.

## Adding a command, agent or skill

Shipped resources live in `resources/claude/`, laid out exactly like a project's `.claude/` directory. Each one must:

- solve a recurring, Laravel-specific problem that Claude Code's built-in commands don't already cover;
- have the front matter Claude Code needs (`description`; agents and skills also need a `name` matching their file or directory);
- tell Claude how to verify its work, and never instruct it to fake, skip or weaken tests;
- be added to `ResourcesTest::test_every_shipped_resource_passes_validation` and to the README.

## Pull requests

- One concern per pull request, with tests for any behaviour change.
- Update `CHANGELOG.md` under *Unreleased*.
- Changes to the sync rules (what is overwritten, skipped or removed) need a test that proves local work is still protected.
