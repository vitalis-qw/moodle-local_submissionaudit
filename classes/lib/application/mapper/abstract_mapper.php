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

namespace local_submissionaudit\lib\application\mapper;

use local_submissionaudit\lib\application\dto\dto_interface;
use local_submissionaudit\lib\domain\model\entity_interface;

/**
 * Implement to_dto(); override to_entity() when the DTO carries enough to rebuild the entity.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class abstract_mapper implements mapper_interface {
    /**
     * To entity.
     *
     * @param dto_interface $dto Dto.
     * @return entity_interface
     */
    public function to_entity(dto_interface $dto): entity_interface {
        throw new \LogicException(sprintf('%s does not map DTOs back to entities', static::class));
    }

    /**
     * To dto collection.
     *
     * @param iterable $entities Entities.
     * @return array
     */
    final public function to_dto_collection(iterable $entities): array {
        $out = [];
        foreach ($entities as $entity) {
            $out[] = $this->to_dto($entity);
        }
        return $out;
    }

    /**
     * To entity collection.
     *
     * @param iterable $dtos Dtos.
     * @return array
     */
    final public function to_entity_collection(iterable $dtos): array {
        $out = [];
        foreach ($dtos as $dto) {
            $out[] = $this->to_entity($dto);
        }
        return $out;
    }

    /**
     * To array collection.
     *
     * @param iterable $entities Entities.
     * @return array
     */
    final public function to_array_collection(iterable $entities): array {
        $out = [];
        foreach ($entities as $entity) {
            $out[] = $this->to_dto($entity)->to_array();
        }
        return $out;
    }
}
