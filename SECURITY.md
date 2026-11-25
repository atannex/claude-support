# Security Policy

## Supported versions

| Version | Supported |
| --- | --- |
| 1.x | Yes |

## Reporting a vulnerability

Please **do not** open a public issue. Email atangageihlekecollins@gmail.com with a description, the affected version and steps to reproduce. You will get an acknowledgement within 72 hours and a fix or mitigation plan within 14 days.

## Scope notes

This package writes files only inside the configured project root. Resource names are restricted to `^[a-z0-9][a-z0-9-]*$`, and configured paths may not be absolute or contain `..`. Any way around these restrictions is a vulnerability.

The permissions it adds to `.claude/settings.json` are limited to running tests, the formatter and static analysis. Reports of a shipped resource that could lead Claude to run destructive commands are also in scope.
