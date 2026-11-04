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

/**
 * Tests for the audit use case with in-memory doubles.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\application;

require_once(__DIR__ . '/../fixtures/fixtures.php');

use local_submissionaudit\application\dto\audit_report;
use local_submissionaudit\application\dto\audit_request;
use local_submissionaudit\application\factory\audit_factory;
use local_submissionaudit\application\mapper\audit_mapper;
use local_submissionaudit\application\service\audit_service;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\event\audit_completed;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\service\auditor;
use local_submissionaudit\fixtures\fake_check_factory;
use local_submissionaudit\fixtures\fake_submission_repository;
use local_submissionaudit\fixtures\fixed_clock;
use local_submissionaudit\fixtures\in_memory_audit_repository;
use local_submissionaudit\lib\testing\in_memory_event_publisher;

/**
 * @covers \local_submissionaudit\application\service\audit_service
 * @covers \local_submissionaudit\application\mapper\audit_mapper
 * @covers \local_submissionaudit\application\factory\audit_factory
 */
final class audit_service_test extends \basic_testcase {
    private in_memory_audit_repository $audits;
    private in_memory_event_publisher $events;
    private fake_submission_repository $submissions;

    protected function setUp(): void {
        parent::setUp();
        $this->audits = new in_memory_audit_repository();
        $this->events = new in_memory_event_publisher();
        $this->submissions = new fake_submission_repository();
    }

    private function service(): audit_service {
        return new audit_service(
            $this->submissions,
            new auditor(new fake_check_factory(new minimum_words(3))),
            $this->audits,
            new audit_factory(),
            new audit_mapper(),
            new fixed_clock(1700000000),
            $this->events,
        );
    }

    public function test_run_audits_stores_publishes_and_reports(): void {
        $this->submissions->add(new submission(7, 8, 9, '<p>one two</p>', []));

        $report = $this->service()->run(new audit_request(7));

        $this->assertInstanceOf(audit_report::class, $report);
        $this->assertSame(7, $report->submissionid);
        $this->assertSame(1700000000, $report->timecreated);
        $this->assertSame(1, $report->failures);
        $this->assertCount(1, $report->results);
        $this->assertSame('fail', $report->results[0]->status);
        $this->assertSame(['count' => 2, 'minimum' => 3], $report->results[0]->messageparams);
        $this->assertSame([
            'submissionid' => 7, 'assignid' => 8, 'userid' => 9, 'timecreated' => 1700000000,
            'results' => [['checkname' => 'minimum_words', 'status' => 'fail', 'score' => 2.0,
                'messagekey' => 'msg:minwords_fail', 'messageparams' => ['count' => 2, 'minimum' => 3]]],
            'failures' => 1,
        ], $report->to_array());

        $this->assertNotNull($this->audits->find(7));
        $this->assertFalse($this->audits->get(7)->has_events(), 'events were dispatched and cleared');
        $this->assertCount(1, $this->events->of_type(audit_completed::class));
    }

    public function test_run_returns_null_for_a_missing_submission(): void {
        $this->assertNull($this->service()->run(new audit_request(404)));
        $this->assertSame([], $this->audits->all());
        $this->assertSame([], $this->events->published());
    }
}
