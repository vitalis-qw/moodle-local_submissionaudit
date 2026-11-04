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
 * Tests for the audit aggregate and the auditor.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\domain;

require_once(__DIR__ . '/../fixtures/fixtures.php');

use local_submissionaudit\domain\check\check_interface;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\event\audit_completed;
use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\check_status;
use local_submissionaudit\domain\model\result;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\service\auditor;
use local_submissionaudit\fixtures\fake_check_factory;

/**
 * @covers \local_submissionaudit\domain\model\audit
 * @covers \local_submissionaudit\domain\event\audit_completed
 * @covers \local_submissionaudit\domain\service\auditor
 */
final class audit_test extends \basic_testcase {
    public function test_completed_audit_records_event_and_counts_failures(): void {
        $submission = new submission(10, 20, 30, 'one two', []);
        $results = [result::fail('a', 'k'), result::pass('b', 'k'), result::error('c', 'k')];

        $audit = audit::completed($submission, $results, 1700000000);

        $this->assertSame(10, $audit->get_id());
        $this->assertSame(20, $audit->assignid());
        $this->assertSame(30, $audit->userid());
        $this->assertSame(1700000000, $audit->timecreated());
        $this->assertSame(1, $audit->failure_count());
        $this->assertTrue($audit->has_failures());

        $events = $audit->pull_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(audit_completed::class, $events[0]);
        $this->assertSame(['submissionid' => 10, 'assignid' => 20, 'userid' => 30, 'failures' => 1], $events[0]->payload());
    }

    public function test_restored_audit_records_nothing(): void {
        $audit = audit::restore(1, 2, 3, [result::pass('a', 'k')], 5);
        $this->assertFalse($audit->has_events());
        $this->assertFalse($audit->has_failures());
        $this->expectException(\InvalidArgumentException::class);
        audit::restore(1, 2, 3, ['not a result'], 5);
    }

    public function test_auditor_isolates_a_throwing_check(): void {
        $broken = new class implements check_interface {
            public function name(): string {
                return 'broken';
            }

            public function run(submission $submission): result {
                throw new \RuntimeException('boom');
            }
        };
        $auditor = new auditor(new fake_check_factory(new minimum_words(1), $broken));

        $results = $auditor->audit(new submission(1, 2, 3, 'hello', []));

        $this->assertCount(2, $results);
        $this->assertTrue($results[0]->is_pass());
        $this->assertSame(check_status::ERROR, $results[1]->status);
        $this->assertSame('broken', $results[1]->checkname);
        $this->assertSame(['error' => 'boom'], $results[1]->messageparams);
        $this->assertSame([], (new auditor(new fake_check_factory()))->audit(new submission(1, 2, 3, '', [])));
    }
}
