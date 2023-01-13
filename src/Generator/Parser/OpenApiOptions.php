<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

use Symfony\Component\Finder\Finder;

final class OpenApiOptions
{
    /**
     * @psalm-param array|Finder|non-empty-string $paths
     * @psalm-param ?non-empty-string $version
     * @psalm-param array $config
     */
    public function __construct(
        public readonly array|Finder|string $paths,
        public readonly array $config,
        public readonly ?string $version = null,
        public readonly null|array|string $exclude = null,
        public readonly ?string $pattern = null
    ) {
    }
}
