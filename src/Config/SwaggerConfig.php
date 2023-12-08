<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Config;

use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;
use Spiral\OpenApi\Generator\Parser\ParserInterface;
use Spiral\OpenApi\Renderer\RendererInterface;

final class SwaggerConfig extends InjectableConfig
{
    public const CONFIG = 'swagger';
    public const DEFAULT_CACHE_KEY = 'swagger_docs';

    protected array $config = [
        'documentation' => [
            'info' => [
                'title' => 'My App',
                'description' => 'API documentation',
                'version' => '1.0.0',
            ],
        ],
        'parsers' => [],
        'renderers' => [],
        'paths' => [],
        'exclude' => null,
        'pattern' => null,
        'version' => null,
        'cache_key' => self::DEFAULT_CACHE_KEY,
        'generator_config' => [
            'operationId' => [
                'hash' => true,
            ],
        ],
        'use_cache' => true,
    ];

    public function getDocumentation(): array
    {
        return $this->config['documentation'] ?? [];
    }

    /**
     * @psalm-return array<non-empty-string, string|ParserInterface|Autowire>
     */
    public function getParsers(): array
    {
        return $this->config['parsers'] ?? [];
    }

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
    public function getCacheKey(): string
    {
        return $this->config['cache_key'] ?? self::DEFAULT_CACHE_KEY;
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

    public function useCache(): bool
    {
        return $this->config['use_cache'] ?? false;
    }
}
