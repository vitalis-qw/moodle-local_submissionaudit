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

/**
 * Tests for the built-in checks.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_submissionaudit\domain;

require_once(__DIR__ . '/../fixtures/fixtures.php');

use local_submissionaudit\domain\check\allowed_file_types;
use local_submissionaudit\domain\check\external_verdict;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\model\check_status;
use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\model\verdict;
use local_submissionaudit\fixtures\fake_verdict_client;
use local_submissionaudit\lib\domain\exception\invariant_violation_exception;

/**
 * @covers \local_submissionaudit\domain\check\minimum_words
 * @covers \local_submissionaudit\domain\check\allowed_file_types
 * @covers \local_submissionaudit\domain\check\external_verdict
 * @covers \local_submissionaudit\domain\model\result
 */
final class checks_test extends \basic_testcase {
    private function submission(string $text, array $files = []): submission {
        return new submission(1, 2, 3, $text, $files);
    }

    public function test_minimum_words(): void {
        $check = new minimum_words(3);
        $fail = $check->run($this->submission('one two'));
        $this->assertSame(check_status::FAIL, $fail->status);
        $this->assertSame('msg:minwords_fail', $fail->messagekey);
        $this->assertSame(['count' => 2, 'minimum' => 3], $fail->messageparams);
        $this->assertSame(2.0, $fail->score);

        $pass = $check->run($this->submission('one two three'));
        $this->assertTrue($pass->is_pass());
        $this->assertSame('minimum_words', $pass->checkname);

        $this->expectException(invariant_violation_exception::class);
        new minimum_words(0);
    }

    public function test_allowed_file_types(): void {
        $check = new allowed_file_types([' .PDF ', 'docx', '']);
        $this->assertSame('msg:filetypes_nofiles', $check->run($this->submission(''))->messagekey);
        $this->assertTrue($check->run($this->submission('', ['a.pdf', 'b.DOCX']))->is_pass());

        $fail = $check->run($this->submission('', ['a.pdf', 'virus.exe', 'notes']));
        $this->assertSame(check_status::FAIL, $fail->status);
        $this->assertSame(['files' => 'virus.exe, notes'], $fail->messageparams);
        $this->assertSame(2.0, $fail->score);

        $this->expectException(invariant_violation_exception::class);
        new allowed_file_types(['', ' ']);
    }

    public function test_external_verdict(): void {
        $client = new fake_verdict_client(new verdict(false, 0.87, 'Likely copied'));
        $result = (new external_verdict($client))->run($this->submission('x'));
        $this->assertSame(check_status::FAIL, $result->status);
        $this->assertSame(0.87, $result->score);
        $this->assertSame(['message' => 'Likely copied'], $result->messageparams);
        $this->assertCount(1, $client->asked);

        $pass = (new external_verdict(new fake_verdict_client(new verdict(true, null, 'ok'))))->run($this->submission('x'));
        $this->assertTrue($pass->is_pass());

        $error = (new external_verdict(new fake_verdict_client(null, 'HTTP 503')))->run($this->submission('x'));
        $this->assertSame(check_status::ERROR, $error->status);
        $this->assertSame(['error' => 'HTTP 503'], $error->messageparams);
    }
}
