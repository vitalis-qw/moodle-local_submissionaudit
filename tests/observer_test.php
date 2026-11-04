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

namespace local_submissionaudit;

use local_submissionaudit\adapter\db_result_store;
use local_submissionaudit\task\audit_submission;

/**
 * Integration tests: event to queued task to stored results.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_submissionaudit\observer
 * @covers     \local_submissionaudit\task\audit_submission
 * @covers     \local_submissionaudit\adapter\moodle_submission_loader
 * @covers     \local_submissionaudit\adapter\db_result_store
 * @covers     \local_submissionaudit\adapter\check_factory
 */
final class observer_test extends \advanced_testcase {
    /**
     * Create a course, a student and an assignment with one online-text submission.
     *
     * @param string $text
     * @return array{0: \stdClass, 1: \stdClass, 2: \stdClass} assign instance, submission, student
     */
    private function create_submission(string $text): array {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $assign = $generator->create_module('assign', [
            'course' => $course->id,
            'assignsubmission_onlinetext_enabled' => 1,
        ]);

        $now = time();
        $submissionid = $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $student->id,
            'status' => 'submitted',
            'attemptnumber' => 0,
            'latest' => 1,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $DB->insert_record('assignsubmission_onlinetext', (object) [
            'assignment' => $assign->id,
            'submission' => $submissionid,
            'onlinetext' => $text,
            'onlineformat' => FORMAT_HTML,
        ]);
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);

        return [$assign, $submission, $student];
    }

    /**
     * The submission event queues exactly one audit task carrying the submission id.
     */
    public function test_event_queues_an_adhoc_task(): void {
        $this->resetAfterTest();
        [$assign, $submission, $student] = $this->create_submission('<p>Hello</p>');
        $context = \context_module::instance($assign->cmid);

        $event = \mod_assign\event\assessable_submitted::create([
            'objectid' => $submission->id,
            'context' => $context,
            'relateduserid' => $student->id,
            'other' => ['submission_editable' => false],
        ]);
        $event->trigger();

        $tasks = \core\task\manager::get_adhoc_tasks(audit_submission::class);
        $this->assertCount(1, $tasks);
        $task = reset($tasks);
        $this->assertSame((int) $submission->id, (int) $task->get_custom_data()->submissionid);
    }

    /**
     * Running the task stores one row per configured check.
     */
    public function test_task_stores_results(): void {
        $this->expectOutputRegex('/local_submissionaudit: submission \d+, \d+ check\(s\) stored/');
        global $DB;
        $this->resetAfterTest();
        set_config('enabled', 1, 'local_submissionaudit');
        set_config('minwords', 5, 'local_submissionaudit');
        set_config('allowedfiletypes', 'pdf', 'local_submissionaudit');
        [$assign, $submission, $student] = $this->create_submission('<p>Only three words</p>');

        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => $submission->id]);
        $task->execute();

        $rows = array_values($DB->get_records(db_result_store::TABLE, ['submissionid' => $submission->id], 'id'));
        $this->assertCount(2, $rows);

        $this->assertSame('minimum_words', $rows[0]->checkname);
        $this->assertSame('fail', $rows[0]->status);
        $this->assertSame((int) $assign->id, (int) $rows[0]->assignid);
        $this->assertSame((int) $student->id, (int) $rows[0]->userid);
        $this->assertSame(3.0, (float) $rows[0]->score);
        $message = json_decode($rows[0]->message, true);
        $this->assertSame('msg:minwords_fail', $message['key']);
        $this->assertSame(['count' => 3, 'minimum' => 5], $message['params']);

        $this->assertSame('allowed_file_types', $rows[1]->checkname);
        $this->assertSame('pass', $rows[1]->status);

        // A second run replaces, never duplicates.
        $task->execute();
        $this->assertCount(2, $DB->get_records(db_result_store::TABLE, ['submissionid' => $submission->id]));
    }

    /**
     * A disabled plugin stores nothing; a vanished submission stores nothing.
     */
    public function test_task_is_a_noop_when_disabled_or_submission_missing(): void {
        $this->expectOutputRegex('/local_submissionaudit: submission \d+, \d+ check\(s\) stored/');
        global $DB;
        $this->resetAfterTest();
        set_config('minwords', 5, 'local_submissionaudit');
        [, $submission] = $this->create_submission('x');

        set_config('enabled', 0, 'local_submissionaudit');
        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => $submission->id]);
        $task->execute();
        $this->assertSame(0, $DB->count_records(db_result_store::TABLE));

        set_config('enabled', 1, 'local_submissionaudit');
        $task->set_custom_data(['submissionid' => $submission->id + 1000]);
        $task->execute();
        $this->assertSame(0, $DB->count_records(db_result_store::TABLE));
    }

    /**
     * Stored messages render through the language pack.
     */
    public function test_message_formatter_renders_stored_key(): void {
        $this->resetAfterTest();
        $stored = json_encode(['key' => 'msg:minwords_fail', 'params' => ['count' => 3, 'minimum' => 5]]);
        $this->assertSame('Only 3 words, minimum 5.', \local_submissionaudit\local\message_formatter::format($stored));
        $this->assertSame('plain text', \local_submissionaudit\local\message_formatter::format('plain text'));
        $this->assertSame('', \local_submissionaudit\local\message_formatter::format(null));
    }
}
