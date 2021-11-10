<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\MatchUp;
use App\Entity\TalkUser;
use App\Entity\MessageUser;
use App\Entity\UserProfile;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @see http://schema.org/User Documentation on Schema.org
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ["email"], message: "Cette adresse e-mail est déja utilisée")]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "app_user")]
#[ApiResource(
    attributes: [
        'pagination_client_items_per_page' => true,
        'pagination_use_output_walkers' => true,
        'force_eager' => true,
    ],
    subresourceOperations: [
        'user_data_get_subresource' => [
            'method' => 'GET',
            'path' => '/users/{id}/datas',
            'openapi_context' => [
                'summary'     => "Affiche les informations d'un utilisateur",
                'description' => "Affiche les informations d'un utilisateur",
            ],
        ],
        'user_tracks_get_subresource' => [
            'method' => 'GET',
            'path' => '/users/{id}/tracks',
            'openapi_context' => [
                'summary'     => "Affiche les titres d'un utilisateur",
                'description' => "Affiche les titres d'un utilisateur",
            ],
        ],
        'user_profiles_get_subresource' => [
            'method' => 'GET',
            'path' => '/users/{id}/profiles',
            'openapi_context' => [
                'summary'     => "Affiche les titres liés aux thèmes d'un utilisateur",
                'description' => "Affiche les titres liés aux thèmes d'un utilisateur",
            ],
        ],
        'talk_users_get_subresource' => [
            'method' => 'GET',
            'path' => '/users/{id}/talks',
            'openapi_context' => [
                'summary'     => "Affiche les discussions d'un utilisateur",
                'description' => "Affiche les discussions d'un utilisateur",
            ],
        ],
    ],
    iri: 'http://schema.org/User',
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => 'Affiche un utilisateur',
                'description' => "Affiche un utilisateur et ses infos (userDatas)",
            ],
        ],
        'patch' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'openapi_context' => [
                'summary'     => "Modifie les informations de connexion d'un utilisateur",
                'description' => "Modifie les informations de connexion d'un utilisateur.",
                'requestBody' => [
                    'content' => [
                        'application/merge-patch+json' => [
                            'schema'  => [
                                'type'       => 'object',
                                'properties' =>
                                [
                                    'email'        => ['type' => 'string'],
                                    'password' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'delete' => [
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('delete', object)",
            'openapi_context' => [
                'summary'     => "Supprime un utilisateur, ses infos et sa playlist",
                'description' => "Supprime un utilisateur, ses infos et sa playlist.",
            ],
        ],
        'me' => [
            'deprecation_reason' => "Pas stateless 😪",
            'security' => "is_granted('ROLE_USER')",
            'method' => 'GET',
            'path' => '/users/me',
            'read' => false,
            'openapi_context' => [
                'summary'     => "Affiche l'utilisateur connecté",
                'description' => "Affiche l'utilisateur connecté et ses infos (userDatas).",
                'parameters' => [
                    'id' => [
                        'in' => 'path',
                        'name' => 'id',
                        'required' => false,
                    ]
                ]
            ],
        ],

    ],
    collectionOperations: [
      'get' => [
            'openapi_context' => [
                'summary'     => 'Affiche tous les utilisateurs',
                'description' => "Affiche tous les utilisateurs, leur infos (userDatas) et leur playlist (userHasTracks).",
            ],
        ],
        'post' => [
            'validation_groups' => ['Default', 'registration'],
            'normalization_context' => ['groups' => ['user:registration']],
            'openapi_context' => [
                'summary'     => "Création d'un utilisateur [ADMIN]",
                'description' => "Création d'un utilisateur [ADMIN].",
            ],
        ]
    ],
    normalizationContext: ['groups' => ['user:read']],
    denormalizationContext: ['groups' => ['user:write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, JWTUserInterface
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['user:read', 'match:read', 'user_matchs:read','talk:read'])]
    #[ApiProperty(identifier: true)]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: "Ton adresse e-mail n'est pas renseigné")]
    #[Assert\Email(message: 'Saisis une adresse e-mail valide')]
    #[Groups(groups: ['user:write', 'user:registration'])]
    #[ApiProperty(iri: 'http://schema.org/name')]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     *
     */
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    /**
     * @var null|mixed
     */
    #[Assert\Regex(pattern: '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/', match: true, message: 'Ton mot de passe doit contenir au moins 1 lettre minuscule, 1 lettre majuscule, 1 chiffre et faire au moins 8 caractères')]
    #[Groups(groups: ['user:write'])]
    #[SerializedName('password')]
    private $plainPassword;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(targetEntity: UserData::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['user:read', 'match:read', 'user_matchs:read','talk:read'])]
    #[ApiSubresource]
    private ?UserData $userData = null;

    /**
     * @var UserHasTrack[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserHasTrack::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    #[ApiSubresource]
    private array|Collection|ArrayCollection $userTracks;

    /**
     * @var UserProfile[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserProfile::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(groups: ['user_matchs:read'])]
    #[ApiSubresource]
    private array|Collection|ArrayCollection $userProfiles;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $reset_token = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $spotifyId = null;

    /**
     * @var UserHasMatchup[]|Collection|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: UserHasMatchup::class, mappedBy: 'user', orphanRemoval: true, cascade: ['persist'])]
    private array|Collection|ArrayCollection $userMatchs;



    #[ORM\Column(type: 'boolean')]
    private bool $isActive = false;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: TalkUser::class)]
    #[ApiSubresource]
    private $talkUsers;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: MessageUser::class)]
    private $messageUsers;

    public function __construct()
    {
        $this->userTracks = new ArrayCollection();
        $this->userMatchs = new ArrayCollection();
        $this->userProfiles = new ArrayCollection();
        $this->talkUsers = new ArrayCollection();
        $this->messageUsers = new ArrayCollection();
    }

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     * @return mixed[]
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param mixed[] $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    #[ORM\PrePersist]
    public function setRolesValue(): void
    {
        $this->roles = ['ROLE_USER'];
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }



    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // #[Groups(groups: ['user:read'])]
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

    // #[Groups(groups: ['user:read'])]
    public function getUpdatedAtAgo(): ?string
    {
        return $this->getUpdatedAt() !== null ? Carbon::instance($this->getUpdatedAt())->diffForHumans() : null;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Get the value of plainPassword
     * @return null|mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * Set the value of plainPassword
     */
    public function setPlainPassword($plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getUserData(): ?UserData
    {
        return $this->userData;
    }

    public function setUserData(?UserData $userData): self
    {
        // unset the owning side of the relation if necessary
        if ($userData === null && $this->userData !== null) {
            $this->userData->setUser(null);
        }

        // set the owning side of the relation if necessary
        if ($userData !== null && $userData->getUser() !== $this) {
            $userData->setUser($this);
        }

        $this->userData = $userData;

        return $this;
    }

    /**
     * @return Collection|UserHasTrack[]
     */
    public function getUserTracks(): Collection
    {
        return $this->userTracks;
    }

    public function addUserHasTrack(UserHasTrack $userTracks): self
    {
        if (!$this->userTracks->contains($userTracks)) {
            $this->userTracks[] = $userTracks;
            $userTracks->setUser($this);
        }

        return $this;
    }

    public function removeUserHasTrack(UserHasTrack $userTracks): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userTracks->removeElement($userTracks) && $userTracks->getUser() === $this) {
            $userTracks->setUser(null);
        }

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

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

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Création d'un objet User à partir du payload JWT
     *
     * @param $id
     * @param array $payload
     * @return User
     */
    public static function createFromPayload($id, array $payload): User
    {
        $user = new User();
        $user->setId($id);
        $user->setRoles($payload['roles']);
        return $user;
    }

    /**
     * @return Collection|UserHasMatchup[]
     */
    public function getUserMatchs(): Collection
    {
        return $this->userMatchs;
    }

    public function addUserMatch(UserHasMatchup $userMatch): self
    {
        if (!$this->userMatchs->contains($userMatch)) {
            $this->userMatchs[] = $userMatch;
            $userMatch->setUser($this);
        }

        return $this;
    }

    public function removeUserMatch(UserHasMatchup $userMatch): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userMatchs->removeElement($userMatch) && $userMatch->getUser() === $this) {
            $userMatch->setUser(null);
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

    public function addUserProfile(UserProfile $userProfile): self
    {
        if (!$this->userProfiles->contains($userProfile)) {
            $this->userProfiles[] = $userProfile;
            $userProfile->setUser($this);
        }

        return $this;
    }

    public function removeUserProfile(UserProfile $userProfile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->userProfiles->removeElement($userProfile) && $userProfile->getUser() === $this) {
            $userProfile->setUser(null);
        }

        return $this;
    }

    /**
     * @return Collection|TalkUser[]
     */
    public function getTalkUsers(): Collection
    {
        return $this->talkUsers;
    }

    public function addTalkUser(TalkUser $talkUser): self
    {
        if (!$this->talkUsers->contains($talkUser)) {
            $this->talkUsers[] = $talkUser;
            $talkUser->setParticipant($this);
        }

        return $this;
    }

    public function removeTalkUser(TalkUser $talkUser): self
    {
        if ($this->talkUsers->removeElement($talkUser)) {
            // set the owning side to null (unless already changed)
            if ($talkUser->getParticipant() === $this) {
                $talkUser->setParticipant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|MessageUser[]
     */
    public function getMessageUsers(): Collection
    {
        return $this->messageUsers;
    }

    public function addMessageUser(MessageUser $messageUser): self
    {
        if (!$this->messageUsers->contains($messageUser)) {
            $this->messageUsers[] = $messageUser;
            $messageUser->setParticipant($this);
        }

        return $this;
    }

    public function removeMessageUser(MessageUser $messageUser): self
    {
        if ($this->messageUsers->removeElement($messageUser)) {
            // set the owning side to null (unless already changed)
            if ($messageUser->getParticipant() === $this) {
                $messageUser->setParticipant(null);
            }
        }

        return $this;
    }
}
