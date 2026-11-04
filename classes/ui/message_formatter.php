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

namespace local_submissionaudit\ui;

/**
 * Renders a {key, params} message in the current language.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class message_formatter {
    /**
     * Format.
     *
     * @param string $key Language string key without the component.
     * @param array $params
     * @return string Escaped, ready for output.
     */
    public static function format(string $key, array $params = []): string {
        if ($key === '' || !get_string_manager()->string_exists($key, 'local_submissionaudit')) {
            return s($key);
        }
        return s(get_string($key, 'local_submissionaudit', (object) array_map('strval', $params)));
    }

    /**
     * Turn the JSON stored in the message column into display text.
     *
     * @param string|null $stored
     * @return string
     */
    public static function format_stored(?string $stored): string {
        if ($stored === null || $stored === '') {
            return '';
        }
        $data = json_decode($stored, true);
        if (!is_array($data) || empty($data['key'])) {
            return s($stored);
        }
        return self::format((string) $data['key'], (array) ($data['params'] ?? []));
    }

    /**
     * Translated name of a check or status, falling back to the machine name.
     *
     * @param string $prefix "check" or "status".
     * @param string $name
     * @return string
     */
    public static function label(string $prefix, string $name): string {
        $key = "{$prefix}:{$name}";
        return get_string_manager()->string_exists($key, 'local_submissionaudit')
            ? get_string($key, 'local_submissionaudit')
            : s($name);
    }
}
