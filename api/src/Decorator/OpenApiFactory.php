<?php

declare(strict_types=1);

namespace App\Decorator;

use ArrayObject;
use ApiPlatform\Core\OpenApi\OpenApi;
use ApiPlatform\Core\OpenApi\Model\PathItem;
use ApiPlatform\Core\OpenApi\Model\Operation;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\OpenApi\Model\RequestBody;
use ApiPlatform\Core\OpenApi\Factory\OpenApiFactoryInterface;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $openApi
            ->getPaths()
            ->addPath('/activate', new PathItem(
                null,
                null,
                null,
                null,
                null,
                new Operation(
                    'post',
                    ['Code de confirmation'],
                    [
                        Response::HTTP_OK => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'id' => [
                                                'type' => 'string',
                                            ],
                                            'email' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'Vérification du code de confirmation.',
                    '',
                    null,
                    [],
                    new RequestBody(
                        '',
                        new ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                        true
                    ),
                )
            ));
        $openApi
            ->getPaths()
            ->addPath('/activate/refresh', new PathItem(
                null,
                null,
                null,
                new Operation(
                    'get',
                    ['Code de confirmation'],
                    [
                        Response::HTTP_OK => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => [
                                                'type' => 'string',
                                            ],
                                            'error' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'Regénération et renvoie par mail du code de confirmation',
                    'Regénération et renvoie par mail du code de confirmation.',
                )
            ));

        $openApi
            ->getPaths()
            ->addPath('/init', new PathItem(
                null,
                null,
                null,
                null,
                null,
                new Operation(
                    'post',
                    ['Connexion'],
                    [
                        Response::HTTP_OK => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'token' => [
                                                'type' => 'string',
                                            ],
                                            'refresh_token' => [
                                                'type' => 'string',   
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        Response::HTTP_UNPROCESSABLE_ENTITY => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'message' => [
                                                'type' => 'string',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                    "Création d'un utilisateur avec l'email puis authentification",
                    "Création d'un utilisateur avec l'email puis authentification.",
                    null,
                    [],
                    new RequestBody(
                        '',
                        new ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'email' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ]),
                        true
                    ),
                )
            ));

        return $openApi;
    }
}
