<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

interface ParserRegistryInterface
{
    public function addParser(ParserInterface $parser): void;

    /**
     * @psalm-return ParserInterface[]
     */
    public function getParsers(): array;

    /**
     * @param class-string $parser
     */
    public function hasParser(string $parser): bool;
}
