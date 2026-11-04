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

namespace local_submissionaudit\adapter;

use local_submissionaudit\domain\port\verdict_client_interface;
use local_submissionaudit\domain\submission;
use local_submissionaudit\domain\verdict;

/**
 * Adapter: asks an HTTPS endpoint for a verdict using Moodle's curl wrapper,
 * so proxy settings and the blocked-hosts list are honoured.
 *
 * Request body: {"submissionid": 1, "text": "...", "filenames": ["a.pdf"]}.
 * Expected answer: {"status": "pass"|"fail", "score": 0.0|null, "message": "..."}.
 *
 * @package    local_submissionaudit
 * @copyright  2026 Vitalii Kuzmyn
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class curl_verdict_client implements verdict_client_interface {
    /** @var int Seconds before giving up on the endpoint. */
    public const TIMEOUT = 20;

    /**
     * @param string $url Endpoint URL.
     * @param string $token Bearer token, may be empty.
     */
    public function __construct(private readonly string $url, private readonly string $token) {
    }

    /**
     * {@inheritDoc}
     */
    public function verdict(submission $submission): verdict {
        $curl = new \curl();
        $headers = ['Content-Type: application/json', 'Accept: application/json'];
        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }
        $curl->setHeader($headers);

        $body = json_encode([
            'submissionid' => $submission->id,
            'text' => $submission->plain_text(),
            'filenames' => $submission->filenames,
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
            isset($data['message']) ? (string) $data['message'] : ''
        );
    }
}
