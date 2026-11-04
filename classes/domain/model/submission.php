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

namespace local_submissionaudit\domain\model;

/**
 * What the checks look at: a submission reduced to plain data.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\domain\model\entity_interface;

/**
 * Identified by the assign_submission id. Free of Moodle: no $DB, no stored_file,
 * no context. Built by {@see \local_submissionaudit\application\factory\submission_factory_interface}.
 */
final class submission implements entity_interface {
    /**
     * @param int $id assign_submission id.
     * @param int $assignid Assignment instance id.
     * @param int $userid Student id.
     * @param string $text Online text as stored (may contain HTML).
     * @param string[] $filenames Names of the submitted files.
     */
    public function __construct(
        private readonly int $id,
        private readonly int $assignid,
        private readonly int $userid,
        private readonly string $text,
        private readonly array $filenames,
    ) {
    }

    public function get_id(): ?int {
        return $this->id;
    }

    /**
     * @return int
     */
    public function assignid(): int {
        return $this->assignid;
    }

    /**
     * @return int
     */
    public function userid(): int {
        return $this->userid;
    }

    /**
     * @return string[]
     */
    public function filenames(): array {
        return $this->filenames;
    }

    /**
     * The online text with markup removed.
     *
     * @return string
     */
    public function plain_text(): string {
        // Block-level boundaries must separate words: "<p>one</p><p>two</p>" is two words, not "onetwo".
        $spaced = (string) preg_replace('~<(?:/(?:p|div|li|h[1-6]|tr|td|th|blockquote|pre)|br\s*/?)>~i', ' $0', $this->text);
        $text = html_entity_decode(strip_tags($spaced), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Number of words in the plain text. Unicode-aware, so "ім'я" and "naïve" count as one word each.
     *
     * @return int
     */
    public function word_count(): int {
        $plain = $this->plain_text();
        if ($plain === '') {
            return 0;
        }
        preg_match_all('/[\p{L}\p{N}]+(?:[\'’\-][\p{L}\p{N}]+)*/u', $plain, $matches);
        return count($matches[0]);
    }

    /**
     * Lower-case extensions of the submitted files, without the dot.
     *
     * @return string[] One entry per file; an empty string for a file without extension.
     */
    public function file_extensions(): array {
        return array_map(static function (string $name): string {
            $dot = strrpos($name, '.');
            return $dot === false ? '' : strtolower(substr($name, $dot + 1));
        }, $this->filenames);
    }
}
