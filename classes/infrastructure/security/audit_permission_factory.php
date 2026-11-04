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

namespace local_submissionaudit\infrastructure\security;

use local_submissionaudit\application\security\audit_permission_factory_interface;
use local_submissionaudit\application\security\audit_permission_interface;

/**
 * Builds audit permissions for a course module.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_permission_factory implements audit_permission_factory_interface {
    /**
     * For module.
     *
     * @param int $cmid Cmid.
     * @param int $userid Userid.
     * @return audit_permission_interface
     */
    public function for_module(int $cmid, int $userid): audit_permission_interface {
        return new capability_audit_permission($userid, \core\context\module::instance($cmid));
    }
}
