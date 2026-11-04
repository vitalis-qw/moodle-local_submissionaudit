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

namespace local_submissionaudit\domain\check;

/**
 * Delegates the decision to an external service through a port.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_submissionaudit\domain\model\result;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\port\verdict_client_interface;

/**
 * A slow or failing service produces an error result; it never blocks the other
 * checks, and the tutor can see that the verdict is missing.
 */
final class external_verdict implements check_interface {
    /** @var string */
    public const NAME = 'external_verdict';

    /**
     * @param verdict_client_interface $client
     */
    public function __construct(private readonly verdict_client_interface $client) {
    }

    public function name(): string {
        return self::NAME;
    }

    public function run(submission $submission): result {
        try {
            $verdict = $this->client->verdict($submission);
        } catch (\RuntimeException $e) {
            return result::error(self::NAME, 'msg:external_error', ['error' => $e->getMessage()]);
        }
        $params = ['message' => $verdict->message];
        if ($verdict->passed) {
            return result::pass(self::NAME, 'msg:external_message', $params, $verdict->score);
        }
        return result::fail(self::NAME, 'msg:external_message', $params, $verdict->score);
    }
}
