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

namespace local_submissionaudit\domain\port;

use local_submissionaudit\domain\submission;
use local_submissionaudit\domain\verdict;

/**
 * Port: an external service that judges a submission (similarity, AI-writing
 * detection, a custom rules engine).
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface verdict_client_interface {
    /**
     * Ask the service for a verdict.
     *
     * @param submission $submission
     * @return verdict
     * @throws \RuntimeException When the service cannot be reached or answers nonsense.
     */
    public function verdict(submission $submission): verdict;
}
