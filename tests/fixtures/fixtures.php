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
 * Loads the test doubles (tests/ is not autoloaded).
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace local_submissionaudit\fixtures;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixed_clock.php');
require_once(__DIR__ . '/fake_submission_repository.php');
require_once(__DIR__ . '/fake_verdict_client.php');
require_once(__DIR__ . '/fake_check_factory.php');
require_once(__DIR__ . '/fake_user_directory.php');
require_once(__DIR__ . '/in_memory_audit_repository.php');
require_once(__DIR__ . '/assign_fixture_trait.php');
