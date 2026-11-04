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

namespace local_submissionaudit\task;

use local_submissionaudit\application\dto\audit_request;
use local_submissionaudit\application\service\audit_service_interface;
use local_submissionaudit\infrastructure\config\settings;

/**
 * Thin by design: fetches the use case from the container and hands over.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_submission extends \core\task\adhoc_task {
    /**
     * Get name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:audit_submission', 'local_submissionaudit');
    }

    /**
     * Execute.
     */
    public function execute(): void {
        if (!\core\di::get(settings::class)->enabled()) {
            return;
        }
        $submissionid = (int) ($this->get_custom_data()->submissionid ?? 0);
        if ($submissionid <= 0) {
            return;
        }
        $report = \core\di::get(audit_service_interface::class)->run(new audit_request($submissionid));
        mtrace(sprintf(
            'local_submissionaudit: submission %d, %d check(s) stored',
            $submissionid,
            $report === null ? 0 : count($report->results),
        ));
    }
}
