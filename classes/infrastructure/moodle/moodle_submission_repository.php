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

namespace local_submissionaudit\infrastructure\moodle;

use local_submissionaudit\application\factory\submission_factory_interface;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\port\submission_repository_interface;

/**
 * Adapter: assign_submission + onlinetext + file area → submission.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class moodle_submission_repository implements submission_repository_interface {
    /**
     * Constructor.
     *
     * @param \moodle_database $db
     * @param submission_factory_interface $factory
     */
    public function __construct(
        /** @var \moodle_database Db. */
        private readonly \moodle_database $db,
        /** @var submission_factory_interface Factory. */
        private readonly submission_factory_interface $factory,
    ) {
    }

    /**
     * Find.
     *
     * @param int $submissionid Submissionid.
     * @return ?submission
     */
    public function find(int $submissionid): ?submission {
        $record = $this->db->get_record('assign_submission', ['id' => $submissionid]);
        if (!$record) {
            return null;
        }
        $cm = get_coursemodule_from_instance('assign', $record->assignment);
        if (!$cm) {
            return null;
        }
        $context = \core\context\module::instance($cm->id);
        $text = (string) $this->db->get_field('assignsubmission_onlinetext', 'onlinetext', ['submission' => $record->id]);
        $filenames = [];
        $files = get_file_storage()->get_area_files(
            $context->id,
            'assignsubmission_file',
            'submission_files',
            $record->id,
            'filename',
            false,
        );
        foreach ($files as $file) {
            $filenames[] = $file->get_filename();
        }
        return $this->factory->create((int) $record->id, (int) $record->assignment, (int) $record->userid, $text, $filenames);
    }
}
