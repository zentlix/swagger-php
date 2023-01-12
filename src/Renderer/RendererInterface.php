<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Renderer;

interface RendererInterface
{
    public function render(): string;
}
