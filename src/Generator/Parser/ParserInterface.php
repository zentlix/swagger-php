<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;

interface ParserInterface
{
    public function parse(OpenApi $openApi, Analysis $analysis): void;
}
