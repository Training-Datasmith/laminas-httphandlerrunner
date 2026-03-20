# Architecture: laminas-httphandlerrunner

## Purpose
A thin PSR-15 request handler runner and PSR-7 response emitter. Bridges the gap between a PSR-7 `ServerRequest`, a PSR-15 `RequestHandlerInterface`, and SAPI output (headers + body).

## Directory Structure
```
src/
  Request_Handler_Runner.php          # Runs a PSR-15 handler for a server request, emits the response
  Request_Handler_Runner_Interface.php # Contract for the runner
  Emitter/
    Emitter_Interface.php             # emit(ResponseInterface): bool
    Sapi_Emitter.php                  # Sends headers via header() and body via echo
    Sapi_Stream_Emitter.php           # Streams large responses in configurable chunks
    Sapi_Emitter_Trait.php            # Shared header emission logic
    Emitter_Stack.php                 # Tries multiple emitters in order; stops on first success
  Exception/
    Emitter_Exception.php
    Invalid_Emitter_Exception.php
  Config_Provider.php                 # Mezzio/Laminas config provider
```

## Key Design Decisions
- **Emitter abstraction** — output is decoupled from the response object via `Emitter_Interface`. This allows substituting the SAPI emitter with test doubles, spool emitters, or streaming emitters.
- **Emitter stack** — `Emitter_Stack` implements a chain-of-responsibility: the first emitter that returns `true` wins. This lets callers prioritize a streaming emitter for large files with a fallback to the standard emitter.
- **Streaming** — `Sapi_Stream_Emitter` reads the response body in chunks to handle responses too large to buffer in memory.
- **Minimal surface area** — the package intentionally does nothing beyond running the handler and emitting the response, leaving middleware, routing, and DI to other packages.

## Extension Points
- Implement `Emitter_Interface` to add a custom emitter (e.g., for Swoole, RoadRunner, or test assertions).
- Compose multiple emitters via `Emitter_Stack`.
- Replace `Request_Handler_Runner` with a custom runner for async or streaming scenarios.

## Dependency Flow
```
Request_Handler_Runner::run()
  ├─ ServerRequest_Factory::fromGlobals() → ServerRequest
  ├─ PSR-15 RequestHandlerInterface::handle(ServerRequest) → Response
  └─ Emitter_Stack::emit(Response)
       └─ Sapi_Emitter::emit() → header() + echo body
```
