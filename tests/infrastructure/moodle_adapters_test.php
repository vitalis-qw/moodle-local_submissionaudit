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

namespace local_submissionaudit\infrastructure;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../fixtures/fixtures.php');

use local_submissionaudit\application\factory\submission_factory;
use local_submissionaudit\application\security\audit_action;
use local_submissionaudit\domain\check\allowed_file_types;
use local_submissionaudit\domain\check\external_verdict;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\model\verdict;
use local_submissionaudit\fixtures\assign_fixture_trait;
use local_submissionaudit\fixtures\fake_verdict_client;
use local_submissionaudit\infrastructure\config\settings;
use local_submissionaudit\infrastructure\moodle\moodle_submission_repository;
use local_submissionaudit\infrastructure\moodle\moodle_user_directory;
use local_submissionaudit\infrastructure\moodle\settings_check_factory;
use local_submissionaudit\infrastructure\security\audit_permission_factory;
use local_submissionaudit\lib\application\security\access_denied_exception;
use local_submissionaudit\lib\infrastructure\config\plugin_config;

/**
 * Tests for the Moodle-facing adapters.
 *
 * @covers \local_submissionaudit\infrastructure\moodle\moodle_submission_repository
 * @covers \local_submissionaudit\infrastructure\moodle\moodle_user_directory
 * @covers \local_submissionaudit\infrastructure\moodle\settings_check_factory
 * @covers \local_submissionaudit\infrastructure\config\settings
 * @covers \local_submissionaudit\infrastructure\security\audit_permission_factory
 * @covers \local_submissionaudit\infrastructure\security\capability_audit_permission
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class moodle_adapters_test extends \advanced_testcase {
    use assign_fixture_trait;

    /**
     * Test: submission repository loads text and files.
     */
    public function test_submission_repository_loads_text_and_files(): void {
        global $DB;
        $this->resetAfterTest();
        [, $assign, $record, $student] = $this->create_submission('<p>hello world</p>', ['essay.pdf', 'notes.txt']);
        $repo = new moodle_submission_repository($DB, new submission_factory());

        $submission = $repo->find((int) $record->id);

        $this->assertNotNull($submission);
        $this->assertSame((int) $record->id, $submission->get_id());
        $this->assertSame((int) $assign->id, $submission->assignid());
        $this->assertSame((int) $student->id, $submission->userid());
        $this->assertSame('hello world', $submission->plain_text());
        $this->assertSame(['essay.pdf', 'notes.txt'], $submission->filenames());
        $this->assertNull($repo->find(999999));
    }

    /**
     * Test: user directory returns full names.
     */
    public function test_user_directory_returns_full_names(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Ann', 'lastname' => 'Tutor']);
        $names = (new moodle_user_directory($this->db()))->names([(int) $user->id, 999999]);
        $this->assertSame([(int) $user->id => 'Ann Tutor'], $names);
        $this->assertSame([], (new moodle_user_directory($this->db()))->names([]));
    }

    /**
     * Test: check factory follows settings.
     */
    public function test_check_factory_follows_settings(): void {
        $this->resetAfterTest();
        $settings = new settings(new plugin_config('local_submissionaudit'));
        $client = new fake_verdict_client(new verdict(true, null, ''));

        $this->assertSame([], (new settings_check_factory($settings, $client))->checks());
        $this->assertTrue($settings->enabled());

        set_config('minwords', '25', 'local_submissionaudit');
        set_config('allowedfiletypes', 'pdf, docx', 'local_submissionaudit');
        set_config('externalurl', 'https://verdict.example.com/check', 'local_submissionaudit');
        set_config('enabled', '0', 'local_submissionaudit');

        $checks = (new settings_check_factory($settings, $client))->checks();
        $this->assertInstanceOf(minimum_words::class, $checks[0]);
        $this->assertInstanceOf(allowed_file_types::class, $checks[1]);
        $this->assertInstanceOf(external_verdict::class, $checks[2]);
        $this->assertFalse($settings->enabled());
        $this->assertSame(['pdf', 'docx'], $settings->allowed_file_types());
    }

    /**
     * Test: permission follows the capability.
     */
    public function test_permission_follows_the_capability(): void {
        $this->resetAfterTest();
        [$course, $assign, , $student] = $this->create_submission('x');
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $factory = new audit_permission_factory();

        $this->assertTrue($factory->for_module((int) $assign->cmid, (int) $teacher->id)->can(audit_action::view_report()));
        $this->assertFalse($factory->for_module((int) $assign->cmid, (int) $student->id)->can(audit_action::view_report()));

        $this->expectException(access_denied_exception::class);
        $factory->for_module((int) $assign->cmid, (int) $student->id)->require(audit_action::view_report());
    }

    /**
     * Db.
     *
     * @return \moodle_database
     */
    private function db(): \moodle_database {
        global $DB;
        return $DB;
    }
}
