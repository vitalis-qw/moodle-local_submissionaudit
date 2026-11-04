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
 * Audit repository held in memory.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\fixtures;

use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\port\audit_repository_interface;
use local_submissionaudit\lib\domain\model\entity_interface;
use local_submissionaudit\lib\testing\in_memory_repository;

final class in_memory_audit_repository extends in_memory_repository implements audit_repository_interface {
    protected function with_id(entity_interface $entity, int $id): entity_interface {
        return $entity; // Audits always carry their submission id.
    }

    public function find_by_assignment(int $assignid): array {
        $found = array_filter($this->items, static fn(audit $a): bool => $a->assignid() === $assignid);
        usort($found, static fn(audit $a, audit $b): int => $b->timecreated() <=> $a->timecreated());
        return array_values($found);
    }
}
