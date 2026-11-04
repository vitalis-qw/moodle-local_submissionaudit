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

namespace local_submissionaudit;

use local_submissionaudit\application\factory\audit_factory;
use local_submissionaudit\application\factory\audit_factory_interface;
use local_submissionaudit\application\factory\submission_factory;
use local_submissionaudit\application\factory\submission_factory_interface;
use local_submissionaudit\application\mapper\audit_mapper;
use local_submissionaudit\application\mapper\audit_mapper_interface;
use local_submissionaudit\application\port\user_directory_interface;
use local_submissionaudit\application\security\audit_permission_factory_interface;
use local_submissionaudit\application\service\audit_service;
use local_submissionaudit\application\service\audit_service_interface;
use local_submissionaudit\application\service\report_service;
use local_submissionaudit\application\service\report_service_interface;
use local_submissionaudit\domain\event\audit_completed;
use local_submissionaudit\domain\port\audit_repository_interface;
use local_submissionaudit\domain\port\check_factory_interface;
use local_submissionaudit\domain\port\submission_repository_interface;
use local_submissionaudit\domain\port\verdict_client_interface;
use local_submissionaudit\domain\service\auditor;
use local_submissionaudit\domain\service\auditor_interface;
use local_submissionaudit\infrastructure\config\settings;
use local_submissionaudit\infrastructure\event\audit_completed_event_factory;
use local_submissionaudit\infrastructure\http\curl_verdict_client;
use local_submissionaudit\infrastructure\moodle\moodle_submission_repository;
use local_submissionaudit\infrastructure\moodle\moodle_user_directory;
use local_submissionaudit\infrastructure\moodle\settings_check_factory;
use local_submissionaudit\infrastructure\persistence\db_audit_repository;
use local_submissionaudit\infrastructure\security\audit_permission_factory;
use local_submissionaudit\lib\di\service_provider as base_service_provider;
use local_submissionaudit\lib\domain\event\event_publisher_interface;
use local_submissionaudit\lib\infrastructure\config\plugin_config;
use local_submissionaudit\lib\infrastructure\event\moodle_event_publisher;
use local_submissionaudit\ui\controller\report_controller;
use local_submissionaudit\ui\controller\report_controller_interface;

/**
 * Registered through db/hooks.php. Everything not listed here is autowired by class name.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class service_provider extends base_service_provider {
    /**
     * Register.
     */
    protected function register(): void {
        $this
            // Domain ports.
            ->bind(submission_repository_interface::class, moodle_submission_repository::class)
            ->bind(audit_repository_interface::class, db_audit_repository::class)
            ->bind(check_factory_interface::class, settings_check_factory::class)
            ->bind(auditor_interface::class, auditor::class)
            ->factory(verdict_client_interface::class, static fn(settings $settings): curl_verdict_client =>
                new curl_verdict_client($settings->external_url(), $settings->external_token()))
            // Application.
            ->bind(audit_service_interface::class, audit_service::class)
            ->bind(report_service_interface::class, report_service::class)
            ->bind(audit_factory_interface::class, audit_factory::class)
            ->bind(submission_factory_interface::class, submission_factory::class)
            ->bind(audit_mapper_interface::class, audit_mapper::class)
            ->bind(user_directory_interface::class, moodle_user_directory::class)
            ->bind(audit_permission_factory_interface::class, audit_permission_factory::class)
            // Infrastructure.
            ->factory(settings::class, static fn(): settings => new settings(new plugin_config('local_submissionaudit')))
            ->factory(event_publisher_interface::class, static fn(audit_completed_event_factory $factory): moodle_event_publisher =>
                new moodle_event_publisher([audit_completed::class => $factory]))
            // UI.
            ->bind(report_controller_interface::class, report_controller::class);
    }
}
