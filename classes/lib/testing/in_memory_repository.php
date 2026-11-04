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

namespace local_submissionaudit\lib\testing;

/**
 * Array-backed repository for application tests.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\domain\exception\not_found_exception;
use local_submissionaudit\lib\domain\model\aggregate_root;
use local_submissionaudit\lib\domain\model\entity_interface;
use local_submissionaudit\lib\domain\repository\repository_interface;

/**
 * Subclasses implement with_id() because entities decide how an id is assigned.
 *
 * @template E of entity_interface
 * @implements repository_interface<E>
 */
abstract class in_memory_repository implements repository_interface {
    /** @var array<int, E> */
    protected array $items = [];

    /** @var int */
    private int $nextid = 1;

    /**
     * Return a copy of the entity carrying the given id.
     *
     * @param E $entity
     * @param int $id
     * @return E
     */
    abstract protected function with_id(entity_interface $entity, int $id): entity_interface;

    public function find(int $id): ?entity_interface {
        return $this->items[$id] ?? null;
    }

    public function get(int $id): entity_interface {
        return $this->items[$id] ?? throw not_found_exception::for(static::class, $id);
    }

    public function save(entity_interface $entity): entity_interface {
        $id = $entity->get_id() ?? $this->nextid++;
        $saved = $entity->get_id() === null ? $this->with_id($entity, $id) : $entity;
        if ($entity instanceof aggregate_root && $saved instanceof aggregate_root && $saved !== $entity) {
            $saved->adopt_events($entity);
        }
        $this->items[$id] = $saved;
        return $saved;
    }

    public function delete(entity_interface $entity): void {
        unset($this->items[$entity->get_id() ?? -1]);
    }

    /**
     * @return E[]
     */
    public function all(): array {
        return array_values($this->items);
    }
}
