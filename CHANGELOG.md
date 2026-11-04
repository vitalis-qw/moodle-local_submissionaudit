# Changelog

## 0.2.0 — 2026-11-04

- Rewritten into four layers (domain, application, infrastructure, ui) with
  interfaces, factories, DTOs and mappers; base classes under `classes/lib`.
- Services are resolved from Moodle's DI container; bindings live in
  `service_provider.php` (hook `core\hook\di_configuration`).
- New Moodle event `audit_completed`, fired after results are stored.
- Report page rendered from a Mustache template through a controller.
- Requires Moodle 4.5+ and PHP 8.2+.
- Test suite: 23 tests, run against PostgreSQL and MariaDB in CI.

## 0.1.0 — 2026-11-03

- First public release.
- Observer on `assessable_submitted`, ad-hoc audit task.
- Checks: minimum words, allowed file types, external verdict.
- Tutor report in the assignment settings menu.
- Privacy API provider, PHPUnit coverage for domain and integration.
