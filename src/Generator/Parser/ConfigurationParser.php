<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi as OpenApiAnnotation;
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\Merger;

final class ConfigurationParser implements ParserInterface
{
    public function __construct(
        private readonly SwaggerConfig $config
    ) {
    }

    public function parse(OpenApiAnnotation $openApi, Analysis $analysis): void
    {
        Merger::merge($openApi, $this->config->getDocumentation());
    }
}
