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

namespace local_submissionaudit\lib\domain\model;

/**
 * Entity that records domain events.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\domain\event\domain_event_interface;

/**
 * The entry point of a consistency boundary. Domain events are recorded here while the
 * aggregate changes and pulled by the application service after the repository saved it.
 */
abstract class aggregate_root implements entity_interface {
    /** @var domain_event_interface[] */
    private array $events = [];

    /**
     * Record an event that the application layer publishes after a successful save.
     *
     * @param domain_event_interface $event
     */
    final protected function record(domain_event_interface $event): void {
        $this->events[] = $event;
    }

    /**
     * Return the recorded events and clear them.
     *
     * @return domain_event_interface[]
     */
    final public function pull_events(): array {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    /**
     * @return bool
     */
    final public function has_events(): bool {
        return $this->events !== [];
    }

    /**
     * Move the events of another instance onto this one. Repositories use it when they
     * return a freshly loaded copy after save().
     *
     * @param aggregate_root $source
     */
    final public function adopt_events(aggregate_root $source): void {
        foreach ($source->pull_events() as $event) {
            $this->events[] = $event;
        }
    }
}
