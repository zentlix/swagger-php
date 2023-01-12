<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator as OpenApiGenerator;
use OpenApi\Util;
use Psr\Log\LoggerAwareTrait;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Spiral\OpenApi\Exception\SwaggerException;

final class Generator implements GeneratorInterface
{
    use LoggerAwareTrait;

    /**
     * @psalm-param non-empty-string $cacheItemId
     */
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly Options $options,
        private readonly string $cacheItemId
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws SwaggerException
     */
    public function generate(): OpenApi
    {
        $item = $this->cache->get($this->cacheItemId);
        if (null !== $item) {
            return $item;
        }

        $generator = new OpenApiGenerator($this->logger);
        $openApi = $generator
            ->setVersion($this->options->version)
            ->setConfig($this->options->config)
            ->setAnalyser(new ReflectionAnalyser([new DocBlockAnnotationFactory(), new AttributeAnnotationFactory()]))
            ->generate(Util::finder($this->options->paths, $this->options->exclude, $this->options->pattern));

        if (null === $openApi) {
            throw new SwaggerException('Unable to generate documentation. Check the definition of your specifications.');
        }

        $this->cache->set($this->cacheItemId, $openApi);

        return $openApi;
    }
}
