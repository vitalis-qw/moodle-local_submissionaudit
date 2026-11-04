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
 * What an external analysis service answers about a submission.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\domain\model\value_object_interface;

final readonly class verdict implements value_object_interface {
    /**
     * @param bool $passed Whether the service considers the submission acceptable.
     * @param float|null $score Optional score the service reported.
     * @param string $message Free-text explanation from the service.
     */
    public function __construct(
        public bool $passed,
        public ?float $score,
        public string $message,
    ) {
    }

    public function equals(value_object_interface $other): bool {
        return $other instanceof self && $other->passed === $this->passed
            && $other->score === $this->score && $other->message === $this->message;
    }
}
