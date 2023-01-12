<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Renderer;

use Spiral\OpenApi\Exception\RenderException;

class Renderer
{
    /**
     * @psalm-var array<non-empty-string, RendererInterface>
     */
    private array $renderers = [];

    /**
     * @psalm-param non-empty-string $format
     */
    public function addRenderer(string $format, RendererInterface $renderer): void
    {
        if (!$this->hasRenderer($format)) {
            $this->renderers[$format] = $renderer;
        }
    }

    /**
     * @psalm-param non-empty-string $format
     */
    public function hasRenderer(string $format): bool
    {
        return isset($this->renderers[$format]);
    }

    /**
     * @psalm-param non-empty-string $format
     *
     * @throws RenderException
     */
    public function render(string $format): string
    {
        if (!$this->hasRenderer($format)) {
            throw new RenderException(sprintf('Format `%s` is not supported.', $format));
        }

        return $this->renderers[$format]->render();
    }
}
