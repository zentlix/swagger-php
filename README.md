# Swagger-php

[![PHP Version Require](https://poser.pugx.org/zentlix/swagger-php/require/php)](https://packagist.org/packages/zentlix/swagger-php)
[![Latest Stable Version](https://poser.pugx.org/zentlix/swagger-php/v/stable)](https://packagist.org/packages/zentlix/swagger-php)
[![phpunit](https://github.com/zentlix/swagger-php/actions/workflows/phpunit.yml/badge.svg)](https://github.com/zentlix/swagger-php/actions)
[![psalm](https://github.com/zentlix/swagger-php/actions/workflows/psalm.yml/badge.svg)](https://github.com/zentlix/swagger-php/actions)
[![Codecov](https://codecov.io/gh/zentlix/swagger-php/branch/master/graph/badge.svg)](https://codecov.io/gh/zentlix/swagger-php)
[![Total Downloads](https://poser.pugx.org/zentlix/swagger-php/downloads)](https://packagist.org/packages/zentlix/swagger-php)
[![type-coverage](https://shepherd.dev/github/zentlix/swagger-php/coverage.svg)](https://shepherd.dev/github/zentlix/swagger-php)
[![psalm-level](https://shepherd.dev/github/zentlix/swagger-php/level.svg)](https://shepherd.dev/github/zentlix/swagger-php)

[**zircote/swagger-php**](https://github.com/zircote/swagger-php) integration package for Spiral Framework.

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+
- Spiral framework 3.5+

## Installation

You can install the package via composer:

```bash
composer require zentlix/swagger-php
```

To enable the package in your Spiral Framework application, you will need to add
the `Spiral\OpenApi\Bootloader\SwaggerBootloader` class to the list of bootloaders in your application:

```php
protected const LOAD = [
    // ...
    \Spiral\OpenApi\Bootloader\SwaggerBootloader::class,
];
```

> **Note**
> If you are using [`spiral-packages/discoverer`](https://github.com/spiral-packages/discoverer),
> you don't need to register bootloader by yourself.

## Configuration

The configuration file should be located at `app/config/swagger.php`, and it allows you to set options.
Here is an example of how the configuration file might look:

```php
use Spiral\OpenApi\Config\SwaggerConfig;
use Spiral\OpenApi\Generator\Parser\ConfigurationParser;
use Spiral\OpenApi\Generator\Parser\OpenApiParser;
use Spiral\OpenApi\Renderer\HtmlRenderer;
use Spiral\OpenApi\Renderer\JsonRenderer;
use Spiral\OpenApi\Renderer\YamlRenderer;

return [
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
        directory('app'),
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
    'use_cache' => env('APP_ENV') === 'prod',
];
```

## Usage

First, create an entity that represents the resource you want to document. For example, you can create
a **User** entity that represents a user resource:

```php
namespace App\Entity;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    description: 'User record',
    required: ['email', 'first_name', 'last_name'],
    type: 'object',
)]
class User
{
    #[OA\Property(type: 'string')]
    public string $email;

    #[OA\Property(property: 'first_name', type: 'string')]
    public string $firstName;

    #[OA\Property(property: 'last_name',type: 'string')]
    public string $lastName;
}
```

Next, create a Controller that handles the actions for the resource, and add Swagger attributes to the actions
to describe the behavior of the endpoint. For example, you can create a **UserController** that handles the **list**
action for the User resource:

```php
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;

class UserController
{
    #[OA\Get(
        path: '/api/v1/users',
        tags: ['User'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'sort',
                description: 'The field used to order users',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                examples: [
                    'user.first_name' => new OA\Examples(
                        example: 'user.first_name',
                        summary: 'Sort by `user.first_name` field',
                        value: 'user.first_name'
                    ),
                    'user.last_name' => new OA\Examples(
                        example: 'user.last_name',
                        summary: 'Sort by `user.last_name` field',
                        value: 'user.last_name'
                    ),
                ]
            ),
            new OA\Parameter(ref: '#/components/parameters/direction')
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retrieve a collection of users',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'meta',
                            ref: '#/components/schemas/ResponseCollectionMeta'
                        ),
                        new OA\Property(
                            property: 'data',
                            description: 'Collection of users',
                            type: 'array',
                            items: new OA\Items(type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: 'uuid',
                                        type: 'string',
                                        example: '7be33fd4-ff46-11ea-adc1-0242ac120002'
                                    ),
                                    new OA\Property(
                                        property: 'type',
                                        type: 'string'
                                    ),
                                    new OA\Property(
                                        property: 'attributes',
                                        ref: '#/components/schemas/User'
                                    ),
                                ],
                                type: 'object'
                            ))
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function list(): ResponseInterface
    {
        // ...
    }
}
```

Some elements like **page**, **limit**, **direction** parameters. The **ResponseCollectionMeta** schema can be used
in a variety of places. Therefore, they can be defined in the configuration file:

```php
// file app/config/swagger.php
return [
    'documentation' => [
        'info' => [
            'title' => 'My App',
            'description' => 'My App API Documentation',
            'version' => '1.0.0'
        ],
        'components' => [
            'parameters' => [
                'page' => [
                    'name' => 'page',
                    'in' => 'query',
                    'example' => 1,
                    'schema' => [
                        'type' => 'integer'
                    ]
                ],
                'limit' => [
                    'name' => 'limit',
                    'in' => 'query',
                    'example' => 25,
                    'schema' => [
                        'type' => 'integer'
                    ]
                ],
                'direction' => [
                    'name' => 'direction',
                    'in' => 'query',
                    'schema' => [
                        'type' => 'string'
                    ],
                    'examples' => [
                        'asc' => [
                            'value' => 'asc',
                            'summary' => 'Sort Ascending'
                        ],
                        'desc' => [
                            'value' => 'desc',
                            'summary' => 'Sort Descending'
                        ]
                    ]
                ]
            ],
            'schemas' => [
                'ResponseCollectionMeta' => [
                    'type' => 'object',
                    'properties' => [
                        'size' => [
                            'type' => 'integer'
                        ],
                        'page' => [
                            'type' => 'integer'
                        ],
                        'total' => [
                            'type' => 'integer'
                        ]
                    ]
                ]
            ]
        ]
    ],
];
```

The package provides a `Spiral\OpenApi\Controller\DocumentationController` controller that can render
the documentation in various formats such as HTML, JSON, and YAML. The HTML format uses the `Swagger UI` for displaying
the documentation. To use this controller, it is necessary to add a route
in the `App\Application\Bootloader\RoutesBootloader` file:

```php
use Spiral\OpenApi\Controller\DocumentationController;

final class RoutesBootloader extends BaseRoutesBootloader
{
    protected function defineRoutes(RoutingConfigurator $routes): void
    {
        // ...

        $routes
            ->add('swagger-api-html', '/api/docs')
            ->action(DocumentationController::class, 'html');
        $routes
            ->add('swagger-api-json', '/api/docs.json')
            ->action(DocumentationController::class, 'json');
        $routes
            ->add('swagger-api-yaml', '/api/docs.yaml')
            ->action(DocumentationController::class, 'yaml');

       // ...
    }
}
```
