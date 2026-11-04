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
 * column = :value.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\infrastructure\persistence\parameter_bag;

final class equal_filter extends abstract_field_filter {
    /**
     * @param string $field
     * @param int|string|float|bool $value
     * @param bool $negate "<>" instead of "=".
     */
    public function __construct(string $field, private readonly int|string|float|bool $value, private readonly bool $negate = false) {
        parent::__construct($field);
    }

    public function sql(parameter_bag $params, \moodle_database $db): string {
        $name = $params->add($this->value, $this->hint());
        return $this->field . ($this->negate ? ' <> :' : ' = :') . $name;
    }
}
