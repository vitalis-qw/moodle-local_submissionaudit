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

namespace local_submissionaudit\domain;

use local_submissionaudit\domain\port\result_store_interface;
use local_submissionaudit\domain\port\submission_loader_interface;

/**
 * The use case: load a submission, audit it, store the results.
 *
 * Talks to the outside world only through ports, so the whole flow is
 * testable with in-memory fakes.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_runner {
    /**
     * @param submission_loader_interface $loader
     * @param auditor $auditor
     * @param result_store_interface $store
     */
    public function __construct(
        private readonly submission_loader_interface $loader,
        private readonly auditor $auditor,
        private readonly result_store_interface $store,
    ) {
    }

    /**
     * Audit one submission.
     *
     * @param int $submissionid
     * @return result[] The stored results; empty when the submission no longer exists.
     */
    public function run(int $submissionid): array {
        $submission = $this->loader->load($submissionid);
        if ($submission === null) {
            return [];
        }
        $results = $this->auditor->audit($submission);
        $this->store->store($submission, $results);
        return $results;
    }
}
