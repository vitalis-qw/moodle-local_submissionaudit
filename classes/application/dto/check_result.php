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

namespace local_submissionaudit\application\dto;

/**
 * One check outcome, as data.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\application\dto\abstract_dto;

final readonly class check_result extends abstract_dto {
    /**
     * @param string $checkname
     * @param string $status pass|fail|error
     * @param float|null $score
     * @param string $messagekey
     * @param array<string, scalar> $messageparams
     */
    public function __construct(
        public string $checkname,
        public string $status,
        public ?float $score,
        public string $messagekey,
        public array $messageparams = [],
    ) {
    }
}
