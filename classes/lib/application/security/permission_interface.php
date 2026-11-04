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
 * One implementation per resource type, with a can()/require() pair. Services call
 * require() at their entry point; controllers may call can() to hide links.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface permission_interface {
    /**
     * Can.
     *
     * @param action $action
     * @param mixed $subject The thing acted on (entity, DTO, id) when the decision depends on it.
     * @return bool
     */
    public function can(action $action, mixed $subject = null): bool;

    /**
     * Require.
     *
     * @param action $action
     * @param mixed $subject
     * @throws access_denied_exception
     */
    public function require(action $action, mixed $subject = null): void;
}
