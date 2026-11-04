# Submission audit (`local_submissionaudit`)

A Moodle local plugin that runs configurable checks on every assignment submission
in the background and gives tutors a per-assignment audit report.

It exists to show one way of building a Moodle plugin that stays testable as it
grows: a framework-free domain, use cases that talk to Moodle only through ports,
dependency injection through Moodle's own container, and a thin UI edge. The slow
or unreliable part (an external analysis service) can never block a student's
submission.

| | |
|---|---|
| Moodle | 4.5 – 5.2 |
| PHP | 8.2+ |
| Databases | PostgreSQL, MariaDB/MySQL (CI runs both) |
| Licence | GPL v3 or later |
| Status | beta, 0.2.0 |

## What it does

1. A student submits an assignment. `mod_assign` fires
   `\mod_assign\event\assessable_submitted`.
2. The observer queues one ad-hoc task for that submission and returns at once.
   Nothing runs inside the student's request.
3. Cron runs the task: the submission (online text, file names) is loaded, every
   configured check runs, the results are stored, and
   `\local_submissionaudit\event\audit_completed` is fired. A check that throws
   becomes an `error` row; the other checks still run.
4. Tutors open **Submission audit** in the assignment's settings menu and see one
   row per student and check: pass / fail / error, an optional score and a
   translated explanation.

Built-in checks:

| Check | Setting | Fails when |
|---|---|---|
| Minimum words | `minwords` | the online text has fewer words (Unicode-aware) |
| Allowed file types | `allowedfiletypes` | a submitted file has an extension outside the list |
| External verdict | `externalurl`, `externaltoken` | an HTTPS endpoint answers `{"status": "fail"}`; an unreachable endpoint gives `error` |

A check is off while its setting is empty or zero.

## Architecture

Four layers; dependencies point inward only.

```
classes/
├── domain/            pure PHP, no Moodle import anywhere
│   ├── model/           submission (entity), audit (aggregate root), result, verdict, check_status
│   ├── check/           check_interface, minimum_words, allowed_file_types, external_verdict
│   ├── service/         auditor: runs the checks, isolates failures
│   ├── port/            interfaces the domain needs the outside world to implement
│   └── event/           audit_completed (domain event, recorded by the aggregate)
├── application/       use cases; in and out as DTOs
│   ├── dto/             audit_request, audit_report, check_result
│   ├── service/         audit_service (load → audit → save → publish), report_service
│   ├── factory/         the only places that construct audits and submissions
│   ├── mapper/          audit → audit_report
│   ├── security/        audit_action, audit_permission_interface (+ factory)
│   └── port/            user_directory_interface
├── infrastructure/    Moodle adapters implementing the ports
│   ├── persistence/     db_audit_repository, result_record_mapper (explicit casts)
│   ├── moodle/          moodle_submission_repository, settings_check_factory, moodle_user_directory
│   ├── http/            curl_verdict_client (Moodle \curl: proxy and blocked hosts honoured)
│   ├── security/        capability_audit_permission (+ factory)
│   ├── event/           audit_completed_event_factory: domain event → Moodle event
│   └── config/          settings: typed view of get_config()
├── ui/                HTTP edge
│   ├── request/         report_request (cleaned with \core\param)
│   ├── controller/      report_controller: request in, response out
│   └── output/          report_page renderable + templates/report_page.mustache
├── lib/               base classes (entity/aggregate, DTO, mapper, permission, filters,
│                      request/controller/response, service provider) shared by the layers
├── service_provider.php   binds every interface to its implementation (\core\di)
├── observer.php, task/, event/, privacy/   thin Moodle entry points
report.php             three lines: build the request, resolve the controller, send
```

Rules the code follows:

- Objects cross a layer boundary as DTOs; entities never reach the UI.
- Every service, port, repository, factory, mapper, controller and permission has an
  interface; implementations are bound in `service_provider.php` and resolved with
  `\core\di::get()`. Tests swap them with `\core\di::set()`.
- Entities are built through factories or named constructors only.
- The domain throws its own exceptions; the controller translates them into Moodle
  error pages (`guard()`).
- Authorisation is a `permission` object with `can()` / `require()`, backed by the
  `local/submissionaudit:viewreport` capability.
- Messages are stored as `{key, params}` and translated when displayed, so the
  domain does not know which language the tutor reads.

## Install

Copy to `local/submissionaudit` and run the upgrade (web or
`php admin/cli/upgrade.php`). Settings are under *Site administration → Plugins →
Local plugins → Submission audit*.

## Tests

```
vendor/bin/phpunit --testsuite local_submissionaudit_testsuite
```

Domain and application tests run without a database; infrastructure and
integration tests use Moodle's generators. CI runs the suite against PostgreSQL and
MariaDB with [moodle-plugin-ci](https://github.com/moodlehq/moodle-plugin-ci).

## Extending

A new check is one class implementing `domain\check\check_interface` plus one
block in `infrastructure\moodle\settings_check_factory` (and a setting). A check
that needs the outside world asks for a port in its constructor; the port's adapter
is bound in `service_provider.php`.
