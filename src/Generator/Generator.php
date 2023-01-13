<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator;

use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\OpenApi\Generator\Parser\ParserRegistryInterface;

final class Generator implements GeneratorInterface
{
    /**
     * @psalm-param non-empty-string $cacheItemId
     */
    public function __construct(
        private readonly ParserRegistryInterface $parsers,
        private readonly string $cacheItemId,
        private readonly ?CacheInterface $cache = null,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function generate(): OpenApi
    {
        $item = $this->cache?->get($this->cacheItemId);
        if (null !== $item) {
            return $item;
        }

        $context = new Context();
        $openApi = new OpenApi(['_context' => $context]);

        $analysis = new Analysis([], $context);
        $analysis->addAnnotation($openApi, $context);

        foreach ($this->parsers->getParsers() as $parser) {
            $parser->parse($openApi, $analysis);
        }

        $this->cache?->set($this->cacheItemId, $openApi);

        return $openApi;
    }
}
