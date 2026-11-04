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

namespace local_submissionaudit\ui\controller;

/**
 * Renders the audit report of an assignment.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\application\dto\audit_report;
use local_submissionaudit\application\security\audit_action;
use local_submissionaudit\application\security\audit_permission_factory_interface;
use local_submissionaudit\application\service\report_service_interface;
use local_submissionaudit\lib\ui\controller\abstract_page_controller;
use local_submissionaudit\lib\ui\request\request_interface;
use local_submissionaudit\lib\ui\response\page_response;
use local_submissionaudit\lib\ui\response\response_interface;
use local_submissionaudit\ui\output\report_page;
use local_submissionaudit\ui\request\report_request;

final class report_controller extends abstract_page_controller implements report_controller_interface {
    /**
     * @param report_service_interface $reports
     * @param audit_permission_factory_interface $permissions
     */
    public function __construct(
        private readonly report_service_interface $reports,
        private readonly audit_permission_factory_interface $permissions,
    ) {
    }

    public function handle(request_interface $request): response_interface {
        global $USER, $PAGE, $OUTPUT;
        if (!$request instanceof report_request) {
            throw new \coding_exception('report_controller expects a report_request');
        }
        [$course, $cm] = get_course_and_cm_from_cmid($request->id, 'assign');
        $context = \core\context\module::instance((int) $cm->id);
        $title = get_string('reporttitle', 'local_submissionaudit', format_string($cm->name));

        $this->setup_page(
            new \moodle_url('/local/submissionaudit/report.php', $request->to_array()),
            $context,
            $title,
            'incourse',
            $course,
            $cm,
        );
        $PAGE->set_cm($cm, $course);
        $PAGE->set_heading(format_string($course->fullname));

        $this->guard(fn() => $this->permissions->for_module((int) $cm->id, (int) $USER->id)->require(audit_action::view_report()));

        $reports = $this->reports->for_assignment((int) $cm->instance);
        $names = $this->reports->student_names(array_map(static fn(audit_report $r): int => $r->userid, $reports));

        $page = new report_page($title, (int) $course->id, $reports, $names);
        return new page_response($OUTPUT->render($page));
    }
}
