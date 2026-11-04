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

namespace local_submissionaudit\lib\infrastructure\config;

/**
 * Typed access to get_config().
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Inject one per plugin (bind it in the service provider with the component name)
 * instead of calling get_config() inside services.
 */
class plugin_config {
    /**
     * @param string $component Frankenstyle name, e.g. "local_example".
     */
    public function __construct(private readonly string $component) {
    }

    /**
     * @param string $name
     * @param string $default
     * @return string
     */
    public function string(string $name, string $default = ''): string {
        $value = get_config($this->component, $name);
        return ($value === false || $value === null) ? $default : (string) $value;
    }

    /**
     * @param string $name
     * @param int $default
     * @return int
     */
    public function int(string $name, int $default = 0): int {
        $value = get_config($this->component, $name);
        return ($value === false || $value === null || $value === '') ? $default : (int) $value;
    }

    /**
     * @param string $name
     * @param bool $default
     * @return bool
     */
    public function bool(string $name, bool $default = false): bool {
        $value = get_config($this->component, $name);
        return ($value === false || $value === null || $value === '') ? $default : (bool) $value;
    }

    /**
     * Comma-separated setting as a trimmed, non-empty list.
     *
     * @param string $name
     * @param string $separator
     * @return string[]
     */
    public function list(string $name, string $separator = ','): array {
        $raw = $this->string($name);
        if ($raw === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode($separator, $raw)), static fn(string $v): bool => $v !== ''));
    }

    /**
     * @return string
     */
    public function component(): string {
        return $this->component;
    }
}
