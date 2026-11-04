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
 * Outcome of one check against one submission.
 *
 * The message is a language-string key plus parameters, not rendered text:
 * the domain does not know which language the tutor reads, and tests can
 * assert on keys without a string manager.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class result {
    /** @var string The check found nothing wrong. */
    public const STATUS_PASS = 'pass';
    /** @var string The check found a problem for a tutor to look at. */
    public const STATUS_FAIL = 'fail';
    /** @var string The check could not run to completion. */
    public const STATUS_ERROR = 'error';

    /**
     * @param string $checkname Machine name of the check that produced this result.
     * @param string $status One of the STATUS_* constants.
     * @param float|null $score Optional numeric score (similarity, confidence, count).
     * @param string $messagekey Language string key, without the component.
     * @param array<string, scalar> $messageparams Parameters for the language string.
     */
    public function __construct(
        public readonly string $checkname,
        public readonly string $status,
        public readonly ?float $score,
        public readonly string $messagekey,
        public readonly array $messageparams = [],
    ) {
        if (!in_array($status, [self::STATUS_PASS, self::STATUS_FAIL, self::STATUS_ERROR], true)) {
            throw new \InvalidArgumentException("Unknown result status '{$status}'");
        }
    }

    /**
     * Build a passing result.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array<string, scalar> $messageparams
     * @param float|null $score
     * @return self
     */
    public static function pass(string $checkname, string $messagekey, array $messageparams = [], ?float $score = null): self {
        return new self($checkname, self::STATUS_PASS, $score, $messagekey, $messageparams);
    }

    /**
     * Build a failing result.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array<string, scalar> $messageparams
     * @param float|null $score
     * @return self
     */
    public static function fail(string $checkname, string $messagekey, array $messageparams = [], ?float $score = null): self {
        return new self($checkname, self::STATUS_FAIL, $score, $messagekey, $messageparams);
    }

    /**
     * Build an error result.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array<string, scalar> $messageparams
     * @return self
     */
    public static function error(string $checkname, string $messagekey, array $messageparams = []): self {
        return new self($checkname, self::STATUS_ERROR, null, $messagekey, $messageparams);
    }

    /**
     * Whether the check passed.
     *
     * @return bool
     */
    public function is_pass(): bool {
        return $this->status === self::STATUS_PASS;
    }
}
