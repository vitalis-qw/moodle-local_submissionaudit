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

namespace local_submissionaudit\fixtures;

use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\port\submission_repository_interface;

/**
 * Submissions held in an array.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fake_submission_repository implements submission_repository_interface {
    /** @var array<int, submission> */
    private array $items = [];

    /**
     * Add.
     *
     * @param submission $submission Submission.
     * @return self
     */
    public function add(submission $submission): self {
        $this->items[(int) $submission->get_id()] = $submission;
        return $this;
    }

    /**
     * Find.
     *
     * @param int $submissionid Submissionid.
     * @return ?submission
     */
    public function find(int $submissionid): ?submission {
        return $this->items[$submissionid] ?? null;
    }
}
