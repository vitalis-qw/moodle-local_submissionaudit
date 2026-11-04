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

use local_submissionaudit\domain\port\submission_loader_interface;
use local_submissionaudit\domain\submission;

/**
 * Adapter: builds a domain submission from assign_submission, the onlinetext
 * sub-plugin and the file area of the file sub-plugin.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class moodle_submission_loader implements submission_loader_interface {
    /**
     * {@inheritDoc}
     */
    public function load(int $submissionid): ?submission {
        global $DB;

        $record = $DB->get_record('assign_submission', ['id' => $submissionid]);
        if (!$record) {
            return null;
        }
        $cm = get_coursemodule_from_instance('assign', $record->assignment);
        if (!$cm) {
            return null;
        }
        $context = \context_module::instance($cm->id);

        $text = (string) $DB->get_field('assignsubmission_onlinetext', 'onlinetext', ['submission' => $record->id]);

        $filenames = [];
        $files = get_file_storage()->get_area_files(
            $context->id,
            'assignsubmission_file',
            'submission_files',
            $record->id,
            'filename',
            false
        );
        foreach ($files as $file) {
            $filenames[] = $file->get_filename();
        }

        return new submission((int) $record->id, (int) $record->assignment, (int) $record->userid, $text, $filenames);
    }
}
