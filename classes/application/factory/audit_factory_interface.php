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

namespace local_submissionaudit\application\factory;

/**
 * Builds audits.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\result;
use local_submissionaudit\domain\model\submission;

interface audit_factory_interface {
    /**
     * A new audit that has just run; it records the audit_completed event.
     *
     * @param submission $submission
     * @param result[] $results
     * @param int $now
     * @return audit
     */
    public function completed(submission $submission, array $results, int $now): audit;

    /**
     * An audit rebuilt from storage; no event.
     *
     * @param int $submissionid
     * @param int $assignid
     * @param int $userid
     * @param result[] $results
     * @param int $timecreated
     * @return audit
     */
    public function restore(int $submissionid, int $assignid, int $userid, array $results, int $timecreated): audit;
}
