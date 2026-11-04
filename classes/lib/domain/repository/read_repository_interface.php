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

namespace local_submissionaudit\lib\domain\repository;

use local_submissionaudit\lib\domain\exception\not_found_exception;
use local_submissionaudit\lib\domain\model\entity_interface;

/**
 * Port: read entities.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface read_repository_interface {
    /**
     * Find.
     *
     * @param int $id
     * @return entity_interface|null
     */
    public function find(int $id): ?entity_interface;

    /**
     * Get.
     *
     * @param int $id
     * @return entity_interface
     * @throws not_found_exception
     */
    public function get(int $id): entity_interface;
}
