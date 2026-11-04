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

namespace local_submissionaudit\lib\testing;

use local_submissionaudit\lib\domain\event\domain_event_interface;
use local_submissionaudit\lib\domain\event\event_publisher_interface;

/**
 * Collects published events for assertions.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class in_memory_event_publisher implements event_publisher_interface {
    /** @var domain_event_interface[] */
    private array $published = [];

    /**
     * Publish.
     *
     * @param domain_event_interface... $events Events.
     */
    public function publish(domain_event_interface ...$events): void {
        foreach ($events as $event) {
            $this->published[] = $event;
        }
    }

    /**
     * Published.
     *
     * @return domain_event_interface[]
     */
    public function published(): array {
        return $this->published;
    }

    /**
     * Of type.
     *
     * @param class-string<domain_event_interface> $class
     * @return domain_event_interface[]
     */
    public function of_type(string $class): array {
        return array_values(array_filter($this->published, static fn(domain_event_interface $e): bool => $e instanceof $class));
    }
}
