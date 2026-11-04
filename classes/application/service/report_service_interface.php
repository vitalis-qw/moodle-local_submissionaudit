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

use local_submissionaudit\application\dto\audit_report;

/**
 * Use case: the audit report of an assignment.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface report_service_interface {
    /**
     * For assignment.
     *
     * @param int $assignid
     * @return audit_report[] Newest first.
     */
    public function for_assignment(int $assignid): array;

    /**
     * Student names.
     *
     * @param int[] $userids
     * @return array
     */
    public function student_names(array $userids): array;
}
