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

use local_submissionaudit\lib\infrastructure\persistence\parameter_bag;

/**
 * column = :value.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class equal_filter extends abstract_field_filter {
    /**
     * Constructor.
     *
     * @param string $field
     * @param int|string|float|bool $value
     * @param bool $negate "<>" instead of "=".
     */
    public function __construct(
        string $field,
        /** @var int|string|float|bool Value. */
        private readonly int|string|float|bool $value,
        /** @var bool "<>" instead of "=". */
        private readonly bool $negate = false,
    ) {
        parent::__construct($field);
    }

    /**
     * Sql.
     *
     * @param parameter_bag $params Params.
     * @param \moodle_database $db Db.
     * @return string
     */
    public function sql(parameter_bag $params, \moodle_database $db): string {
        $name = $params->add($this->value, $this->hint());
        return $this->field . ($this->negate ? ' <> :' : ' = :') . $name;
    }
}
