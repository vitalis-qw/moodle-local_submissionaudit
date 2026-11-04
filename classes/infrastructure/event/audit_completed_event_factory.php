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

namespace local_submissionaudit\infrastructure\event;

use local_submissionaudit\domain\event\audit_completed;
use local_submissionaudit\lib\domain\event\domain_event_interface;

/**
 * Registered in the service provider as the publisher's mapping for audit_completed.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_completed_event_factory {
    /**
     * Invoke the object as a function.
     *
     * @param domain_event_interface $event
     * @return \core\event\base
     */
    public function __invoke(domain_event_interface $event): \core\event\base {
        if (!$event instanceof audit_completed) {
            throw new \coding_exception('audit_completed_event_factory maps audit_completed only');
        }
        $cm = get_coursemodule_from_instance('assign', $event->assignid);
        $context = $cm ? \core\context\module::instance($cm->id) : \core\context\system::instance();
        return \local_submissionaudit\event\audit_completed::create([
            'objectid' => $event->submissionid,
            'relateduserid' => $event->userid,
            'context' => $context,
            'other' => ['failures' => $event->failures],
        ]);
    }
}
