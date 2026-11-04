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

namespace local_submissionaudit\lib\application\dto;

/**
 * Base DTO with reflection-based array conversion.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Subclasses declare promoted readonly constructor properties and get from_array()
 * (keyed by parameter name, defaults honoured) and to_array() (nested DTOs expanded)
 * for free.
 *
 *     final readonly class audit_report extends abstract_dto {
 *         public function __construct(public int $submissionid, public array $results = []) {}
 *     }
 */
abstract readonly class abstract_dto implements dto_interface {
    public static function from_array(array $data): static {
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        $args = [];
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $data)) {
                $args[$name] = $data[$name];
            } else if ($parameter->isDefaultValueAvailable()) {
                $args[$name] = $parameter->getDefaultValue();
            } else {
                throw new \InvalidArgumentException(sprintf('%s::from_array(): key "%s" is missing', static::class, $name));
            }
        }
        return new static(...$args);
    }

    public function to_array(): array {
        $out = [];
        foreach (get_object_vars($this) as $name => $value) {
            $out[$name] = self::export($value);
        }
        return $out;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function export(mixed $value): mixed {
        if ($value instanceof dto_interface) {
            return $value->to_array();
        }
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }
        if (is_array($value)) {
            return array_map(static fn(mixed $item): mixed => self::export($item), $value);
        }
        return $value;
    }
}
