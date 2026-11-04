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

/**
 * Navigation callback.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Adds the audit report to the settings of every assignment the user may audit.
 *
 * @param settings_navigation $settingsnav
 * @param context|null $context
 */
function local_submissionaudit_extend_settings_navigation(settings_navigation $settingsnav, ?context $context): void {
    if (!$context || $context->contextlevel !== CONTEXT_MODULE) {
        return;
    }
    $cm = get_coursemodule_from_id('assign', $context->instanceid);
    if (!$cm || !has_capability('local/submissionaudit:viewreport', $context)) {
        return;
    }
    $modulenode = $settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
    if (!$modulenode) {
        return;
    }
    $modulenode->add(
        get_string('report', 'local_submissionaudit'),
        new moodle_url('/local/submissionaudit/report.php', ['id' => $cm->id]),
        navigation_node::TYPE_SETTING,
        null,
        'local_submissionaudit_report',
        new pix_icon('i/report', '')
    );
}
