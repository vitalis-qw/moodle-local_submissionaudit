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

namespace local_submissionaudit\application\dto;

use local_submissionaudit\lib\application\dto\abstract_dto;

/**
 * An audit, as data for the ui and tasks.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_report extends abstract_dto {
    /**
     * Constructor.
     *
     * @param int $submissionid
     * @param int $assignid
     * @param int $userid
     * @param int $timecreated
     * @param check_result[] $results
     * @param int $failures
     */
    public function __construct(
        /** @var int Submissionid. */
        public readonly int $submissionid,
        /** @var int Assignid. */
        public readonly int $assignid,
        /** @var int Userid. */
        public readonly int $userid,
        /** @var int Timecreated. */
        public readonly int $timecreated,
        /** @var check_result[] Results. */
        public readonly array $results,
        /** @var int Failures. */
        public readonly int $failures,
    ) {
    }
}
