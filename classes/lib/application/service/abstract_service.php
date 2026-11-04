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

namespace local_submissionaudit\lib\application\service;

use local_submissionaudit\lib\domain\event\event_publisher_interface;
use local_submissionaudit\lib\domain\model\aggregate_root;

/**
 * An application service orchestrates one use case: load through ports, call the
 * domain, save, publish the events the aggregate recorded. Subclasses add their own
 * dependencies through the constructor and call parent::__construct().
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_service {
    /**
     * Constructor.
     *
     * @param event_publisher_interface $publisher
     */
    public function __construct(
        /** @var event_publisher_interface Publisher. */
        protected readonly event_publisher_interface $publisher,
    ) {
    }

    /**
     * Publish and clear the events recorded on an aggregate. Call after a successful save.
     *
     * @param aggregate_root $root
     */
    final protected function dispatch_events(aggregate_root $root): void {
        $events = $root->pull_events();
        if ($events !== []) {
            $this->publisher->publish(...$events);
        }
    }
}
