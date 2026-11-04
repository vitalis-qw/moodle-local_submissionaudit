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

/**
 * Convert entities to DTOs and back.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\application\dto\dto_interface;
use local_submissionaudit\lib\domain\model\entity_interface;

/**
 * One mapper per aggregate. Mappers are stateless services resolved from the container.
 *
 * @template E of entity_interface
 * @template D of dto_interface
 */
interface mapper_interface {
    /**
     * @param E $entity
     * @return D
     */
    public function to_dto(entity_interface $entity): dto_interface;

    /**
     * @param D $dto
     * @return E
     */
    public function to_entity(dto_interface $dto): entity_interface;

    /**
     * @param iterable<E> $entities
     * @return D[]
     */
    public function to_dto_collection(iterable $entities): array;

    /**
     * @param iterable<D> $dtos
     * @return E[]
     */
    public function to_entity_collection(iterable $dtos): array;

    /**
     * @param iterable<E> $entities
     * @return array<int, array<string, mixed>>
     */
    public function to_array_collection(iterable $entities): array;
}
