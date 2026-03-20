<?php

declare (strict_types=1);
namespace Laminas\Http_Handler_Runner;

/** @final */
class Config_Provider
{
    public function __invoke(): array
    {
        return ['dependencies' => $this->get_dependencies()];
    }
    public function get_dependencies(): array
    {
        return [];
    }
}