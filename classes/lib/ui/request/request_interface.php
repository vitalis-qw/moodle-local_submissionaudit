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

namespace local_submissionaudit\lib\ui\request;

/**
 * Built from an array (testable) or from the globals (one place only).
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface request_interface {
    /**
     * From array.
     *
     * @param array $input Raw values; cleaned by the implementation.
     * @return static
     */
    public static function from_array(array $input): static;

    /**
     * From globals.
     *
     * @return static
     */
    public static function from_globals(): static;

    /**
     * Cleaned values, suitable for moodle_url parameters.
     *
     * @return array
     */
    public function to_array(): array;
}
