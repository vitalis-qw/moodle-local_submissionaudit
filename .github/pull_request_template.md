## What and why

<!-- What does this change do, and why is it needed? Link the issue: "Closes #12". -->

## How it was tested

<!-- Steps, Moodle version, database. -->

## Checklist

- [ ] Targets `development`
- [ ] PHPUnit tests added or updated; `moodle-plugin-ci phpunit` passes
- [ ] `moodle-plugin-ci phpcs --max-warnings 0` and `phpdoc --max-warnings 0` pass
- [ ] `version.php` bumped and `db/upgrade.php` step added if `db/` changed
- [ ] New language strings in `lang/en`, alphabetical order
- [ ] Layers respected: no Moodle APIs in `classes/domain`
- [ ] Commits signed
