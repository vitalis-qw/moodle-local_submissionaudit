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

namespace local_submissionaudit\application\service;

use local_submissionaudit\application\dto\audit_report;
use local_submissionaudit\application\dto\audit_request;
use local_submissionaudit\application\factory\audit_factory_interface;
use local_submissionaudit\application\mapper\audit_mapper_interface;
use local_submissionaudit\domain\port\audit_repository_interface;
use local_submissionaudit\domain\port\submission_repository_interface;
use local_submissionaudit\domain\service\auditor_interface;
use local_submissionaudit\lib\application\service\abstract_service;
use local_submissionaudit\lib\domain\event\event_publisher_interface;
use Psr\Clock\ClockInterface;

/**
 * Talks to the outside world only through ports, so the whole flow is testable
 * with in-memory doubles.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class audit_service extends abstract_service implements audit_service_interface {
    /**
     * Constructor.
     *
     * @param submission_repository_interface $submissions
     * @param auditor_interface $auditor
     * @param audit_repository_interface $audits
     * @param audit_factory_interface $factory
     * @param audit_mapper_interface $mapper
     * @param ClockInterface $clock
     * @param event_publisher_interface $publisher
     */
    public function __construct(
        /** @var submission_repository_interface Submissions. */
        private readonly submission_repository_interface $submissions,
        /** @var auditor_interface Auditor. */
        private readonly auditor_interface $auditor,
        /** @var audit_repository_interface Audits. */
        private readonly audit_repository_interface $audits,
        /** @var audit_factory_interface Factory. */
        private readonly audit_factory_interface $factory,
        /** @var audit_mapper_interface Mapper. */
        private readonly audit_mapper_interface $mapper,
        /** @var ClockInterface Clock. */
        private readonly ClockInterface $clock,
        event_publisher_interface $publisher,
    ) {
        parent::__construct($publisher);
    }

    /**
     * Run.
     *
     * @param audit_request $request Request.
     * @return ?audit_report
     */
    public function run(audit_request $request): ?audit_report {
        $submission = $this->submissions->find($request->submissionid);
        if ($submission === null) {
            return null;
        }
        $results = $this->auditor->audit($submission);
        $audit = $this->factory->completed($submission, $results, $this->clock->now()->getTimestamp());
        $saved = $this->audits->save($audit);
        $this->dispatch_events($saved);
        $report = $this->mapper->to_dto($saved);
        \assert($report instanceof audit_report);
        return $report;
    }
}
