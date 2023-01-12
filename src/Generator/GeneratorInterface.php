<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator;

use OpenApi\Annotations\OpenApi;

interface GeneratorInterface
{
    public function generate(): OpenApi;
}
