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

namespace local_submissionaudit\domain\check;

use local_submissionaudit\domain\check_interface;
use local_submissionaudit\domain\result;
use local_submissionaudit\domain\submission;

/**
 * Fails when a submitted file has an extension outside the allowed list.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class allowed_file_types implements check_interface {
    /** @var string */
    public const NAME = 'allowed_file_types';

    /** @var string[] Lower-case extensions without the dot. */
    private array $allowed;

    /**
     * @param string[] $extensions Allowed extensions, with or without a leading dot, any case.
     */
    public function __construct(array $extensions) {
        $this->allowed = [];
        foreach ($extensions as $extension) {
            $clean = strtolower(ltrim(trim((string) $extension), '.'));
            if ($clean !== '') {
                $this->allowed[] = $clean;
            }
        }
        if ($this->allowed === []) {
            throw new \InvalidArgumentException('At least one allowed extension is required');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function name(): string {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function run(submission $submission): result {
        if ($submission->filenames === []) {
            return result::pass(self::NAME, 'msg:filetypes_nofiles');
        }
        $rejected = [];
        foreach ($submission->filenames as $index => $filename) {
            if (!in_array($submission->file_extensions()[$index], $this->allowed, true)) {
                $rejected[] = $filename;
            }
        }
        if ($rejected !== []) {
            return result::fail(self::NAME, 'msg:filetypes_fail', ['files' => implode(', ', $rejected)], (float) count($rejected));
        }
        return result::pass(self::NAME, 'msg:filetypes_pass', ['count' => count($submission->filenames)]);
    }
}
