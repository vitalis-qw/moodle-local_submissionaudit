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

use local_submissionaudit\lib\domain\model\value_object_interface;

/**
 * The message is a language-string key plus parameters, not rendered text: the
 * domain does not know which language the tutor reads.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class result implements value_object_interface {
    /**
     * Constructor.
     *
     * @param string $checkname Machine name of the check that produced this result.
     * @param check_status $status
     * @param float|null $score Optional numeric score (similarity, confidence, count).
     * @param string $messagekey Language string key, without the component.
     * @param array $messageparams Parameters for the language string.
     */
    public function __construct(
        /** @var string Machine name of the check that produced this result. */
        public readonly string $checkname,
        /** @var check_status Status. */
        public readonly check_status $status,
        /** @var float|null Optional numeric score (similarity, confidence, count). */
        public readonly ?float $score,
        /** @var string Language string key, without the component. */
        public readonly string $messagekey,
        /** @var array Messageparams. */
        public readonly array $messageparams = [],
    ) {
    }

    /**
     * Pass.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array $messageparams
     * @param float|null $score
     * @return self
     */
    public static function pass(string $checkname, string $messagekey, array $messageparams = [], ?float $score = null): self {
        return new self($checkname, check_status::PASS, $score, $messagekey, $messageparams);
    }

    /**
     * Fail.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array $messageparams
     * @param float|null $score
     * @return self
     */
    public static function fail(string $checkname, string $messagekey, array $messageparams = [], ?float $score = null): self {
        return new self($checkname, check_status::FAIL, $score, $messagekey, $messageparams);
    }

    /**
     * Error.
     *
     * @param string $checkname
     * @param string $messagekey
     * @param array $messageparams
     * @return self
     */
    public static function error(string $checkname, string $messagekey, array $messageparams = []): self {
        return new self($checkname, check_status::ERROR, null, $messagekey, $messageparams);
    }

    /**
     * Is pass.
     *
     * @return bool
     */
    public function is_pass(): bool {
        return $this->status === check_status::PASS;
    }

    /**
     * Equals.
     *
     * @param value_object_interface $other Other.
     * @return bool
     */
    public function equals(value_object_interface $other): bool {
        return $other instanceof self
            && $other->checkname === $this->checkname
            && $other->status === $this->status
            && $other->score === $this->score
            && $other->messagekey === $this->messagekey
            && $other->messageparams === $this->messageparams;
    }
}
