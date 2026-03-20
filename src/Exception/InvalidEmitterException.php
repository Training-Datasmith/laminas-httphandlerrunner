<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Exception;

use function get_debug_type;
use InvalidArgumentException;
use Laminas\Http_Handler_Runner\Emitter;
use function sprintf;
/** @final */
class Invalid_Emitter_Exception extends InvalidArgumentException implements Exception_Interface
{
    /**
     * @param mixed $emitter Invalid emitter type
     */
    public static function for_emitter(mixed $emitter): self
    {
        return new self(sprintf('%s can only compose %s implementations; received %s', Emitter\Emitter_Stack::class, Emitter\Emitter_Interface::class, get_debug_type($emitter)));
    }
}