<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Submission audit';
$string['submissionaudit:viewreport'] = 'View the submission audit report';

// Settings.
$string['settings:enabled'] = 'Enable audits';
$string['settings:enabled_desc'] = 'When disabled, submissions are still observed but no checks run.';
$string['settings:minwords'] = 'Minimum word count';
$string['settings:minwords_desc'] = 'Online text submissions with fewer words fail the "Minimum words" check. 0 disables the check.';
$string['settings:allowedfiletypes'] = 'Allowed file extensions';
$string['settings:allowedfiletypes_desc'] = 'Comma-separated list, for example "pdf, docx". Submitted files with other extensions fail the "Allowed file types" check. Empty disables the check.';
$string['settings:externalurl'] = 'External verdict endpoint';
$string['settings:externalurl_desc'] = 'HTTPS URL that receives the submission text and file names as JSON and answers with {"status": "pass|fail", "score": 0.0, "message": "..."}. Empty disables the check.';
$string['settings:externaltoken'] = 'External endpoint token';
$string['settings:externaltoken_desc'] = 'Sent as a Bearer token in the Authorization header.';

// Report.
$string['report'] = 'Submission audit';
$string['reporttitle'] = 'Submission audit: {$a}';
$string['noresults'] = 'No audited submissions yet. Results appear after the next cron run that follows a submission.';
$string['col:student'] = 'Student';
$string['col:check'] = 'Check';
$string['col:status'] = 'Status';
$string['col:score'] = 'Score';
$string['col:message'] = 'Details';
$string['col:time'] = 'Audited';
$string['status:pass'] = 'Pass';
$string['status:fail'] = 'Fail';
$string['status:error'] = 'Error';

// Check names.
$string['check:minimum_words'] = 'Minimum words';
$string['check:allowed_file_types'] = 'Allowed file types';
$string['check:external_verdict'] = 'External verdict';

// Check messages (keys are produced by the domain layer, rendered here).
$string['msg:minwords_pass'] = '{$a->count} words, minimum {$a->minimum}.';
$string['msg:minwords_fail'] = 'Only {$a->count} words, minimum {$a->minimum}.';
$string['msg:filetypes_pass'] = 'All {$a->count} files have an allowed extension.';
$string['msg:filetypes_nofiles'] = 'No files submitted.';
$string['msg:filetypes_fail'] = 'Not allowed: {$a->files}.';
$string['msg:external_message'] = '{$a->message}';
$string['msg:external_error'] = 'The external service could not be reached: {$a->error}';
$string['msg:check_error'] = 'The check failed unexpectedly: {$a->error}';

// Tasks.
$string['task:audit_submission'] = 'Audit one assignment submission';

// Privacy.
$string['privacy:metadata:result'] = 'Outcome of each check run against a student submission.';
$string['privacy:metadata:result:userid'] = 'The student who made the submission.';
$string['privacy:metadata:result:submissionid'] = 'The assignment submission that was checked.';
$string['privacy:metadata:result:checkname'] = 'Which check produced the row.';
$string['privacy:metadata:result:status'] = 'Whether the check passed, failed or errored.';
$string['privacy:metadata:result:score'] = 'Optional numeric score from the check.';
$string['privacy:metadata:result:message'] = 'Human-readable detail about the outcome.';
$string['privacy:metadata:result:timecreated'] = 'When the check ran.';
$string['privacy:metadata:external'] = 'When an external verdict endpoint is configured, the submission is sent to it for analysis.';
$string['privacy:metadata:external:text'] = 'The online text of the submission.';
$string['privacy:metadata:external:filenames'] = 'The names of the submitted files.';
