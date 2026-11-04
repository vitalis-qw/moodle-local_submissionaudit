# Security policy

## Supported versions

| Plugin version | Moodle | Supported |
|---|---|---|
| 0.2.x | 4.5 – 5.2 | yes |
| 0.1.x | 4.1 – 4.5 | no |

## Reporting a vulnerability

Please **do not** open a public issue for a security problem.

Report it privately through GitHub:
[Security → Report a vulnerability](https://github.com/vitalis-qw/moodle-local_submissionaudit/security/advisories/new),
or by e-mail to vitalis.qw@gmail.com with "local_submissionaudit security" in the subject.

Include the affected version, the Moodle version, steps to reproduce and the
impact you expect. You will get an answer within 5 working days. A fix is
released as soon as it is ready, and you are credited in the advisory unless you
prefer otherwise.

## Scope

In scope: code in this repository — access to the audit report, the stored
results, the external verdict client and the privacy provider.

Out of scope: Moodle core, other plugins, and the external verdict service you
configure (it receives the submission text and file names by design; see the
privacy notes in the README).
