<?php

namespace App\Entity;

use App\Entity\MatchUpFlags;
use App\Entity\UserHasMatchup;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MatchUpRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @see http://schema.org/Match Documentation on Schema.org
 */
#[ApiResource(
    attributes: [
        'pagination_client_items_per_page' => true,
        'force_eager' => false,
    ],
    // subresourceOperations: [
    //     'match_tracks_get_subresource' => [
    //         // 'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
    //         'method' => 'GET',
    //         'path' => '/matchs/{id}/tracks',
    //         'openapi_context' => [
    //             'summary'     => "Affiche les titres en commun d'un match d'utilisateurs",
    //             'description' => "Affiche les titres en commun d'un match d'utilisateurs",
    //         ],
    //     ],
    // ],
    iri: 'http://schema.org/Match',
    shortName: 'Match',
    itemOperations: [
        'get' => [
            'path' => '/matchs/{id}',
            'openapi_context' => [
                'summary'     => "Affiche un match",
                'description' => "Affiche un match.",
            ],
        ],
        'patch' => [
            'path' => '/matchs/{id}',
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'denormalization_context' => ['groups' => ['match:update']],
            'openapi_context' => [
                'summary'     => "Modifie un match",
                'description' => "Modifie un match.",
            ],
        ],
        'delete' => [
            'path' => '/matchs/{id}',
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Supprime un match",
                'description' => "Supprime un match.",
            ],
        ]
    ],
    collectionOperations: [
        'post' => [
            'path' => '/matchs',
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('create', object)",
            'validation_groups' => ['Default', 'registration'],
            'denormalization_context' => ['groups' => ['match:create']],
            'openapi_context' => [
                'summary'     => "Création d'un match",
                'description' => "Création d'un match.",
            ],
        ],
        'get' => [
            'path' => '/matchs',
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche tous les matchs",
                'description' => "Affiche tous les matchs.",
            ],
        ],
    ],
    normalizationContext: ['groups' => ['match:read']],
    denormalizationContext: ['groups' => ['match:write']],
)]
#[ORM\Entity(repositoryClass: MatchUpRepository::class)]
class MatchUp
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['match:read', 'user_matchs:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    /**
     * @var int|null|string
     */
    #[ORM\Column(type: 'bigint', nullable: true)]
    #[Groups(groups: ['match:read', 'user_matchs:read'])]
    private $score;

    /**
     * @var UserHasMatchup[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserHasMatchup::class, mappedBy: 'match', orphanRemoval: true, cascade: ['persist', 'remove'])]
    // #[ApiProperty(attributes:['fetchEager' => true])]
    private array|Collection|ArrayCollection $usersInMatch;


    /**
     * @var int|null|string
     */
    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(groups: ['match:read', 'user_matchs:read'])]
    private $distance = null;

    /**
     * @var mixed|\DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $createdAt;

    /**
     * @var mixed|\DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private bool $isActive = false;

    #[ORM\OneToOne(targetEntity: MatchUpFlags::class, mappedBy: 'match', cascade: ['persist', 'remove'])]
    #[ApiProperty(attributes: ['fetchEager' => false])]
    private ?MatchUpFlags $matchFlags = null;

    #[ORM\OneToOne(mappedBy: 'match', targetEntity: Talk::class, orphanRemoval: true, cascade: ['persist', 'remove'])]
    #[Groups(groups: ['match:read', 'user_matchs:read'])]
    private $talk;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->usersInMatch = new ArrayCollection();
    }

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int|string|null $score): self
    {
        $this->score = $score;

        return $this;
    }

    #[Groups(groups: ['match:read', 'user_matchs:read'])]
    #[SerializedName('commonsTracksCount')]
    public function getTrackNumber(): ?int
    {
        $userTrackA = $this->usersInMatch[0]->getUser()->getUserTracks()->toArray();
        $userTrackB = $this->usersInMatch[1]->getUser()->getUserTracks()->toArray();

        $commonTracks = array_uintersect($userTrackA, $userTrackB, function ($a, $b) {
                return strcmp(spl_object_hash($a->getTrack()->getId()), spl_object_hash($b->getTrack()->getId()));
            });
        return count($commonTracks);
    }


    public function getDistance()
    {
        return ($this->distance < 1000) ? 1 : round($this->distance / 1000);
    }

    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
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

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }


    /**
     * @return Collection|UserHasMatchup[]
     */
    // #[Groups(groups: ['user_has_match:read'])]
    public function getUsersInMatch(): Collection
    {
        return $this->usersInMatch;
    }

    public function addUserInMatch(UserHasMatchup $userMatch): self
    {
        if (!$this->usersInMatch->contains($userMatch)) {
            $this->usersInMatch[] = $userMatch;
            $userMatch->setMatch($this);
        }

        return $this;
    }

    public function removeUserInMatch(UserHasMatchup $userMatch): self
    {
        // set the owning side to null (unless already changed)
        if ($this->usersInMatch->removeElement($userMatch) && $userMatch->getMatch() === $this) {
            $userMatch->setMatch(null);
        }

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

    public function getMatchFlags(): ?MatchUpFlags
    {
        return $this->matchFlags;
    }
    public function setMatchFlags(?MatchUpFlags $matchFlags): self
    {
        $this->matchFlags = $matchFlags;

        return $this;
    }

    // public function getTalk(): ?Talk
    // {
    //     return $this->talk;
    // }

    // public function setTalk(Talk $talk): self
    // {
    //     // set the owning side of the relation if necessary
    //     if ($talk->getMatch() !== $this) {
    //         $talk->setMatch($this);
    //     }

    //     $this->talk = $talk;

    //     return $this;
    // }
}
