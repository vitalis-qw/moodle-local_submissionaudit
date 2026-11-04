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

namespace local_submissionaudit\lib\domain\event;

/**
 * Base class for domain events.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Readonly base: subclasses add promoted constructor properties and implement name()
 * and payload().
 */
abstract readonly class domain_event implements domain_event_interface {
    /**
     * @param int $occurredat Unix timestamp supplied by the caller (keeps the domain clock-free).
     */
    public function __construct(
        private int $occurredat,
    ) {
    }

    public function occurred_at(): int {
        return $this->occurredat;
    }
}
