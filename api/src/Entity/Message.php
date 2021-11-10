<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\Talk;
use App\Entity\MessageUser;
use Ramsey\Uuid\UuidInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MessageRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use Symfony\Component\Serializer\Annotation\SerializedName;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']],
    subresourceOperations: [
        'api_talks_messages_get_subresource' => [
            'normalization_context' => ['groups' => ['message:subresource']],
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
        ],
    ],
    itemOperations: [
        'get' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('read', object)",
            'openapi_context' => [
                'summary'     => 'Affiche un message',
                'description' => "Affiche un umessage",
            ],
        ],
        'patch' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('update', object)",
            'denormalization_context' => ['groups' => ['message:update']],
            'openapi_context' => [
                'summary'     => "Modifie un message",
                'description' => "Modifie un message.",
                'requestBody' => [
                    'content' => [
                        'aapplication/merge-patch+json' => [
                            'schema'  => [
                                'properties' =>
                                [
                                    'content' => [
                                        'type' => 'string',
                                        'example' => 'Salut j\'ai vu qu\'on avait pas mal de matchs musicaux 💃'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'delete' => [
            'security' => "is_granted('ROLE_ADMIN') or is_granted('delete', object)",
            'openapi_context' => [
                'summary'     => "Supprime un message",
                'description' => "Supprime un message.",
            ],
        ],
    ],
    collectionOperations: [
        'get' => [
            'openapi_context' => [
                'summary'     => 'Affiche tous les messages',
                'description' => "Affiche tous les messages.",
            ],
        ],
        'post' => [
            // 'security' => "is_granted('ROLE_ADMIN') or is_granted('create', object)",
            'denormalization_context' => ['groups' => ['message:post']],
            'openapi_context' => [
                'summary'     => "Création d'un message",
                'description' => "Création d'un message.",
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema'  => [
                                'properties' =>
                                [
                                    'author' => [
                                        'type' => 'string',
                                        'example' => '/users/93f7647c-ea8a-4360-b6e5-4683e05f7a0e'
                                    ],
                                    'content' => [
                                        'type' => 'string',
                                        'example' => 'Salut j\'ai vu qu\'on avait pas mal de matchs musicaux 💃'
                                    ],
                                    'talk' => [
                                        'type' => 'string',
                                        'example' => '/talks/2a32da7b-7296-408a-89bf-e80ccc130f48'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]
    ],
)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt'], arguments: ['orderParameterName' => 'order'])]
class Message
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['message:post', 'message:read', 'message:subresource'])]
    private $author;

    #[Groups(['message:read', 'message:subresource'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $updatedAt;

    #[ORM\Column(type: 'text')]
    #[Groups(['message:post', 'message:read', 'message:update', 'message:subresource'])]
    private $content;

    #[ORM\ManyToOne(targetEntity: Talk::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:post'])]
    private $talk;


    #[ORM\OneToMany(mappedBy: 'message', targetEntity: MessageUser::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['message:read', 'message:subresource'])]
    #[SerializedName('status')]
    private $messageUsers;

    public function __construct()
    {
        $this->messageUsers = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    
    #[SerializedName('createdAt')]
    public function getCreatedAtFormat()
    {
        return $this->createdAt ? Carbon::instance($this->createdAt)->format('Y-m-d h:m:s') : null;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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
            $messageUser->setMessage($this);
        }

        return $this;
    }

    public function removeMessageUser(MessageUser $messageUser): self
    {
        if ($this->messageUsers->removeElement($messageUser)) {
            // set the owning side to null (unless already changed)
            if ($messageUser->getMessage() === $this) {
                $messageUser->setMessage(null);
            }
        }

        return $this;
    }

    public function addParticipant(User $user)
    {

        $messageUser = new MessageUser();
        $messageUser->setMessage($this);
        $messageUser->setParticipant($user);

        $this->addMessageUser($messageUser);
    }
}
