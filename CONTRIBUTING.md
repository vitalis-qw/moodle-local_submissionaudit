# Contributing to local_submissionaudit

Thanks for taking the time to help. Bug reports, ideas for new checks and pull
requests are all welcome.

## Reporting a bug or asking for a feature

Open an [issue](https://github.com/vitalis-qw/moodle-local_submissionaudit/issues/new/choose)
using one of the templates. For a bug, include your Moodle version
(`admin/index.php` → *Notifications*, or `version.php`), the PHP version, the
database (PostgreSQL / MariaDB / MySQL), the plugin version and the steps to
reproduce.

Security problems are **not** reported in public issues — see [SECURITY.md](SECURITY.md).

## Development setup

You need a Moodle 4.5–5.2 site (PHP 8.2+) with PHPUnit initialised.

```bash
cd /path/to/moodle
git clone https://github.com/vitalis-qw/moodle-local_submissionaudit.git local/submissionaudit
php admin/cli/upgrade.php --non-interactive
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite local_submissionaudit_testsuite
```

## Branches

- `master` — released versions.
- `development` — integration branch; every pull request targets it.
- Work in `feature/<short-name>` or `fix/<short-name>`, branched from `development`.

Pull requests are merged with a merge commit; `development` is merged into
`master` for a release.

## Before you open a pull request

The same checks as the CI must pass (GitHub Actions runs them on PostgreSQL and
MariaDB with [moodle-plugin-ci](https://moodlehq.github.io/moodle-plugin-ci/)):

```bash
moodle-plugin-ci phplint
moodle-plugin-ci phpcs --max-warnings 0
moodle-plugin-ci phpdoc --max-warnings 0
moodle-plugin-ci validate
moodle-plugin-ci savepoints
moodle-plugin-ci mustache
moodle-plugin-ci grunt --max-lint-warnings 0
moodle-plugin-ci phpunit --fail-on-warning
```

Please also:

- follow the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle);
- keep the layers apart: `classes/domain` never uses Moodle APIs; Moodle is reached
  through ports implemented in `classes/infrastructure`; see the *Architecture*
  section of the [README](README.md);
- add or update PHPUnit tests for every change (domain and application tests run
  without a database);
- bump `$plugin->version` in `version.php` and add a step to `db/upgrade.php` for
  any change under `db/`;
- add new language strings to `lang/en/local_submissionaudit.php` in alphabetical order;
- write commit messages as `type(scope): subject` (for example
  `feat(check): add a plagiarism score threshold`), imperative mood, at most 72 characters;
- sign your commits if you can — the protected branches require signed commits.

## Adding a check

A new check is one class implementing
`local_submissionaudit\domain\check\check_interface`, one block in
`infrastructure\moodle\settings_check_factory`, its admin setting in `settings.php`,
the `check:<name>` and message strings, and tests in `tests/domain`.

## Licence

By contributing you agree that your contribution is licensed under the
[GNU GPL v3 or later](LICENSE), like the rest of the plugin.
