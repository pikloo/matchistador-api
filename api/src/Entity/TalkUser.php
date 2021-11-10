<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\Talk;
use App\Entity\User;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TalkUserRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @see http://schema.org/TalkUser Documentation on Schema.org
 */
#[ORM\Entity()]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'userTalk',
    attributes: ['security' => "is_granted('ROLE_USER')"],
    iri: 'http://schema.org/TalkUser',
    subresourceOperations: [
        'api_users_talk_users_get_subresource' => [
            'normalization_context' => ['groups' => ['talk_user:subresource']],
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
        ],
    ],
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => "Affiche un participant à une discussion",
                'description' => "Affiche un participant à une discussion.",
            ],
        ],
        'patch' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'denormalization_context' => ['groups' => ['talk_user:update']],
            'openapi_context' => [
                'summary'     => "Modifie un participant à une discussion",
                'description' => "Modifie un participant à une discussion.",
            ],
        ],
    ],
    collectionOperations: [
        'post' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('create', object)",
            'openapi_context' => [
                'summary'     => "Création d'un participant à une discussion",
                'description' => "Création d'un participant à une discussion.",
            ],
        ],
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche tous les participants aux discussions",
                'description' => "Affiche tous les participants aux discussions.",
            ],
        ]
    ],
)]
#[UniqueEntity(fields: ["user", "talk"], message: "Cet utilisateur participe déja à cette discussion")]
#[ApiFilter(BooleanFilter::class, properties: ['readingStatus'])]
class TalkUser
{
    CONST NOT_READ = false;
    CONST READ =true;

    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['talk_user:subresource'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['talk:read', 'talk_user:subresource'])]
    private $readingStatus;

    #[ORM\ManyToOne(targetEntity: Talk::class, inversedBy: 'talkUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['talk_user:subresource'])]
    private $talk;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'talkUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['talk:read'])]
    #[SerializedName('user')]
    private $participant;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(groups: ['talk_user:subresource'])]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(groups: ['talk_user:subresource'])]
    private $updatedAt;

    public function __construct(){
        $this->readingStatus = self::NOT_READ;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getReadingStatus(): ?bool
    {
        return $this->readingStatus;
    }

    public function setReadingStatus(bool $readingStatus): self
    {
        $this->readingStatus = $readingStatus;

        return $this;
    }

    public function getTalk(): ?Talk
    {
        return $this->talk;
    }

    public function setTalk(?Talk $talk): self
    {
        $this->talk = $talk;

        return $this;
    }

    public function getParticipant(): ?User
    {
        return $this->participant;
    }

    public function setParticipant(?User $participant): self
    {
        $this->participant = $participant;

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

    public function getUpadtedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUpdatedAtAgo(): ?string
    {
        return $this->updatedAt !== null ? Carbon::instance($this->updatedAt)->diffForHumans() : null;
    }
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
