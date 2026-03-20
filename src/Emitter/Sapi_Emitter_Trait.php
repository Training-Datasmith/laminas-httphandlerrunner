<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Emitter;

use function assert;
use function function_exists;
use function header;
use function headers_sent;
use function is_int;
use function is_string;
use Laminas\Http_Handler_Runner\Exception\Emitter_Exception;
use function ob_get_length;
use function ob_get_level;
use Psr\Http\Message\Response_Interface;
use function sprintf;
use function ucwords;
trait Sapi_Emitter_Trait
{
    /**
     * Checks to see if content has previously been sent.
     *
     * If either headers have been sent or the output buffer contains content,
     * raises an exception.
     *
     * @throws EmitterException If headers have already been sent.
     * @throws EmitterException If output is present in the output buffer.
     */
    private function assert_no_previous_output(): void
    {
        $filename = null;
        $line = null;
        if ($this->headers_sent($filename, $line)) {
            assert(is_string($filename) && is_int($line));
            throw Emitter_Exception::for_headers_sent($filename, $line);
        }
        if (ob_get_level() > 0 && ob_get_length() > 0) {
            throw Emitter_Exception::for_output_sent();
        }
    }
    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is available, it, too, is emitted.
     *
     * It is important to mention that this method should be called after
     * `emitHeaders()` in order to prevent PHP from changing the status code of
     * the emitted response.
     *
     * @see \Laminas\HttpHandlerRunner\Emitter\SapiEmitterTrait::emitHeaders()
     */
    private function emit_status_line(Response_Interface $response): void
    {
        $reason_phrase = $response->get_reason_phrase();
        $status_code = $response->get_status_code();
        $this->header(sprintf('HTTP/%s %d%s', $response->get_protocol_version(), $status_code, $reason_phrase ? ' ' . $reason_phrase : ''), true, $status_code);
    }
    /**
     * Emit response headers.
     *
     * Loops through each header, emitting each; if the header value
     * is an array with multiple values, ensures that each is sent
     * in such a way as to create aggregate headers (instead of replace
     * the previous).
     */
    private function emit_headers(Response_Interface $response): void
    {
        $status_code = $response->get_status_code();
        foreach ($response->get_headers() as $header => $values) {
            assert(is_string($header));
            $name = $this->filter_header($header);
            $first = $name !== 'Set-Cookie';
            foreach ($values as $value) {
                $this->header(sprintf('%s: %s', $name, $value), $first, $status_code);
                $first = false;
            }
        }
    }
    /**
     * Filter a header name to wordcase
     */
    private function filter_header(string $header): string
    {
        return ucwords($header, '-');
    }
    private function headers_sent(?string &$filename = null, ?int &$line = null): bool
    {
        if (function_exists('Laminas\HttpHandlerRunner\Emitter\headers_sent')) {
            // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
            return \Laminas\Http_Handler_Runner\Emitter\headers_sent($filename, $line);
        }
        return headers_sent($filename, $line);
    }
    private function header(string $header_name, bool $replace, int $status_code): void
    {
        if (function_exists('Laminas\HttpHandlerRunner\Emitter\header')) {
            // phpcs:ignore SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName
            \Laminas\Http_Handler_Runner\Emitter\header($header_name, $replace, $status_code);
            return;
        }
        header($header_name, $replace, $status_code);
    }
}