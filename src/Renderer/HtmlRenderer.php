<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Renderer;

use Spiral\OpenApi\Generator\GeneratorInterface;
use Spiral\Views\ViewsInterface;

final class HtmlRenderer implements RendererInterface
{
    public const FORMAT = 'html';

    public function __construct(
        private readonly GeneratorInterface $generator,
        private readonly ViewsInterface $views
    ) {
    }

    public function render(array $config = []): string
    {
        return $this->views->render(
            'swagger:documentation',
            [
                'documentation' => ['spec' => json_decode($this->generator->generate()->toJson(), true)],
                'swagger_ui_config' => $config,
            ]
        );
    }
}
