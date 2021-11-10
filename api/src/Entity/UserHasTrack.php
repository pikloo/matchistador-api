<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\UserTrackFlags;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Filter\FullTextSearchFilter;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\Repository\UserHasTrackRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @see http://schema.org/UserTrack Documentation on Schema.org
 */
#[ApiResource(
    subresourceOperations: [
        'api_users_user_tracks_get_subresource' => [
            'security' => "is_granted('ROLE_USER')",
        ],
    ],
    iri: 'http://schema.org/UserTrack',
    shortName: 'UserTrack',
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => 'Affiche un titre lié à un utilisateur',
                'description' => "Affiche un titre lié à un utilisateur.",
            ],
        ],
        'patch' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'openapi_context' => [
                'summary'     => "Modifie le statut du titre lié à un utilisateur (superTrack)",
                'description' => "Modifie le statut du titre lié à un utilisateur (superTrack).",
            ],
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('delete', object)",
            'openapi_context' => [
                'summary'     => "Supprime un titre lié à un utilisateur",
                'description' => "Supprime un titre lié à un utilisateur.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche les titres liés aux utilisateurs",
                'description' => "Affiche les titres liés aux utilisateurs.",
            ],
        ],
        'post' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Création d'un titre lié à un utilisateur",
                'description' => "Création d'un titre lié à un utilisateur.",
            ],
        ],
    ],
    normalizationContext: ['groups' => ['user_has_track:read']],
    denormalizationContext: ['groups' => ['user_has_track:write']],
)]
#[ApiFilter(FullTextSearchFilter::class, properties: [
    'search_track' => [
        'track.name' => 'ipartial',
        'track.artist' => 'istart'
    ],
])]
#[ApiFilter(BooleanFilter::class, properties: ['isActive'])]
#[ORM\Entity(repositoryClass: UserHasTrackRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["user", "track"], message: "Ce titre est déja enregistré dans la playlist de l'utilisateur")]
class UserHasTrack
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['user_has_track:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userTracks', cascade: ['persist'])]
    #[Groups(groups: ['track:all'])]
    private ?User $user = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['user_has_track:read','user_has_track:write'])]
    private bool $isSuperTrack = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Track::class, inversedBy: 'userHasTracks')]
    #[Groups(groups: ['user_has_track:read'])]
    private ?Track $track = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\OneToOne(targetEntity: UserTrackFlags::class, mappedBy: 'userTrack', cascade: ['persist', 'remove'])]
    private ?UserTrackFlags $userTrackFlags = null;

    public function getId(): UuidInterface
    {
        return $this->id;
    }
    public function getIsSuperTrack(): bool
    {
        return $this->isSuperTrack;
    }
    public function setIsSuperTrack(bool $isSuperTrack): self
    {
        $this->isSuperTrack = $isSuperTrack;

        return $this;
    }
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    #[Groups(groups: ['user_has_track:read'])]
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
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
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
    public function getIsActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getUserTrackFlags(): ?UserTrackFlags
    {
        return $this->userTrackFlags;
    }

    public function setUserTrackFlags(?UserTrackFlags $userTrackFlags): self
    {
        // unset the owning side of the relation if necessary
        if ($userTrackFlags === null && $this->userTrackFlags !== null) {
            $this->userTrackFlags->setUserTrack(null);
        }

        // set the owning side of the relation if necessary
        if ($userTrackFlags !== null && $userTrackFlags->getUserTrack() !== $this) {
            $userTrackFlags->setUserTrack($this);
        }

        $this->userTrackFlags = $userTrackFlags;

        return $this;
    }

}
