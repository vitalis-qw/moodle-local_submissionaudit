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

namespace local_submissionaudit\lib\infrastructure\persistence;

/**
 * Each query gets its own bag, so parameter names never clash and no global counter
 * is needed.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class parameter_bag {
    /** @var array */
    private array $params = [];

    /** @var int */
    private int $counter = 0;

    /**
     * Register a value and return the placeholder name (without the leading colon).
     *
     * @param mixed $value
     * @param string $hint Used as a readable prefix; non-alphanumerics are dropped.
     * @return string
     */
    public function add(mixed $value, string $hint = 'p'): string {
        $prefix = substr((string) preg_replace('/[^a-z0-9_]/', '', strtolower($hint)), 0, 20) ?: 'p';
        $name = $prefix . '_' . $this->counter++;
        $this->params[$name] = $value;
        return $name;
    }

    /**
     * Merge already-named parameters (from raw SQL). Names must not clash.
     *
     * @param array $params
     */
    public function merge(array $params): void {
        foreach ($params as $name => $value) {
            if (array_key_exists($name, $this->params)) {
                throw new \InvalidArgumentException("SQL parameter '{$name}' is already defined");
            }
            $this->params[$name] = $value;
        }
    }

    /**
     * All.
     *
     * @return array
     */
    public function all(): array {
        return $this->params;
    }
}
