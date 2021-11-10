<?php

declare(strict_types=1);

namespace App\Decorator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class JwtDecorator implements NormalizerInterface
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
      ],
    ];

    $docs['components']['schemas']['Credentials'] = [
      'type' => 'object',
      'properties' => [
        'email' => [
          'type' => 'string',
        ],
        'password' => [
          'type' => 'string',
        ],
      ],
    ];

    $tokenDocumentation = [
      'paths' => [
        '/login' => [
          'post' => [
            'tags' => ['Connexion'],
            'operationId' => 'postCredentialsItem',
            'summary' => 'Récupération d\'un token JWT pour se connecter.',
            'requestBody' => [
              'description' => 'Create new JWT Token',
              'content' => [
                'application/json' => [
                  'schema' => [
                    '$ref' => '#/components/schemas/Credentials',
                  ],
                ],
              ],
            ],
            'responses' => [
              Response::HTTP_OK => [
                'description' => 'Get JWT token',
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
