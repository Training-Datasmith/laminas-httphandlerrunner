<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Exception;

use RuntimeException;
use function sprintf;
/** @final */
class Emitter_Exception extends RuntimeException implements Exception_Interface
{
    public static function for_headers_sent(string $filename, int $line): self
    {
        return new self(sprintf('Unable to emit response; headers already sent in %s:%d', $filename, $line));
    }
    public static function for_output_sent(): self
    {
        return new self('Output has been emitted previously; cannot emit response');
    }
}