<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner;

use Laminas\Http_Handler_Runner\Emitter\Emitter_Interface;
use Psr\Http\Message\Response_Interface;
use Psr\Http\Message\Server_Request_Interface;
use Psr\Http\Server\Request_Handler_Interface;
use Throwable;
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
final class Request_Handler_Runner implements Request_Handler_Runner_Interface
{
    /**
     * A factory capable of generating an error response in the scenario that
     * the $serverRequestFactory raises an exception during generation of the
     * request instance.
     *
     * The factory will receive the Throwable or Exception that caused the error,
     * and must return a Psr\Http\Message\ResponseInterface instance.
     *
     * @var callable(Throwable):ResponseInterface
     */
    private $server_request_error_response_generator;
    /**
     * A factory capable of generating a Psr\Http\Message\ServerRequestInterface instance.
     * The factory will not receive any arguments.
     *
     * @var callable():ServerRequestInterface
     */
    private $server_request_factory;
    /**
     * @param callable():ServerRequestInterface     $serverRequestFactory
     * @param callable(Throwable):ResponseInterface $serverRequestErrorResponseGenerator
     */
    public function __construct(
        /**
         * A request handler to run as the application.
         */
        private readonly Request_Handler_Interface $handler,
        private readonly Emitter_Interface $emitter,
        callable $server_request_factory,
        callable $server_request_error_response_generator
    )
    {
        $this->server_request_factory = $server_request_factory;
        $this->server_request_error_response_generator = $server_request_error_response_generator;
    }
    public function run(): void
    {
        try {
            $request = ($this->server_request_factory)();
        } catch (Throwable $e) {
            // Error in generating the request
            $this->emit_marshal_server_request_exception($e);
            return;
        }
        $response = $this->handler->handle($request);
        $this->emitter->emit($response);
    }
    private function emit_marshal_server_request_exception(Throwable $exception): void
    {
        $response = ($this->server_request_error_response_generator)($exception);
        $this->emitter->emit($response);
    }
}