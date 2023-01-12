<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\OpenApi\Renderer\RendererInterface;

final class SwaggerConfig extends InjectableConfig
{
    public const CONFIG = 'swagger';
    public const DEFAULT_CACHE_ITEM_ID = 'cache_item_id';

    protected array $config = [
        'renderers' => [],
        'paths' => [],
        'exclude' => null,
        'pattern' => null,
        'version' => null,
        'cache_item_id' => self::DEFAULT_CACHE_ITEM_ID,
        'generator_config' => [
            'operationId' => [
                'hash' => true,
            ],
        ],
    ];

    /**
     * @psalm-return array<non-empty-string, string|RendererInterface|Autowire>
     */
    public function getRenderers(): array
    {
        return $this->config['renderers'] ?? [];
    }

    /**
     * @psalm-return non-empty-string[]
     */
    public function getPaths(): array
    {
        return $this->config['paths'] ?? [];
    }

    /**
     * @psalm-return array|non-empty-string|null
     */
    public function getExclude(): null|array|string
    {
        return $this->config['exclude'] ?? null;
    }

    /**
     * @psalm-return ?non-empty-string
     */
    public function getPattern(): ?string
    {
        return $this->config['pattern'] ?? null;
    }

    /**
     * @psalm-return non-empty-string
     */
    public function getCacheItemId(): string
    {
        return $this->config['cache_item_id'] ?? self::DEFAULT_CACHE_ITEM_ID;
    }

    /**
     * @psalm-return ?non-empty-string
     */
    public function getVersion(): ?string
    {
        return $this->config['version'] ?? null;
    }

    public function getGeneratorConfig(): array
    {
        return $this->config['generator_config'] ?? [];
    }
}
