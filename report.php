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
 * Audit report for one assignment.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\local\message_formatter;

require(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'assign');
require_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('local/submissionaudit:viewreport', $context);

$url = new moodle_url('/local/submissionaudit/report.php', ['id' => $cm->id]);
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_cm($cm, $course);
$PAGE->set_pagelayout('incourse');
$title = get_string('reporttitle', 'local_submissionaudit', format_string($cm->name));
$PAGE->set_title($title);
$PAGE->set_heading(format_string($course->fullname));

$userfields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
$records = $DB->get_records_sql(
    "SELECT r.id, r.userid, r.checkname, r.status, r.score, r.message, r.timecreated {$userfields}
       FROM {local_submissionaudit_result} r
       JOIN {user} u ON u.id = r.userid
      WHERE r.assignid = :assignid
   ORDER BY r.timecreated DESC, r.userid, r.id",
    ['assignid' => $cm->instance]
);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

if (!$records) {
    echo $OUTPUT->notification(get_string('noresults', 'local_submissionaudit'), 'info');
} else {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable';
    $table->head = [
        get_string('col:student', 'local_submissionaudit'),
        get_string('col:check', 'local_submissionaudit'),
        get_string('col:status', 'local_submissionaudit'),
        get_string('col:score', 'local_submissionaudit'),
        get_string('col:message', 'local_submissionaudit'),
        get_string('col:time', 'local_submissionaudit'),
    ];
    $badges = ['pass' => 'badge-success', 'fail' => 'badge-danger', 'error' => 'badge-warning'];
    foreach ($records as $record) {
        $checkkey = 'check:' . $record->checkname;
        $checkname = get_string_manager()->string_exists($checkkey, 'local_submissionaudit')
            ? get_string($checkkey, 'local_submissionaudit')
            : s($record->checkname);
        $statuskey = 'status:' . $record->status;
        $status = get_string_manager()->string_exists($statuskey, 'local_submissionaudit')
            ? get_string($statuskey, 'local_submissionaudit')
            : s($record->status);
        $table->data[] = [
            html_writer::link(new moodle_url('/user/view.php', ['id' => $record->userid, 'course' => $course->id]), fullname($record)),
            $checkname,
            html_writer::span($status, 'badge ' . ($badges[$record->status] ?? 'badge-secondary')),
            $record->score === null ? '' : format_float((float) $record->score, 2, true, true),
            message_formatter::format($record->message),
            userdate($record->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
