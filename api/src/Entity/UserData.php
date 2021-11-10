<?php

namespace App\Entity;

use Carbon\Carbon;
use App\DBAL\Types\GenderType;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserDataRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\DBAL\Types\SexualOrientationType;
use App\DBAL\Types\StreamingPlatformType;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Jsor\Doctrine\PostGIS\Types\PostGISType;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Fresh\DoctrineEnumBundle\Validator\Constraints as DoctrineAssert;

/**
 * @see http://schema.org/UserData Documentation on Schema.org
 */
#[ApiResource(
    subresourceOperations: [
        'api_users_user_data_get_subresource' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
        ],
    ],
    iri: 'http://schema.org/UserData',
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => "Affiche les informations d'un utilisateur",
                'description' => "Affiche les informations d'un utilisateur.",
            ],
        ],
        'patch' => [
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'denormalization_context' => ['groups' => ['user_data:update']],
            'openapi_context' => [
                'summary'     => "Modifie les informations d'un utilisateur",
                'description' => "Modifie les informations d'un utilisateur.",
            ],
        ],
    ],
    collectionOperations: [
        'post' => [
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('create', object)",
            'validation_groups' => ['Default', 'registration'],
            'denormalization_context' => ['groups' => ['user_data:create']],
            'openapi_context' => [
                'summary'     => "Création des informations d'un utilisateur",
                'description' => "Création des informations d'un utilisateur.",
            ],
        ],
        'get' => [
            // 'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche les informations des utilisateurs",
                'description' => "Affiche les informations des utilisateurs.",
            ],
        ]
    ],
    normalizationContext: ['groups' => ['user_data:read']],
    denormalizationContext: ['groups' => ['user_data:write']],
)]
#[ORM\Entity(repositoryClass: UserDataRepository::class)]
#[ORM\HasLifecycleCallbacks]
class UserData
{
    // const GENDER = ['male', 'female'];
    // const SEXUAL_ORIENTATION = ['male', 'female', 'both'];
    // const STREAMING_PLATFORM = ['spotify', 'deezer'];

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['user:read','user_data:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\Column(type: 'string', length: 64)]
    #[Assert\NotBlank(groups: ['registration'], message: "Ton nom n'est pas renseigné")]
    #[Groups(groups: ['user:read', 'user_data:read', 'user_data:update', 'user_data:create', 'user_matchs:read','talk:read'])]
    private ?string $name = null;
    /**
     * @var \DateTime|null|mixed
     */
    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotBlank(groups: ['registration'], message: "Ta date de naissance n'est pas renseigné")]
    #[Assert\LessThan(value: '-18 years', message: "Tu n'as pas 18 ans")]
    #[Groups(groups: [ 'user_data:update', 'user_data:create', 'user_matchs:read','admin:read', 'owner:read','talk:read'])]
    private $birthDate;

    #[Assert\NotBlank(groups: ['registration'])]
    #[Assert\Range(min: -90, max: 90, notInRangeMessage: 'You must be between {{ min }} and {{ max }} tall to enter')]
    #[Groups(groups: ['user_data:update', 'user_data:create'])]
    private ?float $positionLat = null;
 
    #[Assert\NotBlank(groups: ['registration'])]
    #[Assert\Range(min: -180, max: 180, notInRangeMessage: 'You must be between {{ min }} and {{ max }} tall to enter')]
    #[Groups(groups: ['user_data:update', 'user_data:create'])]
    private ?float $positionLng = null;


