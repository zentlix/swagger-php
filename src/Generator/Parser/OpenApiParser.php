<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Analysers\DocBlockAnnotationFactory;
use OpenApi\Analysers\ReflectionAnalyser;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi as OpenApiAnnotation;
use OpenApi\Generator as OpenApiGenerator;
use OpenApi\Util;
use Psr\Log\LoggerAwareTrait;

final class OpenApiParser implements ParserInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly OpenApiOptions $options
    ) {
    }

    public function parse(OpenApiAnnotation $openApi, Analysis $analysis): void
    {
        $generator = new OpenApiGenerator($this->logger);

        $generator
            ->setVersion($this->options->version)
            ->setConfig($this->options->config)
            ->setAnalyser(new ReflectionAnalyser([new DocBlockAnnotationFactory(), new AttributeAnnotationFactory()]))
            ->generate(
                Util::finder($this->options->paths, $this->options->exclude, $this->options->pattern),
                $analysis
            );
    }
}
