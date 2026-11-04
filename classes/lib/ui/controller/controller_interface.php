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

namespace local_submissionaudit\lib\ui\controller;

/**
 * Request in, response out.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\ui\request\request_interface;
use local_submissionaudit\lib\ui\response\response_interface;

/**
 * Controllers are resolved from the container, so their constructor takes the services
 * they need. One public method, no output side effects.
 */
interface controller_interface {
    /**
     * @param request_interface $request
     * @return response_interface
     */
    public function handle(request_interface $request): response_interface;
}
