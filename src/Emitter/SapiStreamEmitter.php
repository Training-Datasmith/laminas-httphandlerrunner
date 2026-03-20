<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Emitter;

use function flush;
use function preg_match;
use Psr\Http\Message\Response_Interface;
use function strlen;
use function substr;
/**
 * @psalm-type ParsedRangeType = array{0:string,1:int,2:int,3:'*'|int}
 * @final
 */
class Sapi_Stream_Emitter implements Emitter_Interface
{
    use Sapi_Emitter_Trait;
    public function __construct(
        /** @param int Maximum output buffering size for each iteration. */
        private int $max_buffer_length = 8192
    )
    {
    }
    /**
     * Emits a response for a PHP SAPI environment.
     *
     * Emits the status line and headers via the header() function, and the
     * body content via the output buffer.
     */
    public function emit(Response_Interface $response): bool
    {
        $this->assert_no_previous_output();
        $this->emit_headers($response);
        $this->emit_status_line($response);
        flush();
        $range = $this->parse_content_range($response->get_header_line('Content-Range'));
        if (null === $range || 'bytes' !== $range[0]) {
            $this->emit_body($response);
            return true;
        }
        $this->emit_body_range($range, $response);
        return true;
    }
    /**
     * Emit the message body.
     */
    private function emit_body(Response_Interface $response): void
    {
        $body = $response->get_body();
        if ($body->is_seekable()) {
            $body->rewind();
        }
        if (!$body->is_readable()) {
            echo $body;
            return;
        }
        while (!$body->eof()) {
            echo $body->read($this->max_buffer_length);
        }
    }
    /**
     * Emit a range of the message body.
     *
     * @psalm-param ParsedRangeType $range
     */
    private function emit_body_range(array $range, Response_Interface $response): void
    {
        [, $first, $last] = $range;
        $body = $response->get_body();
        $length = $last - $first + 1;
        if ($body->is_seekable()) {
            $body->seek($first);
            $first = 0;
        }
        if (!$body->is_readable()) {
            echo substr((string) $body->get_contents(), $first, $length);
            return;
        }
        $remaining = $length;
        while ($remaining >= $this->max_buffer_length && !$body->eof()) {
            $contents = $body->read($this->max_buffer_length);
            $remaining -= strlen($contents);
            echo $contents;
        }
        if ($remaining > 0 && !$body->eof()) {
            echo $body->read($remaining);
        }
    }
    /**
     * Parse content-range header
     * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
     *
     * @return null|array [unit, first, last, length]; returns null if no
     *     content range or an invalid content range is provided
     * @psalm-return null|ParsedRangeType
     */
    private function parse_content_range(string $header): ?array
    {
        if (!preg_match('/(?P<unit>[\w]+)\s+(?P<first>\d+)-(?P<last>\d+)\/(?P<length>\d+|\*)/', $header, $matches)) {
            return null;
        }
        return [$matches['unit'], (int) $matches['first'], (int) $matches['last'], $matches['length'] === '*' ? '*' : (int) $matches['length']];
    }
}