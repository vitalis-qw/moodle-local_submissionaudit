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

namespace local_submissionaudit\application\service;

use local_submissionaudit\application\mapper\audit_mapper_interface;
use local_submissionaudit\application\port\user_directory_interface;
use local_submissionaudit\domain\port\audit_repository_interface;

/**
 * Use case: the audit report of an assignment.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_service implements report_service_interface {
    /**
     * Constructor.
     *
     * @param audit_repository_interface $audits
     * @param audit_mapper_interface $mapper
     * @param user_directory_interface $users
     */
    public function __construct(
        /** @var audit_repository_interface Audits. */
        private readonly audit_repository_interface $audits,
        /** @var audit_mapper_interface Mapper. */
        private readonly audit_mapper_interface $mapper,
        /** @var user_directory_interface Users. */
        private readonly user_directory_interface $users,
    ) {
    }

    /**
     * For assignment.
     *
     * @param int $assignid Assignid.
     * @return array
     */
    public function for_assignment(int $assignid): array {
        return $this->mapper->to_dto_collection($this->audits->find_by_assignment($assignid));
    }

    /**
     * Student names.
     *
     * @param array $userids Userids.
     * @return array
     */
    public function student_names(array $userids): array {
        return $this->users->names($userids);
    }
}
