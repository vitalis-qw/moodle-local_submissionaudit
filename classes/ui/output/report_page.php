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

namespace local_submissionaudit\ui\output;

use local_submissionaudit\application\dto\audit_report;
use local_submissionaudit\application\dto\check_result;
use local_submissionaudit\ui\message_formatter;

/**
 * Rendered with templates/report_page.mustache.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_page implements \renderable, \templatable {
    /** @var array */
    /** @var array Badges. */
    private const BADGES = ['pass' => 'badge bg-success', 'fail' => 'badge bg-danger', 'error' => 'badge bg-warning text-dark'];

    /**
     * Constructor.
     *
     * @param string $title
     * @param int $courseid
     * @param audit_report[] $reports
     * @param array $names userid => display name.
     */
    public function __construct(
        /** @var string Title. */
        private readonly string $title,
        /** @var int Courseid. */
        private readonly int $courseid,
        /** @var audit_report[] Reports. */
        private readonly array $reports,
        /** @var array Names. */
        private readonly array $names,
    ) {
    }

    /**
     * Export for template.
     *
     * @param \renderer_base $output Output.
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output): \stdClass {
        $rows = [];
        foreach ($this->reports as $report) {
            foreach ($report->results as $result) {
                $rows[] = $this->row($report, $result);
            }
        }
        return (object) [
            'title' => $this->title,
            'hasrows' => $rows !== [],
            'rows' => $rows,
            'noresults' => get_string('noresults', 'local_submissionaudit'),
        ];
    }

    /**
     * Row.
     *
     * @param audit_report $report
     * @param check_result $result
     * @return \stdClass
     */
    private function row(audit_report $report, check_result $result): \stdClass {
        return (object) [
            'studentname' => $this->names[$report->userid] ?? get_string('unknownuser'),
            'studenturl' => (new \moodle_url('/user/view.php', ['id' => $report->userid, 'course' => $this->courseid]))->out(false),
            'check' => message_formatter::label('check', $result->checkname),
            'status' => message_formatter::label('status', $result->status),
            'badge' => self::BADGES[$result->status] ?? 'badge bg-secondary',
            'score' => $result->score === null ? '' : format_float($result->score, 2, true, true),
            'message' => message_formatter::format($result->messagekey, $result->messageparams),
            'time' => userdate($report->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        ];
    }
}
