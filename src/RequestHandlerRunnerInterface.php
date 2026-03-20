<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner;

/**
 * "Run" a request handler.
 *
 * The RequestHandlerRunner will marshal a request using the composed factory, and
 * then pass the request to the composed handler. Finally, it emits the response
 * returned by the handler using the composed emitter.
 *
 * If the factory for generating the request raises an exception or throwable,
 * then the runner will use the composed error response generator to generate a
 * response, based on the exception or throwable raised.
 */
interface Request_Handler_Runner_Interface
{
    /**
     * Run the application
     */
    public function run(): void;
}