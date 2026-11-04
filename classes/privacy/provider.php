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

namespace local_submissionaudit\privacy;

/**
 * Privacy API: audit results are personal data of the student.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_submissionaudit\ui\message_formatter;

final class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {
    /** @var string */
    private const TABLE = 'local_submissionaudit_result';

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(self::TABLE, [
            'userid' => 'privacy:metadata:result:userid',
            'submissionid' => 'privacy:metadata:result:submissionid',
            'checkname' => 'privacy:metadata:result:checkname',
            'status' => 'privacy:metadata:result:status',
            'score' => 'privacy:metadata:result:score',
            'message' => 'privacy:metadata:result:message',
            'timecreated' => 'privacy:metadata:result:timecreated',
        ], 'privacy:metadata:result');
        $collection->add_external_location_link('externalverdict', [
            'text' => 'privacy:metadata:external:text',
            'filenames' => 'privacy:metadata:external:filenames',
        ], 'privacy:metadata:external');
        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {" . self::TABLE . "} r
                  JOIN {course_modules} cm ON cm.instance = r.assignid
                  JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
                  JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :ctxlevel
                 WHERE r.userid = :userid";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, ['ctxlevel' => CONTEXT_MODULE, 'userid' => $userid]);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $sql = "SELECT r.userid
                  FROM {" . self::TABLE . "} r
                  JOIN {course_modules} cm ON cm.instance = r.assignid
                  JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid]);
    }

    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = self::assign_cm($context);
            if (!$cm) {
                continue;
            }
            $records = $DB->get_records(self::TABLE, ['assignid' => $cm->instance, 'userid' => $userid], 'timecreated, id');
            if (!$records) {
                continue;
            }
            $rows = [];
            foreach ($records as $record) {
                $rows[] = (object) [
                    'submissionid' => $record->submissionid,
                    'check' => $record->checkname,
                    'status' => $record->status,
                    'score' => $record->score,
                    'message' => message_formatter::format_stored($record->message),
                    'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
                ];
            }
            writer::with_context($context)->export_data(
                [get_string('pluginname', 'local_submissionaudit')],
                (object) ['results' => $rows]
            );
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        $cm = self::assign_cm($context);
        if ($cm) {
            $DB->delete_records(self::TABLE, ['assignid' => $cm->instance]);
        }
    }

    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = self::assign_cm($context);
            if ($cm) {
                $DB->delete_records(self::TABLE, ['assignid' => $cm->instance, 'userid' => $userid]);
            }
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $cm = self::assign_cm($userlist->get_context());
        $userids = $userlist->get_userids();
        if (!$cm || !$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['assignid'] = $cm->instance;
        $DB->delete_records_select(self::TABLE, "assignid = :assignid AND userid {$insql}", $params);
    }

    /**
     * The assignment course module behind a context, if it is one.
     *
     * @param \context $context
     * @return \stdClass|false
     */
    private static function assign_cm(\context $context) {
        if (!$context instanceof \context_module) {
            return false;
        }
        return get_coursemodule_from_id('assign', $context->instanceid);
    }
}
