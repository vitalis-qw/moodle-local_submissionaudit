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
 * Tests for the report use case.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\application;

require_once(__DIR__ . '/../fixtures/fixtures.php');

use local_submissionaudit\application\mapper\audit_mapper;
use local_submissionaudit\application\service\report_service;
use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\result;
use local_submissionaudit\fixtures\fake_user_directory;
use local_submissionaudit\fixtures\in_memory_audit_repository;

/**
 * @covers \local_submissionaudit\application\service\report_service
 */
final class report_service_test extends \basic_testcase {
    public function test_for_assignment_is_newest_first_and_names_resolve(): void {
        $audits = new in_memory_audit_repository();
        $audits->save(audit::restore(1, 50, 11, [result::pass('a', 'k')], 100));
        $audits->save(audit::restore(2, 50, 12, [result::fail('a', 'k')], 200));
        $audits->save(audit::restore(3, 99, 13, [], 300));
        $service = new report_service($audits, new audit_mapper(), new fake_user_directory([11 => 'Ann', 12 => 'Bob']));

        $reports = $service->for_assignment(50);

        $this->assertSame([2, 1], array_map(static fn($r) => $r->submissionid, $reports));
        $this->assertSame([11 => 'Ann', 12 => 'Bob'], $service->student_names([11, 12, 13]));
    }
}
