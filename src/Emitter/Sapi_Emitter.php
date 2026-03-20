<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner\Emitter;

use Psr\Http\Message\Response_Interface;
/** @final */
class Sapi_Emitter implements Emitter_Interface
{
    use Sapi_Emitter_Trait;
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
        $this->emit_body($response);
        return true;
    }
    /**
     * Emit the message body.
     */
    private function emit_body(Response_Interface $response): void
    {
        echo $response->get_body();
    }
}