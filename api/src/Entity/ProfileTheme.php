<?php

namespace App\Entity;

use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\ProfileThemeRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @see http://schema.org/ProfileTheme Documentation on Schema.org
 */
#[ApiResource(
    iri: 'http://schema.org/ProfileTheme',
    shortName: 'ProfileTheme',
    itemOperations: [
        'get' => [
            'security' => "is_granted('ROLE_USER')",
            'openapi_context' => [
                'summary'     => 'Affiche un thème',
                'description' => "Affiche un thème.",
            ],
        ],
        'patch' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Modifie un thème",
                'description' => "Modifie un thème.",
            ],
        ],
        'delete' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Supprime un thème",
                'description' => "Supprime un thème.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'security' => "is_granted('ROLE_USER')",
            'openapi_context' => [
                'summary'     => "Affiche tous les thèmes",
                'description' => "Affiche tous les thèmes.",
            ],
        ],
        'post' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Création d'un thème",
                'description' => "Création un thème.",
            ],
        ],
    ],
    normalizationContext: ['groups' => ['profile_theme:read']],
    denormalizationContext: ['groups' => ['profile_theme:write']],
)]
#[ORM\Entity(repositoryClass: ProfileThemeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProfileTheme
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['profile_theme:read', 'user_profile:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(groups: ['profile_theme:read', 'profile_theme:write', 'user_profile:read', 'user_matchs:read'])]
    private $name;

    /**
     * @var mixed|\DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(groups: ['profile_theme:read'])]
    private $createdAt;

    /**
     * @var mixed|\DateTimeImmutable|null
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    /**
     * @var UserProfile[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserProfile::class, mappedBy: 'theme', orphanRemoval: true, cascade: ['persist'])]
    private array|Collection|ArrayCollection $userProfiles;

    public function __construct()
    {
        $this->userProfile = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

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

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection|UserProfile[]
     */
    public function getUserProfiles(): Collection
    {
        return $this->userProfiles;
    }

    public function addUserProfile(UserProfile $userProfile): self
    {
        if (!$this->userProfiles->contains($userProfile)) {
            $this->userProfiles[] = $userProfile;
            $userProfile->setTheme($this);
        }

        return $this;
    }

    public function removeUserProfile(UserProfile $userProfile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userProfiles->removeElement($userProfile) && $userProfile->getTheme() === $this) {
            $userProfile->setTheme(null);
        }

        return $this;
    }

}
