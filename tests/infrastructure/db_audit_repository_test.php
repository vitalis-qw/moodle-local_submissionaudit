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

use local_submissionaudit\application\factory\audit_factory;
use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\check_status;
use local_submissionaudit\domain\model\result;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\infrastructure\persistence\db_audit_repository;
use local_submissionaudit\infrastructure\persistence\result_record_mapper;
use local_submissionaudit\lib\domain\exception\not_found_exception;

/**
 * Tests for the DB audit repository.
 *
 * @covers \local_submissionaudit\infrastructure\persistence\db_audit_repository
 * @covers \local_submissionaudit\infrastructure\persistence\result_record_mapper
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class db_audit_repository_test extends \advanced_testcase {
    /**
     * Repository.
     *
     * @return db_audit_repository
     */
    private function repository(): db_audit_repository {
        global $DB;
        return new db_audit_repository($DB, new result_record_mapper(), new audit_factory());
    }

    /**
     * Test: save replaces find and delete.
     */
    public function test_save_replaces_find_and_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $repo = $this->repository();
        $submission = new submission(5, 6, 7, 'x', []);

        $first = audit::completed($submission, [result::fail('a', 'msg:a', ['n' => 1], 1.5), result::error('b', 'msg:b')], 100);
        $saved = $repo->save($first);
        $this->assertTrue($saved->has_events(), 'save keeps the recorded events for the service to publish');
        $this->assertSame(2, $DB->count_records(db_audit_repository::TABLE, ['submissionid' => 5]));

        $loaded = $repo->get(5);
        $this->assertSame(6, $loaded->assignid());
        $this->assertSame(7, $loaded->userid());
        $this->assertSame(100, $loaded->timecreated());
        $this->assertCount(2, $loaded->results());
        $this->assertSame(check_status::FAIL, $loaded->results()[0]->status);
        $this->assertSame(1.5, $loaded->results()[0]->score);
        $this->assertSame(['n' => 1], $loaded->results()[0]->messageparams);
        $this->assertNull($loaded->results()[1]->score);
        $this->assertFalse($loaded->has_events());

        $repo->save(audit::completed($submission, [result::pass('a', 'msg:a')], 200));
        $this->assertSame(1, $DB->count_records(db_audit_repository::TABLE, ['submissionid' => 5]));
        $this->assertSame(200, $repo->get(5)->timecreated());

        $repo->delete($loaded);
        $this->assertNull($repo->find(5));
        $this->expectException(not_found_exception::class);
        $repo->get(5);
    }

    /**
     * Test: find by assignment groups rows newest first.
     */
    public function test_find_by_assignment_groups_rows_newest_first(): void {
        $this->resetAfterTest();
        $repo = $this->repository();
        $repo->save(audit::restore(1, 40, 11, [result::pass('a', 'k'), result::pass('b', 'k')], 100));
        $repo->save(audit::restore(2, 40, 12, [result::fail('a', 'k')], 300));
        $repo->save(audit::restore(3, 41, 13, [result::pass('a', 'k')], 200));

        $audits = $repo->find_by_assignment(40);

        $this->assertSame([2, 1], array_map(static fn(audit $a): int => $a->submissionid(), $audits));
        $this->assertCount(2, $audits[1]->results());
        $this->assertSame([], $repo->find_by_assignment(99));
    }
}
