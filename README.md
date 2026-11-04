# Submission audit (`local_submissionaudit`)

A Moodle local plugin that runs configurable checks on every assignment
submission in the background and gives tutors a per-assignment audit report.

It exists to show one way of building a Moodle plugin that stays testable as
it grows: the rules live in a framework-free domain layer, Moodle is reached
only through small adapters, and the slow or unreliable parts (an external
analysis service) can never block a student's submission.

| | |
|---|---|
| Moodle | 4.1 – 4.5 |
| PHP | 8.1+ |
| Licence | GPL v3 or later |
| Status | beta, 0.1.0 |

## What it does

1. A student submits an assignment. `mod_assign` fires
   `\mod_assign\event\assessable_submitted`.
2. The observer queues one ad-hoc task for that submission and returns at
   once. Nothing runs inside the student's request.
3. Cron runs the task: the submission (online text, file names) is loaded,
   every configured check runs, the results are stored. A check that throws
   becomes an `error` row; the other checks still run.
4. Tutors open **Submission audit** in the assignment's settings menu and see
   one row per student and check: pass / fail / error, an optional score and a
   translated explanation.

Built-in checks:

| Check | Setting | Fails when |
|---|---|---|
| Minimum words | `minwords` | the online text has fewer words (Unicode-aware) |
| Allowed file types | `allowedfiletypes` | a submitted file has an extension outside the list |
| External verdict | `externalurl`, `externaltoken` | an HTTPS endpoint answers `{"status": "fail"}`; an unreachable endpoint gives `error` |

A check is off while its setting is empty or zero.

## Structure

```
classes/
├── domain/                 pure PHP, no Moodle: the rules
│   ├── submission.php        value object (text, file names, word count)
│   ├── result.php            value object (status, score, message key + params)
│   ├── check_interface.php   one rule
│   ├── auditor.php           runs all checks, isolates failures
│   ├── audit_runner.php      the use case: load → audit → store, via ports
│   ├── check/                minimum_words, allowed_file_types, external_verdict
│   └── port/                 interfaces the domain needs the outside world to implement
├── adapter/                Moodle implementations of the ports
│   ├── moodle_submission_loader.php   assign_submission + onlinetext + file area → submission
│   ├── db_result_store.php            results → local_submissionaudit_result
│   ├── curl_verdict_client.php        Moodle \curl → verdict
│   └── check_factory.php              plugin settings → list of checks
├── observer.php            event → queued task
├── task/audit_submission.php   ad-hoc task: wires adapters to the domain
├── local/message_formatter.php stored {key, params} → translated text
└── privacy/provider.php    GDPR export and deletion
```

Why the split:

- **Domain tests run without a database.** `tests/domain/auditor_test.php`
  covers every rule with `basic_testcase` in milliseconds.
- **Messages are keys, not text.** The domain returns a language-string key
  with parameters; the report renders it in the tutor's language. Tests
  assert on keys and never on English prose.
- **The external service is a port.** `external_verdict` depends on
  `verdict_client_interface`; the test uses a three-line fake, production
  uses Moodle's `\curl` (proxy and blocked-host settings apply).
- **Adding a check** is one class implementing `check_interface`, one line in
  `check_factory`, and a `check:<name>` string.

## Install

```bash
cd /path/to/moodle
git clone https://github.com/<owner>/moodle-local_submissionaudit local/submissionaudit
php admin/cli/upgrade.php --non-interactive
```

Then *Site administration → Plugins → Local plugins → Submission audit*:
enable, set a minimum word count and/or allowed extensions, optionally an
external endpoint. Cron must be running for results to appear.

### External endpoint contract

Request, `POST`, `application/json`, `Authorization: Bearer <token>` when set:

```json
{"submissionid": 42, "text": "plain text of the submission", "filenames": ["essay.pdf"]}
```

Response, HTTP 2xx:

```json
{"status": "pass" | "fail", "score": 0.87, "message": "87% similarity with 2 sources"}
```

Anything else (timeout after 20 s, non-2xx, malformed JSON) is stored as an
`error` row with the reason, so the tutor sees that the verdict is missing
instead of a silent pass.

## Development

```bash
# in a Moodle checkout with PHPUnit initialised
vendor/bin/phpunit --testsuite local_submissionaudit_testsuite
# or with moodle-plugin-ci
moodle-plugin-ci phpunit
```

CI (`.github/workflows/ci.yml`) runs `moodle-plugin-ci`: phplint, phpcs with
the Moodle standard, phpdoc, savepoints, mustache, grunt, phpunit on Moodle
4.1 and 4.5 with PHP 8.1 – 8.3, PostgreSQL and MariaDB.

## Roadmap

- Re-run button on the report page.
- Per-assignment overrides of the site-wide settings.
- Notify the tutor when a check fails (message API).
- Behat scenario for the report page.

## Author

Vitalii Kuzmyn — senior PHP developer, Moodle and Totara plugins.
