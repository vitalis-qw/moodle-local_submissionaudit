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

namespace local_submissionaudit\domain;

use local_submissionaudit\domain\model\submission;

/**
 * Tests for the submission entity.
 *
 * @covers \local_submissionaudit\domain\model\submission
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class submission_test extends \basic_testcase {
    /**
     * Submission.
     *
     * @param string $text Text.
     * @param array $files Files.
     * @return submission
     */
    private function submission(string $text, array $files = []): submission {
        return new submission(1, 2, 3, $text, $files);
    }

    /**
     * Test: word count strips html and counts unicode.
     */
    public function test_word_count_strips_html_and_counts_unicode(): void {
        $this->assertSame(0, $this->submission('')->word_count());
        $this->assertSame(0, $this->submission('<p>&nbsp;</p>')->word_count());
        $this->assertSame(3, $this->submission('<p>one <b>two</b></p><p>three</p>')->word_count());
        $this->assertSame(4, $this->submission("Ім'я naïve don't 42")->word_count());
    }

    /**
     * Test: file extensions are lower case without dot.
     */
    public function test_file_extensions_are_lower_case_without_dot(): void {
        $this->assertSame(['pdf', 'docx', ''], $this->submission('', ['a.PDF', 'b.c.docx', 'README'])->file_extensions());
        $this->assertSame(1, $this->submission('', ['a.pdf'])->get_id());
        $this->assertSame(2, $this->submission('')->assignid());
        $this->assertSame(3, $this->submission('')->userid());
    }
}
