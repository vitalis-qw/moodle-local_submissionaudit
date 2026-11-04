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
 * Admin settings.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_submissionaudit', get_string('pluginname', 'local_submissionaudit'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
        'local_submissionaudit/enabled',
        get_string('settings:enabled', 'local_submissionaudit'),
        get_string('settings:enabled_desc', 'local_submissionaudit'),
        1
    ));
    $settings->add(new admin_setting_configtext(
        'local_submissionaudit/minwords',
        get_string('settings:minwords', 'local_submissionaudit'),
        get_string('settings:minwords_desc', 'local_submissionaudit'),
        0,
        PARAM_INT
    ));
    $settings->add(new admin_setting_configtext(
        'local_submissionaudit/allowedfiletypes',
        get_string('settings:allowedfiletypes', 'local_submissionaudit'),
        get_string('settings:allowedfiletypes_desc', 'local_submissionaudit'),
        '',
        PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext(
        'local_submissionaudit/externalurl',
        get_string('settings:externalurl', 'local_submissionaudit'),
        get_string('settings:externalurl_desc', 'local_submissionaudit'),
        '',
        PARAM_URL
    ));
    $settings->add(new admin_setting_configpasswordunmask(
        'local_submissionaudit/externaltoken',
        get_string('settings:externaltoken', 'local_submissionaudit'),
        get_string('settings:externaltoken_desc', 'local_submissionaudit'),
        ''
    ));
}
