<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\Message;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\MessageUserRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @see http://schema.org/MessageUser Documentation on Schema.org
 */
#[ORM\Entity(repositoryClass: MessageUserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    shortName: 'userMessage',
    attributes: ['security' => "is_granted('ROLE_USER')"],
    iri: 'http://schema.org/MessageUser',
    itemOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => "Affiche un participant à un message",
                'description' => "Affiche un participant à un message.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche tous les participants aux messages",
                'description' => "Affiche tous les participants aux messages",
            ],
        ]
    ],
)]
#[UniqueEntity(fields: ["message", "participant"], message: "Cet utilisateur est déja lié à ce message")]
class MessageUser
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
    private UuidInterface $id;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['message:read','message:subresource'])]
    private $readingStatus;

    #[ORM\ManyToOne(targetEntity: Message::class, inversedBy: 'messageUsers')]
    #[ORM\JoinColumn(nullable: false, onDelete:"CASCADE")]
    private $message;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messageUsers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read'])]
    private $participant;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    public function __construct(){
        $this->readingStatus = self::NOT_READ;
    }

    public function getId(): \Ramsey\Uuid\UuidInterface
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

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

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
