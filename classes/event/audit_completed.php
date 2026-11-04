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

namespace local_submissionaudit\event;

/**
 * Moodle event: a submission has been audited.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Fired after the results are stored; logged in the assignment context.
 *
 * @property-read array $other {
 *      int failures Number of failing checks.
 * }
 */
final class audit_completed extends \core\event\base {
    protected function init(): void {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'assign_submission';
    }

    public static function get_name(): string {
        return get_string('event:audit_completed', 'local_submissionaudit');
    }

    public function get_description(): string {
        return "Submission {$this->objectid} of user {$this->relateduserid} was audited: {$this->other['failures']} failing check(s).";
    }

    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->other['failures'])) {
            throw new \coding_exception('The \'failures\' value must be set in other.');
        }
    }
}
