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
 * Validates the column name so a filter can never carry injected SQL.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_field_filter implements filter_interface {
    /**
     * Constructor.
     *
     * @param string $field Column, optionally alias-qualified ("a.userid").
     */
    public function __construct(
        /** @var string Column, optionally alias-qualified ("a.userid"). */
        protected readonly string $field,
    ) {
        if (!preg_match('/^[a-z_][a-z0-9_]*(\.[a-z_][a-z0-9_]*)?$/i', $field)) {
            throw new \InvalidArgumentException("Invalid column name '{$field}'");
        }
    }

    /**
     * Hint.
     *
     * @return string
     */
    protected function hint(): string {
        return str_replace('.', '_', $this->field);
    }
}
