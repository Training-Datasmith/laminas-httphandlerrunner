<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Emitter;

use Laminas\Http_Handler_Runner\Exception;
use Psr\Http\Message\Response_Interface;
use Return_Type_Will_Change;
use SplStack;
/**
 * Provides an EmitterInterface implementation that acts as a stack of Emitters.
 *
 * The implementations emit() method iterates itself.
 *
 * When iterating the stack, the first emitter to return a boolean
 * true value will short-circuit iteration.
 *
 * @template-extends SplStack<EmitterInterface>
 * @final
 */
class Emitter_Stack extends SplStack implements Emitter_Interface
{
    /**
     * Emit a response
     *
     * Loops through the stack, calling emit() on each; any that return a
     * boolean true value will short-circuit, skipping any remaining emitters
     * in the stack.
     *
     * As such, return a boolean false value from an emitter to indicate it
     * cannot emit the response, allowing the next emitter to try.
     */
    public function emit(Response_Interface $response): bool
    {
        foreach ($this as $emitter) {
            if (false !== $emitter->emit($response)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Set an emitter on the stack by index.
     *
     * @param int $offset
     * @param EmitterInterface $value
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[Return_Type_Will_Change]
    public function offsetSet($offset, $value): void
    {
        $this->validate_emitter($value);
        parent::offsetSet($offset, $value);
    }
    /**
     * Push an emitter to the stack.
     *
     * @param EmitterInterface $value
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[Return_Type_Will_Change]
    public function push($value): void
    {
        $this->validate_emitter($value);
        parent::push($value);
    }
    /**
     * Unshift an emitter to the stack.
     *
     * @param EmitterInterface $value
     * @throws Exception\InvalidEmitterException If not an EmitterInterface instance.
     */
    #[Return_Type_Will_Change]
    public function unshift($value): void
    {
        $this->validate_emitter($value);
        parent::unshift($value);
    }
    /**
     * Validate that an emitter implements EmitterInterface.
     *
     * @throws Exception\InvalidEmitterException For non-emitter instances.
     * @psalm-assert EmitterInterface $emitter
     */
    private function validate_emitter(mixed $emitter): void
    {
        if (!$emitter instanceof Emitter_Interface) {
            throw Exception\Invalid_Emitter_Exception::for_emitter($emitter);
        }
    }
}