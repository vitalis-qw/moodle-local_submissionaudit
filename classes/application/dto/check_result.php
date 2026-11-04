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

use local_submissionaudit\lib\application\dto\abstract_dto;

/**
 * One check outcome, as data.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class check_result extends abstract_dto {
    /**
     * Constructor.
     *
     * @param string $checkname
     * @param string $status pass|fail|error
     * @param float|null $score
     * @param string $messagekey
     * @param array<string, scalar> $messageparams
     */
    public function __construct(
        /** @var string Checkname. */
        public readonly string $checkname,
        /** @var string pass|fail|error */
        public readonly string $status,
        /** @var float|null Score. */
        public readonly ?float $score,
        /** @var string Messagekey. */
        public readonly string $messagekey,
        /** @var array Messageparams. */
        public readonly array $messageparams = [],
    ) {
    }
}
