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

use local_submissionaudit\domain\check\check_interface;
use local_submissionaudit\domain\port\check_factory_interface;

/**
 * Check factory with a fixed list.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class fake_check_factory implements check_factory_interface {
    /** @var check_interface[] */
    private array $checks;

    /**
     * Constructor.
     *
     * @param check_interface ...$checks Checks.
     */
    public function __construct(check_interface ...$checks) {
        $this->checks = $checks;
    }

    /**
     * Checks.
     *
     * @return array
     */
    public function checks(): array {
        return $this->checks;
    }
}
