<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Renderer;

use Spiral\OpenApi\Generator\GeneratorInterface;

final class YamlRenderer implements RendererInterface
{
    public const FORMAT = 'yaml';

    public function __construct(
        private readonly GeneratorInterface $generator
    ) {
    }

    public function render(): string
    {
        return $this->generator->generate()->toYaml();
    }
}
