<?php

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Filter\FullTextSearchFilter;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\UserProfileRepository;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @see http://schema.org/UserProfile Documentation on Schema.org
 */
#[ApiResource(
    subresourceOperations: [
        'api_users_user_profiles_get_subresource' => [
            'security' => "is_granted('ROLE_USER')",
        ],
    ],
    iri: 'http://schema.org/UserProfile',
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => "Affiche un thème et son titre choisi par un utilisateur",
                'description' => "Affiche un thème et son titre choisi par un utilisateur.",
            ],
        ],
        'patch' => [
            'denormalization_context' => ['groups' => ['user_profile:update']],
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'openapi_context' => [
                'summary'     => "Modifie un thème et son titre choisi par un utilisateur",
                'description' => "Modifie un thème et son titre choisi par un utilisateur",
                'requestBody' => [
                    'content' => [
                        'application/merge-patch+json' => [
                            'schema'  => [
                                // '$ref'       => '#/components/schemas/UserProfile-user_profile.update',
                                'properties' =>
                                [
                                    'theme' => [
                                        'type' => 'string',
                                        'example' => '/profile_themes/28c17d2f-c908-4599-9f45-3d0afa8f3440'
                                    ],
                                    'track' => [
                                        'type' => 'string',
                                        'example' => '/tracks/2a32da7b-7296-408a-89bf-e80ccc130f48'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('delete', object)",
            'openapi_context' => [
                'summary'     => "Supprime un thème et son titre choisi par un utilisateur",
                'description' => "Supprime un thème et son titre choisi par un utilisateur.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche les thèmes et leur titres choisis des utilisateurs",
                'description' => "Affiche les thèmes et leur titres choisis des utilisateurs.",
            ],
        ],
        'post' => [
            'denormalization_context' => ['groups' => ['user_profile:create']],
            'security' => "is_granted('ROLE_USER')",
            'openapi_context' => [
                'summary'     => "Création d'un thème lié d'un titre et à un utilisateur",
                'description' => "Création d'un thème lié d'un titre et à un utilisateur.",
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema'  => [
                                'properties' =>
                                [
                                    'user' => [
                                        'user' => 'string',
                                        'example' => '/users/93f7647c-ea8a-4360-b6e5-4683e05f7a0e'
                                    ],
                                    'theme' => [
                                        'type' => 'string',
                                        'example' => '/profile_themes/28c17d2f-c908-4599-9f45-3d0afa8f3440'
                                    ],
                                    'track' => [
                                        'type' => 'string',
                                        'example' => '/tracks/2a32da7b-7296-408a-89bf-e80ccc130f48'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'example' => [
                    'user'        => '/users/93f7647c-ea8a-4360-b6e5-4683e05f7a0e',
                    'theme' => '/profile_themes/28c17d2f-c908-4599-9f45-3d0afa8f3440',
                    'track' => '/tracks/2a32da7b-7296-408a-89bf-e80ccc130f48',
                ],
            ],
        ],
    ],
    normalizationContext: ['groups' => ['user_profile:read']],
    denormalizationContext: ['groups' => ['user_profile:write']],
)]
#[ORM\Entity(repositoryClass: UserProfileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["user", "theme"], message: "Ce thème a déja été renseigné pour cet utilisateur")]
#[UniqueEntity(fields: ["user", "theme", "track"], message: "Ce titre pour ce thème a déja été renseigné pour cet utilisateur")]
#[ApiFilter(FullTextSearchFilter::class, properties: [
    'search_theme' => [
        'theme.id' => 'exact',
    ]
])]
class UserProfile
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['user_profile:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Track::class, inversedBy: 'userProfiles', cascade: ['persist'])]
    #[Groups(groups: ['user_profile:read', 'user_profile:update', 'user_profile:create', 'user_matchs:read'])]
    #[Assert\NotBlank(message: "Le titre n'est pas renseigné")]
    private ?Track $track = null;

    #[ORM\ManyToOne(targetEntity: ProfileTheme::class, inversedBy: 'userProfiles', cascade: ['persist'])]
    #[Groups(groups: ['user_profile:read', 'user_profile:update', 'user_profile:create', 'user_matchs:read'])]
    #[Assert\NotBlank(message: "Le thème n'est pas renseigné")]
    private ?ProfileTheme $theme = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userProfiles', cascade: ['persist'])]
    #[Groups(groups: ['user_profile:read', 'user_profile:create'])]
    private ?User $user = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): self
    {
        $this->track = $track;

        return $this;
    }

    public function getTheme(): ?ProfileTheme
    {
        return $this->theme;
    }

    public function setTheme(?ProfileTheme $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
