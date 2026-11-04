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
 * Subclasses implement can() for the actions of one resource type. Unknown actions deny.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_permission implements permission_interface {
    /**
     * Constructor.
     *
     * @param int $userid The user the decision is about.
     */
    public function __construct(
        /** @var int The user the decision is about. */
        protected readonly int $userid,
    ) {
    }

    /**
     * Require.
     *
     * @param action $action Action.
     * @param mixed $subject Subject.
     */
    final public function require(action $action, mixed $subject = null): void {
        if (!$this->can($action, $subject)) {
            throw new access_denied_exception($action, $this->userid);
        }
    }

    /**
     * Get userid.
     *
     * @return int
     */
    final public function get_userid(): int {
        return $this->userid;
    }
}
