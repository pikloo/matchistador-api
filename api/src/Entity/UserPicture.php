<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Controller\CreateUserPictureAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity]
#[ApiResource(
  iri: 'http://schema.org/UserPicture',
  normalizationContext: ['groups' => ['user_picture:read']],
  itemOperations: [
    'get' => [
      'openapi_context' => [
        'summary'     => "Affiche une image uploadée",
        'description' => "Affiche une image uploadée.",
      ],
    ],
  ],
  collectionOperations: [
    'get' => [
      'openapi_context' => [
        'summary'     => "Affiche toutes les images uploadées",
        'description' => "Affiche toutes les images uploadées.",
      ],
    ],
    'post' => [
      'controller' => CreateUserPictureAction::class,
      'deserialize' => false,
      'validation_groups' => ['Default', 'user_picture:create'],
      'openapi_context' => [
        'summary'     => "Upload d'une image",
        'description' => "Upload d'une image.",
        'requestBody' => [
          'content' => [
            'multipart/form-data' => [
              'schema' => [
                'type' => 'object',
                'properties' => [
                  'file' => [
                    'type' => 'string',
                    'format' => 'binary',
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ],
  ]
)]
#[ORM\HasLifecycleCallbacks]
class UserPicture
{
  #[ORM\Id]
  #[ORM\Column(type: 'uuid', unique: true)]
  #[ORM\GeneratedValue(strategy: 'CUSTOM')]
  #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
  #[Groups(['user_picture:read'])]
  private \Ramsey\Uuid\UuidInterface $id;

  #[ApiProperty(iri: 'http://schema.org/contentUrl')]
  #[Groups(groups: ['user:read', 'user_data:update', 'user_data:create', 'user_matchs:read', 'user_data:read', 'user_picture:read','talk:read'])]
  public ?string $contentUrl = null;

  /**
   * @Vich\UploadableField(mapping="user_picture", fileNameProperty="filePath")
   */
  #[Assert\NotNull(groups: ['user_picture:create'])]
  public ?File $file = null;

  #[ORM\Column(nullable: true)]
  #[Groups(groups: ['user:read', 'user_data:update', 'user_data:create', 'user_matchs:read', 'user_data:read', 'user_picture:read','talk:read'])]
  public ?string $filePath = null;

  public function getId(): \Ramsey\Uuid\UuidInterface
  {
    return $this->id;
  }
}
