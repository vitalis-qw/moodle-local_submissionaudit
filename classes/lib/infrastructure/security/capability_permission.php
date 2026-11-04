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

namespace local_submissionaudit\lib\infrastructure\security;

/**
 * Permission backed by Moodle capabilities.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\application\security\abstract_permission;

/**
 * Subclasses map actions to capability checks in can(); has() wraps has_capability()
 * for the permission's user and context.
 */
abstract class capability_permission extends abstract_permission {
    /**
     * @param int $userid
     * @param \core\context $context
     */
    public function __construct(int $userid, protected readonly \core\context $context) {
        parent::__construct($userid);
    }

    /**
     * @param string $capability
     * @param \core\context|null $context Defaults to the permission's context.
     * @return bool
     */
    final protected function has(string $capability, ?\core\context $context = null): bool {
        return has_capability($capability, $context ?? $this->context, $this->userid);
    }

    /**
     * @return \core\context
     */
    final public function get_context(): \core\context {
        return $this->context;
    }
}
