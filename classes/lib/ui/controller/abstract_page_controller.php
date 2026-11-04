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
 * Base for controllers that render a page.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\application\security\access_denied_exception;
use local_submissionaudit\lib\domain\exception\not_found_exception;

/**
 * Helpers for $PAGE setup and for translating application exceptions into Moodle
 * error pages. The entry script is three lines:
 *
 *     $request = report_request::from_globals();
 *     \core\di::get(report_controller::class)->handle($request)->send();
 */
abstract class abstract_page_controller implements controller_interface {
    /**
     * require_login() and the usual $PAGE calls.
     *
     * @param \moodle_url $url
     * @param \core\context $context
     * @param string $title
     * @param string $layout
     * @param \stdClass|int|null $course Passed to require_login().
     * @param \cm_info|\stdClass|null $cm Passed to require_login().
     */
    protected function setup_page(
        \moodle_url $url,
        \core\context $context,
        string $title,
        string $layout = 'report',
        \stdClass|int|null $course = null,
        \cm_info|\stdClass|null $cm = null,
    ): void {
        global $PAGE;
        require_login($course, false, $cm);
        $PAGE->set_context($context);
        $PAGE->set_url($url);
        $PAGE->set_pagelayout($layout);
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
    }

    /**
     * @param string $component
     * @return \renderer_base
     */
    protected function renderer(string $component): \renderer_base {
        global $PAGE;
        return $PAGE->get_renderer($component);
    }

    /**
     * Run application code and map its exceptions to Moodle ones.
     *
     * @param callable $work
     * @return mixed
     */
    protected function guard(callable $work): mixed {
        try {
            return $work();
        } catch (access_denied_exception $e) {
            throw new \moodle_exception('nopermissions', 'error', '', (string) $e->action, $e->getMessage());
        } catch (not_found_exception $e) {
            throw new \moodle_exception('invalidrecord', 'error', '', $e->getMessage());
        }
    }
}
