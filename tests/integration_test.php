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

declare(strict_types=1);

namespace local_submissionaudit;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/fixtures.php');

use local_submissionaudit\application\service\audit_service_interface;
use local_submissionaudit\application\service\report_service_interface;
use local_submissionaudit\domain\port\verdict_client_interface;
use local_submissionaudit\fixtures\assign_fixture_trait;
use local_submissionaudit\infrastructure\persistence\db_audit_repository;
use local_submissionaudit\task\audit_submission;
use local_submissionaudit\ui\controller\report_controller_interface;
use local_submissionaudit\ui\output\report_page;
use local_submissionaudit\ui\request\report_request;

/**
 * Observer, task, container and report page working together.
 *
 * @covers \local_submissionaudit\observer
 * @covers \local_submissionaudit\task\audit_submission
 * @covers \local_submissionaudit\service_provider
 * @covers \local_submissionaudit\infrastructure\event\audit_completed_event_factory
 * @covers \local_submissionaudit\event\audit_completed
 * @covers \local_submissionaudit\ui\controller\report_controller
 * @covers \local_submissionaudit\ui\output\report_page
 * @covers \local_submissionaudit\ui\request\report_request
 * @covers \local_submissionaudit\ui\message_formatter
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class integration_test extends \advanced_testcase {
    use assign_fixture_trait;

    /**
     * Test: container resolves the use cases.
     */
    public function test_container_resolves_the_use_cases(): void {
        $this->resetAfterTest();
        $this->assertInstanceOf(audit_service_interface::class, \core\di::get(audit_service_interface::class));
        $this->assertInstanceOf(report_service_interface::class, \core\di::get(report_service_interface::class));
        $this->assertInstanceOf(report_controller_interface::class, \core\di::get(report_controller_interface::class));
        $this->assertInstanceOf(verdict_client_interface::class, \core\di::get(verdict_client_interface::class));
    }

    /**
     * Test: event queues an adhoc task.
     */
    public function test_event_queues_an_adhoc_task(): void {
        $this->resetAfterTest();
        [, $assign, $submission, $student] = $this->create_submission('x');
        $context = \core\context\module::instance($assign->cmid);

        $event = \mod_assign\event\assessable_submitted::create([
            'context' => $context,
            'objectid' => $submission->id,
            'relateduserid' => $student->id,
            'other' => ['submission_editable' => false],
        ]);
        $event->trigger();

        $tasks = \core\task\manager::get_adhoc_tasks(audit_submission::class);
        $this->assertCount(1, $tasks);
        $this->assertSame((int) $submission->id, (int) reset($tasks)->get_custom_data()->submissionid);
    }

    /**
     * Test: task stores results and fires the event.
     */
    public function test_task_stores_results_and_fires_the_event(): void {
        global $DB;
        $this->resetAfterTest();
        $this->expectOutputRegex('/submission \d+, 2 check\(s\) stored/');
        set_config('minwords', '3', 'local_submissionaudit');
        set_config('allowedfiletypes', 'pdf', 'local_submissionaudit');
        [, $assign, $submission] = $this->create_submission('<p>one two</p>', ['a.exe']);
        $sink = $this->redirectEvents();

        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => (int) $submission->id]);
        $task->execute();

        $rows = array_values($DB->get_records(db_audit_repository::TABLE, ['submissionid' => $submission->id], 'id'));
        $this->assertCount(2, $rows);
        $this->assertSame('fail', $rows[0]->status);
        $this->assertSame('allowed_file_types', $rows[1]->checkname);

        $events = array_values(array_filter($sink->get_events(), static fn($e) => $e instanceof event\audit_completed));
        $this->assertCount(1, $events);
        $this->assertSame((int) $submission->id, (int) $events[0]->objectid);
        $this->assertSame(2, $events[0]->other['failures']);
        $this->assertSame((int) $assign->cmid, (int) $events[0]->contextinstanceid);
        $sink->close();
    }

    /**
     * Test: task is a noop when disabled or submission missing.
     */
    public function test_task_is_a_noop_when_disabled_or_submission_missing(): void {
        global $DB;
        $this->resetAfterTest();
        $this->expectOutputRegex('/submission \d+, 0 check\(s\) stored/');
        set_config('minwords', '3', 'local_submissionaudit');
        [, , $submission] = $this->create_submission('x');

        set_config('enabled', '0', 'local_submissionaudit');
        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => (int) $submission->id]);
        $task->execute();
        $this->assertSame(0, $DB->count_records(db_audit_repository::TABLE));

        set_config('enabled', '1', 'local_submissionaudit');
        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => 999999]);
        $task->execute();
        $this->assertSame(0, $DB->count_records(db_audit_repository::TABLE));
    }

    /**
     * Test: report page renders rows for a tutor and denies a student.
     */
    public function test_report_page_renders_rows_for_a_tutor_and_denies_a_student(): void {
        $this->resetAfterTest();
        set_config('minwords', '3', 'local_submissionaudit');
        [$course, $assign, $submission, $student] = $this->create_submission('<p>one two</p>');
        $teacher = $this->getDataGenerator()->create_user(['firstname' => 'Tina', 'lastname' => 'Tutor']);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => (int) $submission->id]);
        $this->expectOutputRegex('/1 check\(s\) stored/');
        $task->execute();

        $this->setUser($teacher);
        $request = report_request::from_array(['id' => (string) $assign->cmid]);
        $this->assertSame(['id' => (int) $assign->cmid], $request->to_array());

        $response = \core\di::get(report_controller_interface::class)->handle($request);
        $html = (string) $this->export_body($response);
        $this->assertStringContainsString(fullname($student), $html);
        $this->assertStringContainsString('Minimum words', $html);
        $this->assertStringContainsString('Only 2 words, minimum 3.', $html);
        $this->assertStringContainsString('bg-danger', $html);

        $this->setUser($student);
        $this->expectException(\moodle_exception::class);
        \core\di::get(report_controller_interface::class)->handle($request);
    }

    /**
     * Test: report page without results.
     */
    public function test_report_page_without_results(): void {
        global $PAGE;
        $this->resetAfterTest();
        $output = $PAGE->get_renderer('core');
        $page = new report_page('Title', 1, [], []);
        $data = $page->export_for_template($output);
        $this->assertFalse($data->hasrows);
        $this->assertStringContainsString('No audited submissions yet', $output->render($page));
    }

    /**
     * The rendered body of a page response, without the theme header and footer.
     *
     * @param object $response
     * @return string
     */
    private function export_body(object $response): string {
        $property = new \ReflectionProperty($response, 'body');
        return (string) $property->getValue($response);
    }
}
