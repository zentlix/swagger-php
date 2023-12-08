<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Bootloader;

use Psr\SimpleCache\CacheInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment\DebugMode;
use Spiral\Cache\Bootloader\CacheBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\Generator;
use Spiral\OpenApi\Generator\GeneratorInterface;
use Spiral\OpenApi\Generator\Parser\ConfigurationParser;
use Spiral\OpenApi\Generator\Parser\OpenApiOptions;
use Spiral\OpenApi\Generator\Parser\OpenApiParser;
use Spiral\OpenApi\Generator\Parser\ParserInterface;
use Spiral\OpenApi\Generator\Parser\ParserRegistry;
use Spiral\OpenApi\Generator\Parser\ParserRegistryInterface;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\Renderer;
use Spiral\OpenApi\Renderer\RendererInterface;
use Spiral\OpenApi\Renderer\YamlRenderer;
use Spiral\Views\Bootloader\ViewsBootloader;

final class SwaggerBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        CacheBootloader::class,
    ];

    protected const SINGLETONS = [
        Renderer::class => [self::class, 'initRenderer'],
        GeneratorInterface::class => [self::class, 'initGenerator'],
        ParserRegistryInterface::class => [self::class, 'initParserRegistry'],
    ];

    protected const BINDINGS = [
        OpenApiParser::class => [self::class, 'initOpenApiParser'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(DebugMode $debugMode, DirectoriesInterface $dirs): void
    {
        $this->initConfig($debugMode, $dirs);
    }

    public function boot(ViewsBootloader $views): void
    {
        $views->addDirectory('swagger', \dirname(__DIR__, 2).'/views');
    }

    /**
     * @psalm-param non-empty-string $path
     */
    public function addPath(string $path): void
    {
        $this->config->modify(
            SwaggerConfig::CONFIG,
            new Append('paths', null, $path)
        );
    }

    private function initConfig(DebugMode $debugMode, DirectoriesInterface $dirs): void
    {
        $this->config->setDefaults(
            SwaggerConfig::CONFIG,
            [
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
                    $dirs->get('app'),
                ],
                'exclude' => null,
                'pattern' => null,
                'version' => null,
                'cache_key' => SwaggerConfig::DEFAULT_CACHE_KEY,
                'generator_config' => [
                    'operationId' => [
                        'hash' => true,
                    ],
                ],
                'use_cache' => false === $debugMode->isEnabled(),
            ]
        );
    }

    private function initRenderer(SwaggerConfig $config, FactoryInterface $factory): Renderer
    {
        $renderer = new Renderer();

        foreach ($config->getRenderers() as $format => $formatRenderer) {
            $formatRenderer = match (true) {
                \is_string($formatRenderer) => $factory->make($formatRenderer),
                $formatRenderer instanceof Autowire => $formatRenderer->resolve($factory),
                default => $formatRenderer
            };

            \assert($formatRenderer instanceof RendererInterface);
            $renderer->addRenderer($format, $formatRenderer);
        }

        return $renderer;
    }

    private function initParserRegistry(SwaggerConfig $config, FactoryInterface $factory): ParserRegistry
    {
        $registry = new ParserRegistry();

        foreach ($config->getParsers() as $parser) {
            $parser = match (true) {
                \is_string($parser) => $factory->make($parser),
                $parser instanceof Autowire => $parser->resolve($factory),
                default => $parser
            };

            \assert($parser instanceof ParserInterface);
            $registry->addParser($parser);
        }

        return $registry;
    }

    private function initGenerator(
        ParserRegistryInterface $registry,
        CacheInterface $cache,
        SwaggerConfig $config
    ): GeneratorInterface {
        return new Generator($registry, $config->getCacheKey(), $config->useCache() ? $cache : null);
    }

    private function initOpenApiParser(SwaggerConfig $config): OpenApiParser
    {
        return new OpenApiParser(new OpenApiOptions(
            paths: $config->getPaths(),
            config: $config->getGeneratorConfig(),
            version: $config->getVersion(),
            exclude: $config->getExclude(),
            pattern: $config->getPattern()
        ));
    }
}
