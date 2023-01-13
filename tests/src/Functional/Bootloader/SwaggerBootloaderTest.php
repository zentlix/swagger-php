<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Tests\Functional\Bootloader;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\OpenApi\Bootloader\SwaggerBootloader;
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\Generator;
use Spiral\OpenApi\Generator\GeneratorInterface;
use Spiral\OpenApi\Generator\Parser\ConfigurationParser;
use Spiral\OpenApi\Generator\Parser\OpenApiParser;
use Spiral\OpenApi\Generator\Parser\ParserRegistry;
use Spiral\OpenApi\Generator\Parser\ParserRegistryInterface;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\Renderer;
use Spiral\OpenApi\Renderer\YamlRenderer;
use Spiral\OpenApi\Tests\Functional\TestCase;
use Spiral\Views\Config\ViewsConfig;

final class SwaggerBootloaderTest extends TestCase
{
    public function testCacheBootloaderShouldBeRegistered(): void
    {
        $this->assertBootloaderRegistered(CacheBootloader::class);
    }

    public function testRendererShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(Renderer::class, Renderer::class);
    }

    public function testGeneratorShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(GeneratorInterface::class, Generator::class);
    }

    public function testParserRegistryShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(ParserRegistryInterface::class, ParserRegistry::class);
    }

    public function testOpenApiParserShouldBeBound(): void
    {
        $this->assertContainerBound(OpenApiParser::class, OpenApiParser::class);
    }

    public function testViewsDirectoryShouldBeAdded(): void
    {
        $this->assertConfigHasFragments(
            ViewsConfig::CONFIG,
            [
                'namespaces' => [
                    'default' => [
                        str_replace('\\', '/', dirname(__DIR__)) . '/../app/views/'
                    ],
                    'swagger' => [
                        dirname(__DIR__, 4) . '/views'
                    ]
                ]
            ]
        );
    }

    public function testDefaultConfigShouldBeDefined(): void
    {
        $this->assertConfigMatches(SwaggerConfig::CONFIG, [
            'documentation' => [
                'info' => [
                    'title' => 'My App',
                    'description' => 'API documentation',
                    'version' => '1.0.0',
                ],
            ],
            'parsers' => [
                ConfigurationParser::class,
                OpenApiParser::class,
            ],
            'renderers' => [
                JsonRenderer::FORMAT => JsonRenderer::class,
                YamlRenderer::FORMAT => YamlRenderer::class,
                HtmlRenderer::FORMAT => HtmlRenderer::class,
            ],
            'paths' => [
                ($this->getContainer()->get(DirectoriesInterface::class))->get('app'),
            ],
            'exclude' => null,
            'pattern' => null,
            'version' => null,
            'cache_item_id' => SwaggerConfig::DEFAULT_CACHE_ITEM_ID,
            'generator_config' => [
                'operationId' => [
                    'hash' => true,
                ],
            ],
            'use_cache' => true,
        ]);
    }

    public function testAddPath(): void
    {
        $bootloader = $this->getContainer()->get(SwaggerBootloader::class);
        $bootloader->addPath('foo/bar');

        $this->assertSame('foo/bar', $this->getConfig(SwaggerConfig::CONFIG)['paths'][1]);
    }
}
