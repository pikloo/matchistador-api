<?php

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TrackRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @see http://schema.org/Track Documentation on Schema.org
 */

#[ORM\Entity(repositoryClass: TrackRepository::class)]
#[UniqueEntity(fields:["name", "artist"], message:"Ce titre est déja enregistré")]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    iri: 'http://schema.org/Track',
    itemOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => 'Affiche un titre',
                'description' => "Affiche un titre.",
            ],
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Supprime un titre (ADMIN)",
                'description' => "Supprime un titre (ADMIN).",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche tous les titres et les IRI des utilisateurs liés (filtre nom du titre et artiste)",
                'description' => "Affiche tous les titres et les IRI des utilisateurs liés (filtre nom du titre et artiste).",
            ],
            'normalization_context' => ['groups' => ['track:all']]
        ],
        'post' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Création d'un titre",
                'description' => "Création d'un titre.",
            ],
        ],
        'api_match_tracks' => [
            // 'path' => '/matchs/{id}/tracks',
            // 'hydra_context' => [
            //     "expects" => "hydra:template"
            // ],
            // 'force_eager' => true,
            'security' => "is_granted('ROLE_USER')",
            'method' => 'GET',
            'normalization_context' => ['groups' => ['match_tracks:read']],
        ],
    ],
    normalizationContext: ['groups' => ['track:read']],
    denormalizationContext: ['groups' => ['track:write']],
)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'artist' => 'partial'])]
class Track
{

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['track:read', 'track:all','user_has_track:read' ,'match_tracks:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(groups: ['track:read', 'track:write', 'track:all', 'user_has_track:read', 'user_profile:read', 'matchTracks:read', 'user_matchs:read','match_tracks:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(groups: ['track:read', 'track:write', 'track:all', 'user_has_track:read', 'user_profile:read', 'matchTracks:read', 'user_matchs:read','match_tracks:read'])]
    private ?string $artist = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(groups: ['track:read', 'track:write', 'track:all', 'user_has_track:read', 'user_profile:read', 'matchTracks:read', 'user_matchs:read','match_tracks:read'])]
    private ?string $album = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write', 'user_has_track:read', 'user_profile:read', 'matchTracks:read', 'user_matchs:read','match_tracks:read'])]
    private ?string $pictureUrl = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write'])]
    private ?int $popularity = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write'])]
    private ?string $spotifyId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write'])]
    private ?string $deezerId = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write','user_has_track:read','match_tracks:read'])]
    private ?string $spotifyPreviewUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(groups: ['track:read', 'track:write', 'user_has_track:read','match_tracks:read'])]
    private ?string $deezerPreviewUrl = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var UserHasTrack[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserHasTrack::class, mappedBy: 'track', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['track:all'])]
    private array|Collection|ArrayCollection $userHasTracks;

    /**
     * @var UserProfile[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserProfile::class, mappedBy: 'track', orphanRemoval: true, cascade: ['persist'])]
    private array|Collection|ArrayCollection $userProfiles;

    public function __construct()
    {
        $this->userHasTracks = new ArrayCollection();
        $this->userProfiles = new ArrayCollection();
    }

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getArtist(): ?string
    {
        return $this->artist;
    }

    public function setArtist(string $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getAlbum(): ?string
    {
        return $this->album;
    }

    public function setAlbum(string $album): self
    {
        $this->album = $album;

        return $this;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function setPictureUrl(?string $pictureUrl): self
    {
        $this->pictureUrl = $pictureUrl;

        return $this;
    }

    public function getPopularity(): ?int
    {
        return $this->popularity;
    }

    public function setPopularity(?int $popularity): self
    {
        $this->popularity = $popularity;

        return $this;
    }

    public function getSpotifyId(): ?string
    {
        return $this->spotifyId;
    }

    public function setSpotifyId(?string $spotifyId): self
    {
        $this->spotifyId = $spotifyId;

        return $this;
    }

    public function getDeezerId(): ?string
    {
        return $this->deezerId;
    }

    public function setDeezerId(?string $deezerId): self
    {
        $this->deezerId = $deezerId;

        return $this;
    }

    public function getSpotifyPreviewUrl(): ?string
    {
        return $this->spotifyPreviewUrl;
    }

    public function setSpotifyPreviewUrl(?string $spotifyPreviewUrl): self
    {
        $this->spotifyPreviewUrl = $spotifyPreviewUrl;

        return $this;
    }

    public function getDeezerPreviewUrl(): ?string
    {
        return $this->deezerPreviewUrl;
    }

    public function setDeezerPreviewUrl(?string $deezerPreviewUrl): self
    {
        $this->deezerPreviewUrl = $deezerPreviewUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[Groups(groups: ['track:read'])]
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->getCreatedAt())->diffForHumans();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue() : void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[Groups(groups: ['track:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        return $this->getUpdatedAt() !== null ? Carbon::instance($this->getUpdatedAt())->diffForHumans() : null;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue() : void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection|UserHasTrack[]
     */
    public function getUserHasTracks(): Collection
    {
        return $this->userHasTracks;
    }

    public function addUserHasTrack(UserHasTrack $userHasTrack): self
    {
        if (!$this->userHasTracks->contains($userHasTrack)) {
            $this->userHasTracks[] = $userHasTrack;
            $userHasTrack->setTrack($this);
        }

        return $this;
    }

    public function removeUserHasTrack(UserHasTrack $userHasTrack): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userHasTracks->removeElement($userHasTrack) && $userHasTrack->getTrack() === $this) {
            $userHasTrack->setTrack(null);
        }

        return $this;
    }

    /**
     * @return Collection|UserProfile[]
     */
    public function getUserProfiles(): Collection
    {
        return $this->userProfiles;
    }

    public function addUserProfile(UserProfile $userProfiles): self
    {
        if (!$this->userProfiles->contains($userProfiles)) {
            $this->userProfiles[] = $userProfiles;
            $userProfiles->setTrack($this);
        }

        return $this;
    }

    public function removeUserProfile(UserProfile $userProfiles): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userProfiles->removeElement($userProfiles) && $userProfiles->getTrack() === $this) {
            $userProfiles->setTrack(null);
        }

        return $this;
    }
}
