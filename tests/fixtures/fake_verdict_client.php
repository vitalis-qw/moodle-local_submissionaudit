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
use local_submissionaudit\domain\model\verdict;
use local_submissionaudit\domain\port\verdict_client_interface;

/**
 * Verdict client with a scripted answer.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fake_verdict_client implements verdict_client_interface {
    /** @var submission[] */
    public array $asked = [];

    /**
     * Constructor.
     *
     * @param ?verdict $verdict Verdict.
     * @param ?string $error Error.
     */
    public function __construct(
        /** @var ?verdict Verdict. */
        private readonly ?verdict $verdict,
        /** @var ?string Error. */
        private readonly ?string $error = null,
    ) {
    }

    /**
     * Verdict.
     *
     * @param submission $submission Submission.
     * @return verdict
     */
    public function verdict(submission $submission): verdict {
        $this->asked[] = $submission;
        if ($this->error !== null) {
            throw new \RuntimeException($this->error);
        }
        return $this->verdict;
    }
}
