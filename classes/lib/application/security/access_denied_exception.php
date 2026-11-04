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

namespace local_submissionaudit\lib\application\security;

/**
 * A user may not perform an action.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Pure PHP. The ui layer turns it into a Moodle "nopermissions" error page.
 */
final class access_denied_exception extends \RuntimeException {
    /**
     * @param action $action
     * @param int $userid
     */
    public function __construct(
        public readonly action $action,
        public readonly int $userid,
    ) {
        parent::__construct(sprintf('User %d may not %s', $userid, (string) $action));
    }
}
