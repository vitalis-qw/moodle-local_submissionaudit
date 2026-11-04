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

use local_submissionaudit\domain\event\audit_completed;
use local_submissionaudit\lib\domain\model\aggregate_root;

/**
 * Identified by the submission id: a submission has at most one current audit, and a
 * new audit replaces the previous one. Built only through
 * {@see \local_submissionaudit\application\factory\audit_factory_interface}.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit extends aggregate_root {
    /**
     * Constructor.
     *
     * @param int $submissionid
     * @param int $assignid
     * @param int $userid
     * @param result[] $results One per check, in configuration order.
     * @param int $timecreated
     */
    private function __construct(
        /** @var int Submissionid. */
        private readonly int $submissionid,
        /** @var int Assignid. */
        private readonly int $assignid,
        /** @var int Userid. */
        private readonly int $userid,
        /** @var result[] One per check, in configuration order. */
        private readonly array $results,
        /** @var int Timecreated. */
        private readonly int $timecreated,
    ) {
        foreach ($results as $result) {
            if (!$result instanceof result) {
                throw new \InvalidArgumentException('Every audit result must be a result value object');
            }
        }
    }

    /**
     * A freshly completed audit; records {@see audit_completed}.
     *
     * @param submission $submission
     * @param result[] $results
     * @param int $now
     * @return self
     */
    public static function completed(submission $submission, array $results, int $now): self {
        $audit = new self(
            (int) $submission->get_id(),
            $submission->assignid(),
            $submission->userid(),
            array_values($results),
            $now,
        );
        $audit->record(new audit_completed($now, $audit->submissionid, $audit->assignid, $audit->userid, $audit->failure_count()));
        return $audit;
    }

    /**
     * An audit loaded from storage; records nothing.
     *
     * @param int $submissionid
     * @param int $assignid
     * @param int $userid
     * @param result[] $results
     * @param int $timecreated
     * @return self
     */
    public static function restore(int $submissionid, int $assignid, int $userid, array $results, int $timecreated): self {
        return new self($submissionid, $assignid, $userid, array_values($results), $timecreated);
    }

    /**
     * Get id.
     *
     * @return ?int
     */
    public function get_id(): ?int {
        return $this->submissionid;
    }

    /**
     * Submissionid.
     *
     * @return int
     */
    public function submissionid(): int {
        return $this->submissionid;
    }

    /**
     * Assignid.
     *
     * @return int
     */
    public function assignid(): int {
        return $this->assignid;
    }

    /**
     * Userid.
     *
     * @return int
     */
    public function userid(): int {
        return $this->userid;
    }

    /**
     * Results.
     *
     * @return result[]
     */
    public function results(): array {
        return $this->results;
    }

    /**
     * Timecreated.
     *
     * @return int
     */
    public function timecreated(): int {
        return $this->timecreated;
    }

    /**
     * Failure count.
     *
     * @return int
     */
    public function failure_count(): int {
        return count(array_filter($this->results, static fn(result $r): bool => $r->status === check_status::FAIL));
    }

    /**
     * Has failures.
     *
     * @return bool
     */
    public function has_failures(): bool {
        return $this->failure_count() > 0;
    }
}
