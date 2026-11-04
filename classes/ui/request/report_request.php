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

namespace local_submissionaudit\ui\request;

use core\param;
use local_submissionaudit\lib\ui\request\abstract_request;

/**
 * Parameters of the report page.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_request extends abstract_request {
    /**
     * Constructor.
     *
     * @param int $id Course module id of the assignment.
     */
    public function __construct(
        /** @var int Course module id of the assignment. */
        public int $id,
    ) {
    }

    /**
     * Rules.
     *
     * @return array
     */
    protected static function rules(): array {
        return ['id' => param::INT];
    }
}
