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

namespace local_submissionaudit\infrastructure\persistence;

/**
 * Adapter: audits in local_submissionaudit_result, one row per check.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\application\factory\audit_factory_interface;
use local_submissionaudit\domain\model\audit;
use local_submissionaudit\domain\port\audit_repository_interface;
use local_submissionaudit\lib\domain\exception\not_found_exception;
use local_submissionaudit\lib\domain\model\entity_interface;
use local_submissionaudit\lib\infrastructure\persistence\filter\equal_filter;
use local_submissionaudit\lib\infrastructure\persistence\parameter_bag;

final class db_audit_repository implements audit_repository_interface {
    /** @var string */
    public const TABLE = 'local_submissionaudit_result';

    /**
     * @param \moodle_database $db
     * @param result_record_mapper $mapper
     * @param audit_factory_interface $factory
     */
    public function __construct(
        private readonly \moodle_database $db,
        private readonly result_record_mapper $mapper,
        private readonly audit_factory_interface $factory,
    ) {
    }

    public function find(int $id): ?entity_interface {
        $audits = $this->audits_from($this->rows(new equal_filter('submissionid', $id)));
        return $audits[0] ?? null;
    }

    public function get(int $id): entity_interface {
        return $this->find($id) ?? throw not_found_exception::for('audit', $id);
    }

    public function find_by_assignment(int $assignid): array {
        return $this->audits_from($this->rows(new equal_filter('assignid', $assignid)));
    }

    public function save(entity_interface $entity): entity_interface {
        if (!$entity instanceof audit) {
            throw new \InvalidArgumentException('db_audit_repository stores audits only');
        }
        $rows = [];
        foreach ($entity->results() as $result) {
            $rows[] = $this->mapper->to_record($entity, $result);
        }
        $transaction = $this->db->start_delegated_transaction();
        try {
            $this->db->delete_records(self::TABLE, ['submissionid' => $entity->submissionid()]);
            if ($rows !== []) {
                $this->db->insert_records(self::TABLE, $rows);
            }
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
        }
        return $entity;
    }

    public function delete(entity_interface $entity): void {
        $this->db->delete_records(self::TABLE, ['submissionid' => $entity->get_id()]);
    }

    /**
     * @param equal_filter $filter
     * @return \stdClass[]
     */
    private function rows(equal_filter $filter): array {
        $params = new parameter_bag();
        $where = $filter->sql($params, $this->db);
        return array_values($this->db->get_records_select(self::TABLE, $where, $params->all(), 'timecreated DESC, submissionid, id'));
    }

    /**
     * Group rows by submission and rebuild one audit per group, keeping row order.
     *
     * @param \stdClass[] $rows
     * @return audit[]
     */
    private function audits_from(array $rows): array {
        $groups = [];
        foreach ($rows as $row) {
            $groups[(int) $row->submissionid][] = $row;
        }
        $audits = [];
        foreach ($groups as $submissionid => $group) {
            $first = $group[0];
            $audits[] = $this->factory->restore(
                $submissionid,
                (int) $first->assignid,
                (int) $first->userid,
                array_map(fn(\stdClass $r) => $this->mapper->to_result($r), $group),
                (int) $first->timecreated,
            );
        }
        return $audits;
    }
}
