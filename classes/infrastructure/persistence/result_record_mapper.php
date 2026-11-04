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

namespace local_submissionaudit\infrastructure\persistence;

/**
 * DB record ↔ result value object, with explicit casts.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\check_status;
use local_submissionaudit\domain\model\result;

final class result_record_mapper {
    /**
     * @param \stdClass $record Row of local_submissionaudit_result.
     * @return result
     */
    public function to_result(\stdClass $record): result {
        $message = json_decode((string) $record->message, true);
        return new result(
            (string) $record->checkname,
            check_status::from((string) $record->status),
            $record->score === null ? null : (float) $record->score,
            is_array($message) ? (string) ($message['key'] ?? '') : '',
            is_array($message) && is_array($message['params'] ?? null) ? $message['params'] : [],
        );
    }

    /**
     * @param audit $audit
     * @param result $result
     * @return \stdClass
     */
    public function to_record(audit $audit, result $result): \stdClass {
        return (object) [
            'assignid' => $audit->assignid(),
            'submissionid' => $audit->submissionid(),
            'userid' => $audit->userid(),
            'checkname' => $result->checkname,
            'status' => $result->status->value,
            'score' => $result->score,
            'message' => json_encode(['key' => $result->messagekey, 'params' => $result->messageparams]),
            'timecreated' => $audit->timecreated(),
        ];
    }
}