    #[ORM\Column(
        type: PostGISType::GEOGRAPHY, 
        // options: ['geography_type' => 'POINT', 'srid' => 4326],
        nullable: true
    )]
    public string $location; 

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(groups: ['user:read', 'user_data:read', 'user_matchs:read', 'user_data:update', 'user_data:create','talk:read'])]
    private ?string $city = null;

    #[ORM\Column(type: 'StreamingPlatformType', nullable: true)]
    #[Assert\NotBlank(groups: ['registration'], message: "La plateforme de streaming n'est pas renseigné")]
    #[Groups(groups: ['owner:read', 'admin:read', 'user_data:update', 'user_data:create', 'user_data:read','talk:read'])]
    /**
     * @DoctrineAssert\Enum(entity="App\DBAL\Types\StreamingPlatformType",message="Ton choix n'est pas valide")  
     */ 
    private ?string $streamingPlatform = null;

    #[ORM\Column(type: 'GenderType', nullable: true)]
    #[Assert\NotBlank(groups: ['registration'], message: "Ton genre n'est pas renseigné")]
    #[Groups(groups: ['owner:read', 'user_data:update', 'user_data:create', 'user_data:read','talk:read'])]
    /**
     * @DoctrineAssert\Enum(entity="App\DBAL\Types\GenderType",message="Ton choix n'est pas valide")  
     */ 
    private ?string $gender = null;

    #[ORM\Column(type: 'SexualOrientationType', nullable: true)]
    #[Assert\NotBlank(groups: ['registration'], message: "Ton orientation sexuelle n'est pas renseigné")]
    #[Groups(groups: ['owner:read', 'admin:read', 'user_data:update', 'user_data:create', 'user_data:read'])]
     /**
     * @DoctrineAssert\Enum(entity="App\DBAL\Types\SexualOrientationType",message="Ton choix n'est pas valide")  
     */ 
    private ?string $sexualOrientation = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(groups: ['user:read', 'user_data:update', 'user_data:create', 'user_matchs:read' ,'user_data:read','talk:read'])]
    private ?string $pictureUrl = null;

    #[ApiProperty(iri: 'http://schema.org/uploaded-picture')]
    #[ORM\ManyToOne(targetEntity: UserPicture::class, cascade: ['persist', 'remove'])]
    #[Groups(groups: ['user:read','user_data:read','user_data:create','user_data:update', 'user_matchs:read','talk:read'])]
    public ?UserPicture $uploadedPicture = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userData', cascade: ['persist', 'remove'])]
    #[Groups(groups: ['user_data:create'])]
    private ?User $user = null;

    #[ORM\OneToOne(targetEntity: UserDataFlags::class, mappedBy: 'userData', cascade: ['persist', 'remove'])]
    private ?UserDataFlags $userDataFlags = null;
    /**
     * @var string|null|mixed
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(groups: ['user:write'])]
    private $activation_token;
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
    /**
     * @return mixed|null
     */
    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }
    public function setBirthDate(?\DateTime $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    #[Groups(groups: ['owner:read', 'admin:read', 'user_data:read','talk:read'])]
    #[SerializedName('birthDate')]
    public function getDateFormat()
    {
        return $this->birthDate ? Carbon::instance($this->birthDate)->format('Y-m-d') : null;
    }

    #[Groups(groups: ['user:read','user_matchs:read', 'user_data:read','talk:read'])]
    public function getAge()
    {
        return $this->birthDate ? Carbon::instance($this->birthDate)->diffInYears(): null;
    }

    public function getPositionLat(): ?float
    {
        return $this->positionLat;
    }
    public function setPositionLat(?float $positionLat): self
    {
        $this->positionLat = $positionLat;

        return $this;
    }
    public function getPositionLng(): ?float
    {
        return $this->positionLng;
    }
    public function setPositionLng(?float $positionLng): self
    {
        $this->positionLng = $positionLng;

        return $this;
    }
    public function getStreamingPlatform(): ?string
    {
        return $this->streamingPlatform;
    }
    public function setStreamingPlatform(?string $streamingPlatform): self
    {
        $this->streamingPlatform = $streamingPlatform;

        return $this;
    }
    public function getGender(): ?string
    {
        return $this->gender;
    }
    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
    public function getSexualOrientation(): ?string
    {
        return $this->sexualOrientation;
    }
    public function setSexualOrientation(?string $sexualOrientation): self
    {
        $this->sexualOrientation = $sexualOrientation;

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
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
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
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
    public function getCity(): ?string
    {
        return $this->city;
    }
    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }
    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }
    /**
     * Set the value of activation_token
     */
    public function setActivationToken($activation_token): self
    {
        $this->activation_token = $activation_token;

        return $this;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    public function getUserDataFlags(): ?UserDataFlags
    {
        return $this->userDataFlags;
    }

    public function setUserDataFlags(?UserDataFlags $userDataFlags): self
    {
        // unset the owning side of the relation if necessary
        if ($userDataFlags === null && $this->userDataFlags !== null) {
            $this->userDataFlags->setUserData(null);
        }

        // set the owning side of the relation if necessary
        if ($userDataFlags !== null && $userDataFlags->getUserData() !== $this) {
            $userDataFlags->setUserData($this);
        }

        $this->userDataFlags = $userDataFlags;

        return $this;
    }
}
