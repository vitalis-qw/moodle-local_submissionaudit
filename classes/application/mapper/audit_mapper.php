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

namespace local_submissionaudit\application\mapper;

/**
 * audit → audit_report.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\application\dto\audit_report;
use local_submissionaudit\application\dto\check_result;
use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\model\result;
use local_submissionaudit\lib\application\dto\dto_interface;
use local_submissionaudit\lib\application\mapper\abstract_mapper;
use local_submissionaudit\lib\domain\model\entity_interface;

final class audit_mapper extends abstract_mapper implements audit_mapper_interface {
    public function to_dto(entity_interface $entity): dto_interface {
        if (!$entity instanceof audit) {
            throw new \InvalidArgumentException('audit_mapper maps audits only');
        }
        return new audit_report(
            $entity->submissionid(),
            $entity->assignid(),
            $entity->userid(),
            $entity->timecreated(),
            array_map(static fn(result $r): check_result => new check_result(
                $r->checkname,
                $r->status->value,
                $r->score,
                $r->messagekey,
                $r->messageparams,
            ), $entity->results()),
            $entity->failure_count(),
        );
    }
}
