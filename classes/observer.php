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
 * Event observer: queues an audit, never runs one inline.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit;

use local_submissionaudit\task\audit_submission;

/**
 * The submission request must stay fast, and a failing external service must not
 * surface as an error to the student.
 */
final class observer {
    /**
     * @param \mod_assign\event\assessable_submitted $event
     */
    public static function assessable_submitted(\mod_assign\event\assessable_submitted $event): void {
        $task = new audit_submission();
        $task->set_custom_data(['submissionid' => (int) $event->objectid]);
        $task->set_component('local_submissionaudit');
        \core\task\manager::queue_adhoc_task($task, true);
    }
}
