<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Generator\Parser;

final class ParserRegistry implements ParserRegistryInterface
{
    /**
     * @var ParserInterface[]
     */
    private array $parsers = [];

    public function addParser(ParserInterface $parser): void
    {
        if (!$this->hasParser($parser::class)) {
            $this->parsers[$parser::class] = $parser;
        }
    }

    /**
     * @psalm-return ParserInterface[]
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     * @param class-string $parser
     */
    public function hasParser(string $parser): bool
    {
        return isset($this->parsers[$parser]);
    }
}
