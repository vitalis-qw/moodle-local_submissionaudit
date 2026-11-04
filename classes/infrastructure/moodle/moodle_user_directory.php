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

use local_submissionaudit\application\port\user_directory_interface;

/**
 * Adapter: user table → display names.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class moodle_user_directory implements user_directory_interface {
    /**
     * Constructor.
     *
     * @param \moodle_database $db
     */
    public function __construct(
        /** @var \moodle_database Db. */
        private readonly \moodle_database $db,
    ) {
    }

    /**
     * Names.
     *
     * @param array $userids Userids.
     * @return array
     */
    public function names(array $userids): array {
        $userids = array_values(array_unique(array_map('intval', $userids)));
        if ($userids === []) {
            return [];
        }
        $fields = 'id, ' . implode(', ', \core_user\fields::for_name()->get_required_fields());
        $names = [];
        foreach ($this->db->get_records_list('user', 'id', $userids, '', $fields) as $user) {
            $names[(int) $user->id] = fullname($user);
        }
        return $names;
    }
}
