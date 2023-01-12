<?php

declare(strict_types=1);

namespace Spiral\OpenApi\Bootloader;

use Psr\SimpleCache\CacheInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\Generator;
use Spiral\OpenApi\Generator\GeneratorInterface;
use Spiral\OpenApi\Generator\Options;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\Renderer;
use Spiral\OpenApi\Renderer\RendererInterface;
use Spiral\OpenApi\Renderer\YamlRenderer;
use Spiral\Views\Bootloader\ViewsBootloader;

final class SwaggerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        Renderer::class => [self::class, 'initRenderer'],
        GeneratorInterface::class => [self::class, 'initGenerator'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->initConfig();
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

    private function initConfig(): void
    {
        $this->config->setDefaults(
            SwaggerConfig::CONFIG,
            [
                'renderers' => [
                    JsonRenderer::FORMAT => JsonRenderer::class,
                    YamlRenderer::FORMAT => YamlRenderer::class,
                    HtmlRenderer::FORMAT => HtmlRenderer::class,
                ],
                'paths' => [],
                'exclude' => null,
                'pattern' => null,
                'version' => null,
                'cache_item_id' => SwaggerConfig::DEFAULT_CACHE_ITEM_ID,
                'generator_config' => [
                    'operationId' => [
                        'hash' => true,
                    ],
                ],
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

    private function initGenerator(SwaggerConfig $config, CacheInterface $cache): GeneratorInterface
    {
        $options = new Options(
            paths: $config->getPaths(),
            config: $config->getGeneratorConfig(),
            version: $config->getVersion(),
            exclude: $config->getExclude(),
            pattern: $config->getPattern()
        );

        return new Generator($cache, $options, $config->getCacheItemId());
    }
}
