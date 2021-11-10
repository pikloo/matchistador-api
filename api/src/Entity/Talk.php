<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\MatchUp;
use App\Entity\Message;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TalkRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @see http://schema.org/Talk Documentation on Schema.org
 */
#[ORM\Entity(repositoryClass: TalkRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    attributes: ['security' => "is_granted('ROLE_USER')", 'force_eager' => true,],
    iri: 'http://schema.org/Talk',
    itemOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
            'openapi_context' => [
                'summary'     => "Affiche toutes les discussions",
                'description' => "Affiche toutes les discussions.",
            ],
        ],
        'patch' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'denormalization_context' => ['groups' => ['talk_user:update']],
            'openapi_context' => [
                'summary'     => "Modifie une discussion",
                'description' => "Modifie une discussion.",
            ],
        ],
    ],
    subresourceOperations: [
        'messages_get_subresource' => [
            'method' => 'GET',
            'path' => '/talks/{id}/messages',
            'openapi_context' => [
                'summary'     => "Affiche les messages d'une discussion",
                'description' => "Affiche les messages d'une discussion",
            ],
        ]
    ],
    collectionOperations: [
        'post' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('create', object)",
            'openapi_context' => [
                'summary'     => "Création d'une discussion",
                'description' => "Création d'une discussion.",
            ],
        ],
        'get' => [
            'security' => "is_granted('ROLE_ADMIN')",
            'openapi_context' => [
                'summary'     => "Affiche une discussion",
                'description' => "Affiche une discussion.",
            ],
        ]
    ],
    normalizationContext: ['groups' => ['talk:read']],
    denormalizationContext: ['groups' => ['talk:write']],
)]
class Talk
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    #[Groups(groups: ['talk:read'])]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\OneToOne(inversedBy: 'talk', targetEntity: MatchUp::class, cascade: ['persist', 'remove'])]
    #[Groups(groups: ['talk:read'])]
    #[ORM\JoinColumn(nullable: true)]
    private $match;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(groups: ['talk:read'])]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(groups: ['talk:read'])]
    private $upadtedAt;

    #[ORM\OneToMany(mappedBy: 'talk', targetEntity: Message::class, orphanRemoval: true)]
    #[ApiSubresource]
    private $messages;

    #[Groups(groups: ['talk:read'])]
    #[ORM\OneToMany(mappedBy: 'talk', targetEntity: TalkUser::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[SerializedName('participants')]
    private $talkUsers;

    private $participants = [];

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->talkUsers = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getMatch(): ?MatchUp
    {
        return $this->match;
    }

    public function setMatch(MatchUp $match): self
    {
        $this->match = $match;

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
        return $this->upadtedAt;
    }

    public function getUpdatedAtAgo(): ?string
    {
        return $this->upadtedAt !== null ? Carbon::instance($this->upadtedAt)->diffForHumans() : null;
    }
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setTalk($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getTalk() === $this) {
                $message->setTalk(null);
            }
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
            $talkUser->setTalk($this);
        }

        return $this;
    }

    public function removeTalkUser(TalkUser $talkUser): self
    {
        if ($this->talkUsers->removeElement($talkUser)) {
            // set the owning side to null (unless already changed)
            if ($talkUser->getTalk() === $this) {
                $talkUser->setTalk(null);
            }
        }

        return $this;
    }

    public function getParticipants(): ?array
    {
        return $this->participants;
    }

    public function setParticipants(array $participants): self
    {
        $this->participants = $participants;

        return $this;
    }

    public function addParticipant(User $user){

        $talkUser = new TalkUser();
        $talkUser->setTalk($this);
        $talkUser->setParticipant($user);

        $this->addTalkUser($talkUser);

    }

    public function getAllParticipants(): ?array
    {
        $participants = [];
        foreach ($this->getTalkUsers()->getValues() as $participant){
            $participants[] = $participant->getParticipant();
        }

        return $participants;
    }

    public function isParticipantInTalk(User $user):bool
    {
        $participants = $this->getAllParticipants();

        return in_array($user, $participants);
    }
}
