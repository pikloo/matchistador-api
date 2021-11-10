<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\UserData;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\UserDataFlagsRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserDataFlagsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["userData"])]
class UserDataFlags
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\OneToOne(targetEntity: UserData::class, inversedBy: 'userDataFlags')]
    private ?UserData $userData = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $geoFlag = false;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $genderFlag = false;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $orientationFlag = false;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function getUserData(): ?UserData
    {
        return $this->userData;
    }
    public function setUserData(?UserData $userData): self
    {
        $this->userData = $userData;

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
    public function getGeo(): bool
    {
        return $this->geoFlag;
    }
    public function setGeo(bool $geoFlag): self
    {
        $this->geoFlag = $geoFlag;

        return $this;
    }

    public function getGender(): bool
    {
        return $this->genderFlag;
    }
    public function setGender(bool $genderFlag): self
    {
        $this->genderFlag = $genderFlag;

        return $this;
    }

    public function getOrientation(): bool
    {
        return $this->orientationFlag;
    }
    public function setOrientation(bool $orientationFlag): self
    {
        $this->orientationFlag = $orientationFlag;

        return $this;
    }


}
