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

namespace local_submissionaudit\infrastructure\moodle;

/**
 * Adapter: plugin settings → list of checks.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\domain\check\allowed_file_types;
use local_submissionaudit\domain\check\external_verdict;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\port\check_factory_interface;
use local_submissionaudit\domain\port\verdict_client_interface;
use local_submissionaudit\infrastructure\config\settings;

/**
 * The one place that knows both the admin settings and the check constructors.
 * Adding a check means one new class plus one block here.
 */
final class settings_check_factory implements check_factory_interface {
    /**
     * @param settings $settings
     * @param verdict_client_interface $client
     */
    public function __construct(
        private readonly settings $settings,
        private readonly verdict_client_interface $client,
    ) {
    }

    public function checks(): array {
        $checks = [];
        if ($this->settings->min_words() > 0) {
            $checks[] = new minimum_words($this->settings->min_words());
        }
        if ($this->settings->allowed_file_types() !== []) {
            $checks[] = new allowed_file_types($this->settings->allowed_file_types());
        }
        if ($this->settings->external_url() !== '') {
            $checks[] = new external_verdict($this->client);
        }
        return $checks;
    }
}
