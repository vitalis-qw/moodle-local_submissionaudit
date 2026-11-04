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

namespace local_submissionaudit\infrastructure\http;

use local_submissionaudit\domain\model\submission;
use local_submissionaudit\domain\model\verdict;
use local_submissionaudit\domain\port\verdict_client_interface;

/**
 * Moodle's curl class honours proxy settings and the blocked-hosts list.
 *
 * Request body: {"submissionid": 1, "text": "...", "filenames": ["a.pdf"]}.
 * Expected answer: {"status": "pass"|"fail", "score": 0.0|null, "message": "..."}.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn <vitalis.qw@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class curl_verdict_client implements verdict_client_interface {
    /** @var int Seconds before giving up on the endpoint. */
    /** @var int Timeout. */
    public const TIMEOUT = 20;

    /**
     * Constructor.
     *
     * @param string $url Endpoint URL; empty means the client is unconfigured.
     * @param string $token Bearer token, may be empty.
     */
    public function __construct(
        /** @var string Endpoint URL; empty means the client is unconfigured. */
        private readonly string $url,
        /** @var string Bearer token, may be empty. */
        private readonly string $token,
    ) {
    }

    /**
     * Verdict.
     *
     * @param submission $submission Submission.
     * @return verdict
     */
    public function verdict(submission $submission): verdict {
        if ($this->url === '') {
            throw new \RuntimeException('No external verdict endpoint configured');
        }
        $curl = new \curl();
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        $curl->setHeader($headers);
        $body = json_encode([
            'submissionid' => $submission->get_id(),
            'text' => $submission->plain_text(),
            'filenames' => $submission->filenames(),
        ]);
        $response = $curl->post($this->url, $body, ['CURLOPT_TIMEOUT' => self::TIMEOUT]);
        if ($curl->get_errno() !== 0) {
            throw new \RuntimeException($curl->error ?: 'curl error ' . $curl->get_errno());
        }
        $info = $curl->get_info();
        $httpcode = (int) ($info['http_code'] ?? 0);
        if ($httpcode < 200 || $httpcode >= 300) {
            throw new \RuntimeException('HTTP ' . $httpcode);
        }
        $data = json_decode((string) $response, true);
        if (!is_array($data) || !isset($data['status']) || !in_array($data['status'], ['pass', 'fail'], true)) {
            throw new \RuntimeException('Unexpected response body');
        }
        return new verdict(
            $data['status'] === 'pass',
            isset($data['score']) && is_numeric($data['score']) ? (float) $data['score'] : null,
            isset($data['message']) ? (string) $data['message'] : '',
        );
    }
}
