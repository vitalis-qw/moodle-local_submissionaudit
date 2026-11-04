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

namespace local_submissionaudit\domain;

use local_submissionaudit\domain\check\allowed_file_types;
use local_submissionaudit\domain\check\external_verdict;
use local_submissionaudit\domain\check\minimum_words;
use local_submissionaudit\domain\port\verdict_client_interface;

/**
 * Domain tests: no database, no Moodle services.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_submissionaudit\domain\auditor
 * @covers     \local_submissionaudit\domain\submission
 * @covers     \local_submissionaudit\domain\check\minimum_words
 * @covers     \local_submissionaudit\domain\check\allowed_file_types
 * @covers     \local_submissionaudit\domain\check\external_verdict
 */
final class auditor_test extends \basic_testcase {
    /**
     * Build a submission with the given text and files.
     *
     * @param string $text
     * @param string[] $files
     * @return submission
     */
    private function submission(string $text, array $files = []): submission {
        return new submission(1, 2, 3, $text, $files);
    }

    /**
     * Word counting ignores markup and understands Unicode.
     */
    public function test_word_count_strips_html_and_counts_unicode(): void {
        $this->assertSame(0, $this->submission('')->word_count());
        $this->assertSame(0, $this->submission('<p>&nbsp;</p>')->word_count());
        $this->assertSame(3, $this->submission('<p>one <b>two</b></p><p>three</p>')->word_count());
        $this->assertSame(4, $this->submission("Ім'я naïve don't 42")->word_count());
    }

    /**
     * Minimum words passes and fails with the count in the message.
     */
    public function test_minimum_words(): void {
        $check = new minimum_words(3);

        $fail = $check->run($this->submission('too short'));
        $this->assertSame(result::STATUS_FAIL, $fail->status);
        $this->assertSame('msg:minwords_fail', $fail->messagekey);
        $this->assertSame(['count' => 2, 'minimum' => 3], $fail->messageparams);
        $this->assertSame(2.0, $fail->score);

        $pass = $check->run($this->submission('long enough now'));
        $this->assertTrue($pass->is_pass());
        $this->assertSame('msg:minwords_pass', $pass->messagekey);
    }

    /**
     * File type check compares extensions case-insensitively and lists offenders.
     */
    public function test_allowed_file_types(): void {
        $check = new allowed_file_types(['.PDF', 'docx', '']);

        $this->assertSame('msg:filetypes_nofiles', $check->run($this->submission('x'))->messagekey);

        $pass = $check->run($this->submission('x', ['essay.pdf', 'Notes.DOCX']));
        $this->assertTrue($pass->is_pass());
        $this->assertSame(['count' => 2], $pass->messageparams);

        $fail = $check->run($this->submission('x', ['essay.pdf', 'virus.exe', 'README']));
        $this->assertSame(result::STATUS_FAIL, $fail->status);
        $this->assertSame(['files' => 'virus.exe, README'], $fail->messageparams);
        $this->assertSame(2.0, $fail->score);
    }

    /**
     * An empty allowed list is a configuration error, not a silent pass-all.
     */
    public function test_allowed_file_types_rejects_empty_list(): void {
        $this->expectException(\InvalidArgumentException::class);
        new allowed_file_types([' ', '.']);
    }

    /**
     * The external check maps verdicts and turns transport failures into errors.
     */
    public function test_external_verdict(): void {
        $client = new class implements verdict_client_interface {
            /** @var verdict|\RuntimeException */
            public $answer;

            public function verdict(submission $submission): verdict {
                if ($this->answer instanceof \RuntimeException) {
                    throw $this->answer;
                }
                return $this->answer;
            }
        };
        $check = new external_verdict($client);

        $client->answer = new verdict(false, 0.87, '87% similarity');
        $fail = $check->run($this->submission('x'));
        $this->assertSame(result::STATUS_FAIL, $fail->status);
        $this->assertSame(0.87, $fail->score);
        $this->assertSame(['message' => '87% similarity'], $fail->messageparams);

        $client->answer = new verdict(true, null, 'ok');
        $this->assertTrue($check->run($this->submission('x'))->is_pass());

        $client->answer = new \RuntimeException('HTTP 503');
        $error = $check->run($this->submission('x'));
        $this->assertSame(result::STATUS_ERROR, $error->status);
        $this->assertSame(['error' => 'HTTP 503'], $error->messageparams);
    }

    /**
     * One broken check does not hide the others.
     */
    public function test_auditor_isolates_a_throwing_check(): void {
        $broken = new class implements check_interface {
            public function name(): string {
                return 'broken';
            }

            public function run(submission $submission): result {
                throw new \LogicException('boom');
            }
        };
        $auditor = new auditor([new minimum_words(1), $broken, new allowed_file_types(['pdf'])]);

        $results = $auditor->audit($this->submission('hello', ['a.pdf']));

        $this->assertCount(3, $results);
        $this->assertTrue($results[0]->is_pass());
        $this->assertSame(result::STATUS_ERROR, $results[1]->status);
        $this->assertSame('broken', $results[1]->checkname);
        $this->assertSame(['error' => 'boom'], $results[1]->messageparams);
        $this->assertTrue($results[2]->is_pass());
    }

    /**
     * The auditor refuses things that are not checks.
     */
    public function test_auditor_rejects_non_checks(): void {
        $this->expectException(\InvalidArgumentException::class);
        new auditor([new \stdClass()]);
    }

    /**
     * A result with an unknown status cannot exist.
     */
    public function test_result_validates_status(): void {
        $this->expectException(\InvalidArgumentException::class);
        new result('x', 'maybe', null, 'msg:x');
    }
}
