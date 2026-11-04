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

namespace local_submissionaudit\domain\event;

use local_submissionaudit\lib\domain\event\domain_event;

/**
 * Domain event: a submission has been audited.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_completed extends domain_event {
    /**
     * Constructor.
     *
     * @param int $occurredat
     * @param int $submissionid
     * @param int $assignid
     * @param int $userid
     * @param int $failures Number of failing checks.
     */
    public function __construct(
        int $occurredat,
        /** @var int Submissionid. */
        public readonly int $submissionid,
        /** @var int Assignid. */
        public readonly int $assignid,
        /** @var int Userid. */
        public readonly int $userid,
        /** @var int Number of failing checks. */
        public readonly int $failures,
    ) {
        parent::__construct($occurredat);
    }

    /**
     * Name.
     *
     * @return string
     */
    public function name(): string {
        return 'audit_completed';
    }

    /**
     * Payload.
     *
     * @return array
     */
    public function payload(): array {
        return [
            'submissionid' => $this->submissionid,
            'assignid' => $this->assignid,
            'userid' => $this->userid,
            'failures' => $this->failures,
        ];
    }
}
