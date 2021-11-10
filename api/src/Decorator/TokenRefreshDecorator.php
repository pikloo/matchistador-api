<?php

declare(strict_types=1);

namespace App\Decorator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TokenRefreshDecorator implements NormalizerInterface
{
  public function __construct(private NormalizerInterface $decorated)
  {
  }

  public function supportsNormalization($data, string $format = null): bool
  {
    return $this->decorated->supportsNormalization($data, $format);
  }

  /**
   * @return mixed[]
   */
  public function normalize($object, string $format = null, array $context = []): array
  {
    $docs = $this->decorated->normalize($object, $format, $context);

    $docs['components']['schemas']['Token'] = [
      'type' => 'object',
      'properties' => [
        'token' => [
          'type' => 'string',
          'readOnly' => true,
        ],
        'refresh_token' => [
          'type' => 'string',
          'readOnly' => true,
        ],
      ],
    ];

    $docs['components']['schemas']['tokenRefresh'] = [
      'type' => 'object',
      'properties' => [
        'refresh_token' => [
          'type' => 'string',
        ],
      ],
    ];

    $tokenDocumentation = [
      'paths' => [
        '/login/refresh' => [
          'post' => [
            'tags' => ['Connexion'],
            'operationId' => 'tokenRefresh',
            'summary' => "Récupération d'un nouveau token après l'expiration du précédent",
            'requestBody' => [
              'description' => 'Create a new JWT Token',
              'content' => [
                'application/json' => [
                  'schema' => [
                    '$ref' => '#/components/schemas/tokenRefresh',
                  ],
                ],
              ],
            ],
            'responses' => [
              Response::HTTP_OK => [
                'description' => 'Get a new token',
                'content' => [
                  'application/json' => [
                    'schema' => [
                      '$ref' => '#/components/schemas/Token',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ];

    return array_merge_recursive($docs, $tokenDocumentation);
  }
}
