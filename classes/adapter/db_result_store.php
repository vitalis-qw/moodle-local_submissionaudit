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

namespace local_submissionaudit\adapter;

use local_submissionaudit\domain\port\result_store_interface;
use local_submissionaudit\domain\submission;

/**
 * Adapter: persists results in local_submissionaudit_result.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class db_result_store implements result_store_interface {
    /** @var string */
    public const TABLE = 'local_submissionaudit_result';

    /**
     * {@inheritDoc}
     */
    public function store(submission $submission, array $results): void {
        global $DB;

        $now = time();
        $rows = [];
        foreach ($results as $result) {
            $rows[] = (object) [
                'assignid' => $submission->assignid,
                'submissionid' => $submission->id,
                'userid' => $submission->userid,
                'checkname' => $result->checkname,
                'status' => $result->status,
                'score' => $result->score,
                'message' => json_encode(['key' => $result->messagekey, 'params' => $result->messageparams]),
                'timecreated' => $now,
            ];
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(self::TABLE, ['submissionid' => $submission->id]);
        if ($rows !== []) {
            $DB->insert_records(self::TABLE, $rows);
        }
        $transaction->allow_commit();
    }
}
