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

use local_submissionaudit\application\security\audit_action;
use local_submissionaudit\application\security\audit_permission_interface;
use local_submissionaudit\lib\application\security\action;
use local_submissionaudit\lib\infrastructure\security\capability_permission;

/**
 * Audit permissions backed by capabilities.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class capability_audit_permission extends capability_permission implements audit_permission_interface {
    /** @var string */
    /** @var string Cap view report. */
    public const CAP_VIEW_REPORT = 'local/submissionaudit:viewreport';

    /**
     * Can.
     *
     * @param action $action Action.
     * @param mixed $subject Subject.
     * @return bool
     */
    public function can(action $action, mixed $subject = null): bool {
        if ($action->resource !== audit_action::RESOURCE) {
            return false;
        }
        return match ($action->name) {
            'view_report' => $this->has(self::CAP_VIEW_REPORT),
            default => false,
        };
    }
}
