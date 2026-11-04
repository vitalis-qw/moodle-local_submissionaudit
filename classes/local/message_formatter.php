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

namespace local_submissionaudit\local;

/**
 * Renders the stored {key, params} message of a result in the current language.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class message_formatter {
    /**
     * Turn the JSON stored in the message column into display text.
     *
     * @param string|null $stored
     * @return string Escaped, ready for output.
     */
    public static function format(?string $stored): string {
        if ($stored === null || $stored === '') {
            return '';
        }
        $data = json_decode($stored, true);
        if (!is_array($data) || empty($data['key'])) {
            return s($stored);
        }
        $key = (string) $data['key'];
        if (!get_string_manager()->string_exists($key, 'local_submissionaudit')) {
            return s($key);
        }
        $params = (object) array_map('strval', (array) ($data['params'] ?? []));
        return s(get_string($key, 'local_submissionaudit', $params));
    }
}
