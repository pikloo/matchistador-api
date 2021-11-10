<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\MatchUpFlags;
use Ramsey\Uuid\UuidInterface;
use App\Entity\UserMatchUpFlags;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserHasMatchupRepository;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @see http://schema.org/UserMatch Documentation on Schema.org
 */
#[ApiResource(
    attributes: [
        'force_eager' => true,
    ],
    iri: 'http://schema.org/UserMatch',
    shortName: 'UserMatch',
    itemOperations: [
        'get' => [
            'path' => '/user_matchs/{id}',
            'openapi_context' => [
                'summary'     => 'Affiche un match lié à un utilisateur',
                'description' => "Affiche un match lié à un utilisateur.",
            ],
        ],
        'patch' => [
            'path' => '/user_matchs/{id}',
            'denormalization_context' => ['groups' => ['user_matchs:update']],
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'openapi_context' => [
                'summary'     => "Modifie un match lié à un utilisateur",
                'description' => "Modifie un match lié à un utilisateur.",
            ],
        ],
        'delete' => [
            'path' => '/user_matchs/{id}',
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('delete', object)",
            'openapi_context' => [
                'summary'     => "Supprime un match lié à un utilisateur",
                'description' => "Supprime un match lié à un utilisateur.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'path' => '/user_matchs/',
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche les matchs liés aux utilisateurs",
                'description' => "Affiche les matchs liés aux utilisateurs.",
            ],
        ],
        'post' => [
            'path' => '/user_matchs/',
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Ajout d'un match lié à un utilisateur",
                'description' => "Ajout d'un match lié à un utilisateur.",
            ],
        ],
        'api_user_match' => [
            'path' => '/user/{id}/matchs',
            // 'hydra_context' => [
            //     "expects" => "hydra:template"
            // ],
            'force_eager' => true,
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
            'method' => 'GET',
            'normalization_context' => ['groups' => ['user_matchs:read']],
            'openapi_context' => [
                'summary'     => "Affiche les matchs d'un utilisateur",
                'description' => "Affiche les matchs d'un utilisateur.",
                'parameters' => [
                    'id' => [
                        'in' => 'path',
                        'name' => 'id',
                        'required' => true,
                        'description' => 'User identifier'
                    ],
                    'page' => [
                        'in' => 'query',
                        'name' => 'page',
                        'schema' => [
                            'type' => 'integer',
                            'default' => 1
                        ],
                        'description' => 'The collection page number'
                    ],
                    'limit' => [
                        'in' => 'query',
                        'name' => 'limit',
                        'schema' => [
                            'type' => 'integer',
                            'default' => 30
                        ],
                        'description' => 'The number of items per page'
                    ],
                    'orderByScore' => [
                        'in' => 'query',
                        'name' => 'orderByScore',
                        'schema' => [
                            'type' => 'string',
                            'default' => 'desc'
                        ],
                    ],
                    'orderByUpdatedAt' => [
                        'in' => 'query',
                        'name' => 'orderByUpdatedAt',
                        'schema' => [
                            'type' => 'string',
                            'default' => 'desc'
                        ],
                    ]
                ],
                'responses' => [
                    '200' => [
                        'description' => 'User resource',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => [
                                            'schema' => [
                                                'type' => 'string',
                                            ],
                                            'example' => '4a771835-86cd-42a4-b9f2-84566e3743da'
                                        ],
                                        'user' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => [

                                                    'type' => 'string',
                                                    'example' => '4a771835-86cd-42a4-b9f2-84566e3743da'
                                                ],
                                                'userData' => [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'name' => [
                                                            'type' => 'string',
                                                        ],
                                                        'age' => [
                                                            'type' => 'integer',
                                                        ],
                                                        'city' => [
                                                            'type' => 'string',
                                                        ],
                                                        'pictureUrl' => [
                                                            'type' => 'string',
                                                        ],
                                                    ],
                                                ],
                                                //TODO: USerProfile
                                                // 'userProfiles' => [
                                                //     'type' => 'object',
                                                //     'properties' => [
                                                //         'track' => [
                                                //             'type' => 'string',
                                                //         ],
                                                //         'themes' => [
                                                //             'type' => 'integer',
                                                //         ],
                                                //     ],
                                                // ],
                                            ],
                                        ],
                                        'match' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'id' => [
                                                    'type' => 'string',
                                                    'example' => '4a771835-86cd-42a4-b9f2-84566e3743da'
                                                ],
                                                'score' => [
                                                    'type' => 'integer',
                                                    'example' => '50'
                                                ],
                                                'distance' => [
                                                    'type' => 'integer',
                                                    'example' => '15'
                                                ],
                                                'commonsTracksCount' => [
                                                    'type' => 'integer',
                                                    'example' => '4'
                                                ],
                                            ],
                                        ],
                                        'isFavorite' => [
                                            'schema' => [
                                                'type' => 'boolean',
                                            ],
                                            'example' => 'true'
                                        ],
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    normalizationContext: ['groups' => ['user_matchs:read']],
    denormalizationContext: ['groups' => ['user_matchs:write']],
)]
#[ORM\Entity(repositoryClass: UserHasMatchupRepository::class)]
class UserHasMatchup
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['user_matchs:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userMatchs')]
    #[Groups(groups: ['user_matchs:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: MatchUp::class, inversedBy: 'usersInMatch', fetch: 'EAGER')]
    #[Groups(groups: ['user_matchs:read'])]
    #[ApiProperty(attributes:['fetchEager' => false])]
    private ?MatchUp $match = null;

    /**
     * @var mixed|bool|null
     */
    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(groups: ['user_matchs:read', 'user_matchs:update'])]
    #[SerializedName('isFavorite')]
    private $isFavorite = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): UuidInterface
    {
        return $this->id;
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

    public function getMatch(): ?MatchUp
    {
        return $this->match;
    }
    public function setMatch(?MatchUp $match): self
    {
        $this->match = $match;

        return $this;
    }

    public function getIsFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(?bool $isFavorite): self
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
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
    public function getUpdatedAtAgo(): ?string
    {
        return $this->getUpdatedAt() !== null ? Carbon::instance($this->getUpdatedAt())->diffForHumans() : null;
    }
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

}
