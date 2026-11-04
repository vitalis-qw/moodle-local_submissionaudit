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
 * One mapper per aggregate. Mappers are stateless services resolved from the container.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface mapper_interface {
    /**
     * To dto.
     *
     * @param entity_interface $entity
     * @return dto_interface
     */
    public function to_dto(entity_interface $entity): dto_interface;

    /**
     * To entity.
     *
     * @param dto_interface $dto
     * @return entity_interface
     */
    public function to_entity(dto_interface $dto): entity_interface;

    /**
     * To dto collection.
     *
     * @param iterable $entities
     * @return dto_interface[]
     */
    public function to_dto_collection(iterable $entities): array;

    /**
     * To entity collection.
     *
     * @param iterable $dtos
     * @return entity_interface[]
     */
    public function to_entity_collection(iterable $dtos): array;

    /**
     * To array collection.
     *
     * @param iterable $entities
     * @return array<int, array<string, mixed>>
     */
    public function to_array_collection(iterable $entities): array;
}
