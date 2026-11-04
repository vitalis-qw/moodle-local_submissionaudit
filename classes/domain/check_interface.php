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

namespace local_submissionaudit\domain;

/**
 * One rule applied to a submission.
 *
 * Implementations must be pure: no database, no HTTP, no global state. A check
 * that needs the outside world asks for a port (an interface under
 * {@see \local_submissionaudit\domain\port}) in its constructor.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface check_interface {
    /**
     * Stable machine name, at most 50 characters, used as the storage key and
     * as the suffix of the "check:<name>" language string.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Run the check.
     *
     * @param submission $submission
     * @return result
     */
    public function run(submission $submission): result;
}
