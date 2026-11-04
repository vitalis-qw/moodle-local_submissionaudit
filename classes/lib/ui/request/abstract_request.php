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

namespace local_submissionaudit\lib\ui\request;

/**
 * Base request: declare rules, get a readonly object.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\param;

/**
 *     final readonly class report_request extends abstract_request {
 *         public function __construct(public int $id, public int $page = 1) {}
 *         protected static function rules(): array {
 *             return ['id' => param::INT, 'page' => param::INT];
 *         }
 *     }
 *
 * A key present in the input is cleaned with its param type. A missing key takes the
 * constructor default; without a default it is a missing-parameter error.
 */
abstract readonly class abstract_request implements request_interface {
    /**
     * Parameter name => core\param type.
     *
     * @return array<string, param>
     */
    abstract protected static function rules(): array;

    public static function from_array(array $input): static {
        $rules = static::rules();
        $constructor = (new \ReflectionClass(static::class))->getConstructor();
        $args = [];
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            $name = $parameter->getName();
            $type = $rules[$name] ?? throw new \LogicException(sprintf('%s: no rule for parameter "%s"', static::class, $name));
            if (array_key_exists($name, $input)) {
                $args[$name] = $type->clean($input[$name]);
            } else if ($parameter->isDefaultValueAvailable()) {
                $args[$name] = $parameter->getDefaultValue();
            } else {
                throw new \moodle_exception('missingparam', '', '', $name);
            }
        }
        return new static(...$args);
    }

    public static function from_globals(): static {
        // Same precedence as optional_param(): POST wins over GET.
        return static::from_array(array_merge($_GET, $_POST));
    }

    public function to_array(): array {
        $out = [];
        foreach (get_object_vars($this) as $name => $value) {
            $out[$name] = $value instanceof \BackedEnum ? $value->value : $value;
        }
        return $out;
    }
}
