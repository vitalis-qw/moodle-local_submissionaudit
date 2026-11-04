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
 * What an external analysis service answers about a submission.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class verdict {
    /**
     * @param bool $passed Whether the service considers the submission acceptable.
     * @param float|null $score Optional score the service reported.
     * @param string $message Free-text explanation from the service.
     */
    public function __construct(
        public readonly bool $passed,
        public readonly ?float $score,
        public readonly string $message,
    ) {
    }
}
