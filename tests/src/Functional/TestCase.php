<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Tests\Functional;

use Spiral\OpenApi\Bootloader\SwaggerBootloader;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__ . '/../';
    }

    public function defineBootloaders(): array
    {
        return [
            SwaggerBootloader::class,
        ];
    }
}
