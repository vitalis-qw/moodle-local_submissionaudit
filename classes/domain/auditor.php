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

namespace local_submissionaudit\domain;

/**
 * Runs every configured check against a submission and never lets one
 * broken check hide the results of the others.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class auditor {
    /** @var check_interface[] */
    private array $checks = [];

    /**
     * @param check_interface[] $checks Checks in the order they should run.
     */
    public function __construct(array $checks) {
        foreach ($checks as $check) {
            if (!$check instanceof check_interface) {
                throw new \InvalidArgumentException('Every check must implement check_interface');
            }
            $this->checks[] = $check;
        }
    }

    /**
     * The configured checks.
     *
     * @return check_interface[]
     */
    public function checks(): array {
        return $this->checks;
    }

    /**
     * Run all checks. A check that throws yields an error result instead of
     * aborting the audit.
     *
     * @param submission $submission
     * @return result[] One result per check, in configuration order.
     */
    public function audit(submission $submission): array {
        $results = [];
        foreach ($this->checks as $check) {
            try {
                $results[] = $check->run($submission);
            } catch (\Throwable $e) {
                $results[] = result::error($check->name(), 'msg:check_error', ['error' => $e->getMessage()]);
            }
        }
        return $results;
    }
}
