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

namespace local_submissionaudit\task;

use local_submissionaudit\adapter\check_factory;
use local_submissionaudit\adapter\db_result_store;
use local_submissionaudit\adapter\moodle_submission_loader;
use local_submissionaudit\domain\audit_runner;
use local_submissionaudit\domain\auditor;

/**
 * Ad-hoc task: audit one submission in the background.
 *
 * Thin by design: wires adapters to the domain and hands over.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_submission extends \core\task\adhoc_task {
    /**
     * Task name shown in the admin UI.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:audit_submission', 'local_submissionaudit');
    }

    /**
     * Run the audit.
     */
    public function execute(): void {
        if (!get_config('local_submissionaudit', 'enabled')) {
            return;
        }
        $data = $this->get_custom_data();
        $submissionid = (int) ($data->submissionid ?? 0);
        if ($submissionid <= 0) {
            return;
        }

        $runner = new audit_runner(
            new moodle_submission_loader(),
            new auditor(check_factory::from_config()),
            new db_result_store()
        );
        $results = $runner->run($submissionid);
        mtrace(sprintf('local_submissionaudit: submission %d, %d check(s) stored', $submissionid, count($results)));
    }
}
