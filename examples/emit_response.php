<?php

declare(strict_types=1);

/**
 * Example: emitting PSR-7 responses to the SAPI with laminas-httphandlerrunner.
 *
 * Run from the laminas-httphandlerrunner project root:
 *   php examples/emit_response.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\HttpHandlerRunner\Emitter\SapiStreamEmitter;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\TextResponse;

// --- Demonstrate emitter stack ---
$stack = new EmitterStack();
// SapiStreamEmitter handles responses with Content-Range header (streaming/partial content)
$stack->push(new SapiStreamEmitter());
// SapiEmitter handles all other responses
$stack->push(new SapiEmitter());

// --- Build a JSON response ---
$response = new JsonResponse(['message' => 'Hello, World!', 'status' => 'ok'], 200, [
    'X-Powered-By' => ['Laminas'],
]);

echo "Response status: " . $response->get_status_code() . "\n";
echo "Content-Type:    " . $response->get_header_line('Content-Type') . "\n";
echo "Body:            " . (string) $response->get_body() . "\n\n";

// In a real application, the emitter would send headers and body to the SAPI:
// $stack->emit($response);
//
// The EmitterStack iterates emitters; the first one that can emit the response
// handles it and returns true, stopping further processing.
//
// Typical usage in a PSR-15 dispatcher:
//
//   $request  = ServerRequestFactory::fromGlobals();
//   $response = $application->handle($request);
//   (new SapiEmitter())->emit($response);

echo "EmitterStack resolves emitters LIFO (last pushed runs first).\n";
echo "SapiStreamEmitter handles responses with Content-Range header.\n";
echo "SapiEmitter handles all other responses.\n";
