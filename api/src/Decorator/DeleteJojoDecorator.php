<?php

declare(strict_types=1);

namespace App\Decorator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DeleteJojoDecorator implements NormalizerInterface
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

    $docs['components']['schemas']['Delete'] = [
      'type' => 'object',
      'properties' => [
        'message' => [
          'type' => 'string',
          'readOnly' => true,
        ],
      ],
    ];

    $tokenDocumentation = [
      'paths' => [
        '/delete_jojo' => [
          'get' => [
            'tags' => ['User'],
            'operationId' => 'deleteJojo',
            'summary' => 'Supprimer Jojo',
            'responses' => [
              Response::HTTP_OK => [
                'description' => 'Confirmation de suppression',
                'content' => [
                  'application/json' => [
                    'schema' => [
                      '$ref' => '#/components/schemas/Delete',
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