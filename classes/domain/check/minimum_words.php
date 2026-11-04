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

namespace local_submissionaudit\domain\check;

use local_submissionaudit\domain\check_interface;
use local_submissionaudit\domain\result;
use local_submissionaudit\domain\submission;

/**
 * Fails when the online text is shorter than a minimum number of words.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class minimum_words implements check_interface {
    /** @var string */
    public const NAME = 'minimum_words';

    /**
     * @param int $minimum Smallest acceptable word count.
     */
    public function __construct(private readonly int $minimum) {
        if ($minimum < 1) {
            throw new \InvalidArgumentException('The minimum must be at least 1');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function name(): string {
        return self::NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function run(submission $submission): result {
        $count = $submission->word_count();
        $params = ['count' => $count, 'minimum' => $this->minimum];
        if ($count < $this->minimum) {
            return result::fail(self::NAME, 'msg:minwords_fail', $params, (float) $count);
        }
        return result::pass(self::NAME, 'msg:minwords_pass', $params, (float) $count);
    }
}
