<?php

declare(strict_types=1);

namespace App\Decorator;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TrackslistDecorator implements NormalizerInterface
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

    $docs['components']['schemas']['Result'] = [
      'type' => 'object',
      'properties' => [
        'nb total de musiques envoyées' => [
          'type' => 'integer',
          'readOnly' => true,
        ],
        'nb de musiques enregistrées' => [
          'type' => 'integer',
          'readOnly' => true,
        ],
        'nb de musiques dans la playlist user' => [
          'type' => 'integer',
          'readOnly' => true,
        ],
        'errors' => [
          'type' => 'object',
          'readOnly' => true,
        ],
      ],
    ];


    $docs['components']['schemas']['User'] = [
      'type' => 'string',
    ];


    $docs['components']['schemas']['TracksList'] = [
      'type' => 'object',
      'properties' => [
        'user' => [
          'schema' => [
            '$ref' => '#/components/schemas/User',
          ],
          'example' => '4a771835-86cd-42a4-b9f2-84566e3743da'
        ],
        'tracks' => [
          'type' => 'array',
          'items' => [
            'type' => 'object',
            'properties' => [
              'name' => [
                'type' => 'string',
              ],
              'artist' => [
                'type' => 'string',
              ],
              'album' => [
                'type' => 'string',
              ],
              'pictureUrl' => [
                'type' => 'string',
              ],
              'popularity' => [
                'type' => 'string',
                'example' => '50'
              ],
              'spotifyId' => [
                'type' => 'string',
              ],
              'deezerId' => [
                'type' => 'string',
                'example' => null
              ],
              'spotifyPreviewUrl' => [
                'type' => 'string',
              ],
              'deezerPreviewUrl' => [
                'type' => 'string',
                'example' => null
              ],
              'isTopTrack' => [
                'type' => 'boolean',
              ],
            ]
          ]
        ], 
        
        
      ]

    ];

    $tokenDocumentation = [
      'paths' => [
        '/trackslist/initialize' => [
          'post' => [
            'tags' => ['Ajout de tracks en masse'],
            'operationId' => 'app_track_create',
            'summary' => 'Création de la playlist d\'un utilisateur (array).',
            'requestBody' => [
              'description' => 'Création de la playlist d\'un utilisateur',
              'content' => [
                'application/json' => [
                  'schema' => [
                    '$ref' => '#/components/schemas/TracksList',
                  ],
                ],
              ],
            ],
            'responses' => [
              Response::HTTP_OK => [
                'description' => 'Get the saves results',
                'content' => [
                  'application/json' => [
                    'schema' => [
                      '$ref' => '#/components/schemas/Result',
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
