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

namespace local_submissionaudit\lib\application\security;

/**
 * What a user wants to do to a resource.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\domain\model\value_object_interface;

/**
 * Value object: "view@audit_report". Plugins declare their actions as constants or
 * static constructors on a final class.
 */
final readonly class action implements value_object_interface, \Stringable {
    /**
     * @param string $name Verb, e.g. "view", "run".
     * @param string $resource Resource type, e.g. "audit_report".
     */
    public function __construct(
        public string $name,
        public string $resource,
    ) {
        if ($name === '' || $resource === '') {
            throw new \InvalidArgumentException('An action needs both a name and a resource');
        }
    }

    public function equals(value_object_interface $other): bool {
        return $other instanceof self && $other->name === $this->name && $other->resource === $this->resource;
    }

    public function __toString(): string {
        return "{$this->name}@{$this->resource}";
    }
}
