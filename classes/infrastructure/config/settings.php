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

namespace local_submissionaudit\infrastructure\config;

/**
 * Typed view of the plugin settings.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\lib\infrastructure\config\plugin_config;

final class settings {
    /**
     * @param plugin_config $config
     */
    public function __construct(private readonly plugin_config $config) {
    }

    /**
     * @return bool
     */
    public function enabled(): bool {
        return $this->config->bool('enabled', true);
    }

    /**
     * @return int 0 = check disabled.
     */
    public function min_words(): int {
        return $this->config->int('minwords');
    }

    /**
     * @return string[] Empty = check disabled.
     */
    public function allowed_file_types(): array {
        return $this->config->list('allowedfiletypes');
    }

    /**
     * @return string Empty = check disabled.
     */
    public function external_url(): string {
        return trim($this->config->string('externalurl'));
    }

    /**
     * @return string
     */
    public function external_token(): string {
        return $this->config->string('externaltoken');
    }
}
