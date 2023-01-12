<?php

namespace Spiral\OpenApi\Controller;

use Psr\Http\Message\ResponseInterface;
use Spiral\Http\Exception\ClientException\BadRequestException;
use Spiral\Http\ResponseWrapper;
use Spiral\OpenApi\Exception\RenderException;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\Renderer;
use Spiral\OpenApi\Renderer\YamlRenderer;

final class DocumentationController
{
    public function __construct(
        private readonly ResponseWrapper $response,
        private readonly Renderer $renderer
    ) {
    }

    public function json(): ResponseInterface
    {
        try {
            return $this->response->json(json_decode($this->renderer->render(JsonRenderer::FORMAT), true));
        } catch (RenderException $e) {
            throw new BadRequestException($e->getMessage());
        }
    }

    public function html(): ResponseInterface
    {
        try {
            return $this->response->html($this->renderer->render(HtmlRenderer::FORMAT));
        } catch (RenderException $e) {
            throw new BadRequestException($e->getMessage());
        }
    }

    public function yaml(): ResponseInterface
    {
        try {
            $response = $this->response->create(200)->withHeader('Content-Type', 'text/x-yaml');
            $response->getBody()->write($this->renderer->render(YamlRenderer::FORMAT));

            return $response;
        } catch (RenderException $e) {
            throw new BadRequestException($e->getMessage());
        }
    }
}
