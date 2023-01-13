<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container\Autowire;
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\GeneratorInterface;
use Spiral\OpenApi\Generator\Parser\OpenApiOptions;
use Spiral\OpenApi\Generator\Parser\OpenApiParser;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\YamlRenderer;

final class SwaggerConfigTest extends TestCase
{
    public function testGetDocumentation(): void
    {
        $config = new SwaggerConfig(['documentation' => [
            'info' => ['some'],
            'components' => ['other']
        ]]);

        $this->assertSame([
            'info' => ['some'],
            'components' => ['other']
        ], $config->getDocumentation());
    }

    public function testGetParsers(): void
    {
        $parsers = [
            OpenApiParser::class,
            new OpenApiParser(new OpenApiOptions(['foo'], [])),
            new Autowire(OpenApiParser::class)
        ];

        $config = new SwaggerConfig(['parsers' => $parsers]);

        $this->assertSame($parsers, $config->getParsers());
    }

    public function testGetRenderers(): void
    {
        $renderers = [
            'html' => HtmlRenderer::class,
            'yaml' => new YamlRenderer($this->createMock(GeneratorInterface::class)),
            'json' => new Autowire(JsonRenderer::class)
        ];

        $config = new SwaggerConfig(['renderers' => $renderers]);

        $this->assertSame($renderers, $config->getRenderers());
    }

    public function testGetPaths(): void
    {
        $config = new SwaggerConfig(['paths' => ['foo', 'bar']]);

        $this->assertSame(['foo', 'bar'], $config->getPaths());
    }

    /**
     * @dataProvider excludeDataProvider
     */
    public function testGetExclude(mixed $exclude): void
    {
        $config = new SwaggerConfig(['exclude' => $exclude]);

        $this->assertSame($exclude, $config->getExclude());
    }

    public function testGetPattern(): void
    {
        $config = new SwaggerConfig(['pattern' => null]);
        $this->assertNull($config->getPattern());

        $config = new SwaggerConfig(['pattern' => 'foo']);
        $this->assertSame('foo', $config->getPattern());
    }

    public function testGetCacheItemId(): void
    {
        $config = new SwaggerConfig(['cache_item_id' => 'some']);

        $this->assertSame('some', $config->getCacheItemId());
    }

    public function testGetVersion(): void
    {
        $config = new SwaggerConfig(['version' => null]);
        $this->assertNull($config->getVersion());

        $config = new SwaggerConfig(['version' => '1.0.0']);
        $this->assertSame('1.0.0', $config->getVersion());
    }

    public function testGetGeneratorConfig(): void
    {
        $config = new SwaggerConfig(['generator_config' => [
            'foo' => 'bar'
        ]]);

        $this->assertSame([
            'foo' => 'bar'
        ], $config->getGeneratorConfig());
    }

    public function testUseCache(): void
    {
        $config = new SwaggerConfig(['use_cache' => true]);

        $this->assertTrue($config->useCache());
    }

    public function excludeDataProvider(): \Traversable
    {
        yield [null];
        yield ['foo'];
        yield [['foo', 'bar']];
    }
}
