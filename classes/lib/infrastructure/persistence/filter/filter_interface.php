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

namespace local_submissionaudit\lib\infrastructure\persistence\filter;

/**
 * A WHERE fragment.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\infrastructure\persistence\parameter_bag;

/**
 * Returns a condition (without WHERE) and registers its values in the bag.
 * An empty string means "no condition".
 */
interface filter_interface {
    /**
     * @param parameter_bag $params
     * @param \moodle_database $db For dialect-specific SQL such as sql_like().
     * @return string
     */
    public function sql(parameter_bag $params, \moodle_database $db): string;
}
