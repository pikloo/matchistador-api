<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\UserHasTrack;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\UserTrackFlagsRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserTrackFlagsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["userTrack"])]
class UserTrackFlags
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\OneToOne(targetEntity: UserHasTrack::class, inversedBy: 'userTrackFlags')]
    private ?UserHasTrack $userTrack = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $createFlag = false;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $updateFlag = false;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $deleteFlag = false;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function getUserTrack(): ?UserHasTrack
    {
        return $this->userTrack;
    }
    public function setUserTrack(?UserHasTrack $userTrack): self
    {
        $this->userTrack = $userTrack;

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
    public function getCreateFlag(): bool
    {
        return $this->createFlag;
    }
    public function setCreateFlag(bool $createFlag): self
    {
        $this->createFlag = $createFlag;

        return $this;
    }

    public function getUpdateFlag(): bool
    {
        return $this->updateFlag;
    }
    public function setUpdateFlag(bool $updateFlag): self
    {
        $this->updateFlag = $updateFlag;

        return $this;
    }

    public function getDeleteFlag(): bool
    {
        return $this->deleteFlag;
    }
    public function setDeleteFlag(bool $deleteFlag): self
    {
        $this->deleteFlag = $deleteFlag;

        return $this;
    }

}
