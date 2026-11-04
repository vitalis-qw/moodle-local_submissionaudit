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

namespace local_submissionaudit\lib\ui\response;

/**
 * Body between the theme header and footer. The controller has already set up $PAGE.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class page_response implements response_interface {
    /**
     * Constructor.
     *
     * @param string $body HTML.
     * @param \renderer_base|null $output Defaults to the core renderer.
     */
    public function __construct(
        /** @var string HTML. */
        private readonly string $body,
        /** @var \renderer_base|null Defaults to the core renderer. */
        private readonly ?\renderer_base $output = null,
    ) {
    }

    /**
     * Send.
     */
    public function send(): void {
        global $OUTPUT;
        $output = $this->output ?? $OUTPUT;
        echo $output->header();
        echo $this->body;
        echo $output->footer();
    }
}
