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

use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\submission;

/**
 * Builds audits.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_factory implements audit_factory_interface {
    /**
     * Completed.
     *
     * @param submission $submission Submission.
     * @param array $results Results.
     * @param int $now Now.
     * @return audit
     */
    public function completed(submission $submission, array $results, int $now): audit {
        return audit::completed($submission, $results, $now);
    }

    /**
     * Restore.
     *
     * @param int $submissionid Submissionid.
     * @param int $assignid Assignid.
     * @param int $userid Userid.
     * @param array $results Results.
     * @param int $timecreated Timecreated.
     * @return audit
     */
    public function restore(int $submissionid, int $assignid, int $userid, array $results, int $timecreated): audit {
        return audit::restore($submissionid, $assignid, $userid, $results, $timecreated);
    }
}
