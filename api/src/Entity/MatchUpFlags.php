<?php

namespace App\Entity;

use Carbon\Carbon;
use App\Entity\UserHasMatchup;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use App\Repository\MatchUpFlagsRepository;
use App\Repository\UserMatchUpFlagsRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MatchUpFlagsRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ["match"])]
class MatchUpFlags
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private \Ramsey\Uuid\UuidInterface $id;

    #[ORM\OneToOne(targetEntity: MatchUp::class, inversedBy: 'matchFlags')]
    private ?MatchUp $match = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: 'boolean',nullable: true)]
    private bool $calculFlag = false;

    public function getId(): \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    public function getMatch(): ?MatchUp
    {
        return $this->match;
    }
    public function setMatch(?MatchUp $match): self
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
    public function getCalculFlag(): bool
    {
        return $this->calculFlag;
    }
    public function setCalculFlag(bool $calculFlag): self
    {
        $this->calculFlag = $calculFlag;

        return $this;
    }

    // public function getUserMatchFlags(): ?UserMatchUpFlags
    // {
    //     return $this->userMatchFlags;
    // }

    // public function setUserMatchFlags(?UserMatchUpFlags $userMatchFlags): self
    // {
    //     // unset the owning side of the relation if necessary
    //     if ($userMatchFlags === null && $this->userMatchFlags !== null) {
    //         $this->userMatchFlags->setUserMatch(null);
    //     }

    //     // set the owning side of the relation if necessary
    //     if ($userMatchFlags !== null && $userMatchFlags->getUserMatch() !== $this) {
    //         $userMatchFlags->setUserMatch($this);
    //     }

    //     $this->userMatchFlags = $userMatchFlags;

    //     return $this;
    // }

}

