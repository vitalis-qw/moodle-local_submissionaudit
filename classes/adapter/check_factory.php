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

namespace local_submissionaudit\adapter;

use local_submissionaudit\domain\check\allowed_file_types;
use local_submissionaudit\domain\check\external_verdict;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\check_interface;

/**
 * Adapter: turns plugin settings into a list of checks.
 *
 * This is the one place that knows both the admin settings and the check
 * constructors. Adding a check means one new class plus one line here.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class check_factory {
    /**
     * Build the checks enabled in the plugin settings.
     *
     * @return check_interface[]
     */
    public static function from_config(): array {
        $config = get_config('local_submissionaudit');
        $checks = [];

        $minwords = (int) ($config->minwords ?? 0);
        if ($minwords > 0) {
            $checks[] = new minimum_words($minwords);
        }

        $extensions = array_filter(array_map('trim', explode(',', (string) ($config->allowedfiletypes ?? ''))));
        if ($extensions !== []) {
            $checks[] = new allowed_file_types($extensions);
        }

        $url = trim((string) ($config->externalurl ?? ''));
        if ($url !== '') {
            $checks[] = new external_verdict(new curl_verdict_client($url, (string) ($config->externaltoken ?? '')));
        }

        return $checks;
    }
}
