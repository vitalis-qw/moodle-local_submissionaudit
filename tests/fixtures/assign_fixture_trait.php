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

/**
 * Creates a course, an assignment and a submission.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\fixtures;

/**
 * For advanced_testcase subclasses.
 */
trait assign_fixture_trait {
    /**
     * @param string $text Online text.
     * @param string[] $filenames Files to put in the submission file area.
     * @return array{0: \stdClass, 1: \stdClass, 2: \stdClass, 3: \stdClass} [course, assign, submission record, student]
     */
    private function create_submission(string $text, array $filenames = []): array {
        global $DB;

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $student = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $assign = $generator->create_module('assign', [
            'course' => $course->id,
            'assignsubmission_onlinetext_enabled' => 1,
            'assignsubmission_file_enabled' => 1,
            'assignsubmission_file_maxfiles' => 5,
            'assignsubmission_file_maxsizebytes' => 1048576,
        ]);

        $now = time();
        $submissionid = $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $student->id,
            'status' => 'submitted',
            'attemptnumber' => 0,
            'latest' => 1,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $DB->insert_record('assignsubmission_onlinetext', (object) [
            'assignment' => $assign->id,
            'submission' => $submissionid,
            'onlinetext' => $text,
            'onlineformat' => FORMAT_HTML,
        ]);
        $context = \core\context\module::instance($assign->cmid);
        foreach ($filenames as $filename) {
            get_file_storage()->create_file_from_string([
                'contextid' => $context->id,
                'component' => 'assignsubmission_file',
                'filearea' => 'submission_files',
                'itemid' => $submissionid,
                'filepath' => '/',
                'filename' => $filename,
            ], 'content of ' . $filename);
        }
        $submission = $DB->get_record('assign_submission', ['id' => $submissionid]);

        return [$course, $assign, $submission, $student];
    }
}
