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

namespace local_submissionaudit\lib\infrastructure\event;

use local_submissionaudit\lib\domain\event\domain_event_interface;
use local_submissionaudit\lib\domain\event\event_publisher_interface;

/**
 * The map says how each domain event becomes a Moodle event:
 *
 *     new moodle_event_publisher([
 *         audit_completed::class => fn(audit_completed $e): \core\event\base =>
 *             \local_example\event\audit_completed::create([...]),
 *     ])
 *
 * An unmapped event is a programming error, not something to swallow.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class moodle_event_publisher implements event_publisher_interface {
    /**
     * Constructor.
     *
     * @param array<class-string<domain_event_interface>, callable(domain_event_interface): \core\event\base> $map
     */
    public function __construct(
        /** @var array Map. */
        private readonly array $map,
    ) {
    }

    /**
     * Publish.
     *
     * @param domain_event_interface... $events Events.
     */
    public function publish(domain_event_interface ...$events): void {
        foreach ($events as $event) {
            $factory = $this->map[$event::class] ?? null;
            if ($factory === null) {
                throw new \coding_exception('No Moodle event mapped for domain event ' . $event::class);
            }
            $factory($event)->trigger();
        }
    }
}
