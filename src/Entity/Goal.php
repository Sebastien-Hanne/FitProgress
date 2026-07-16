<?php

namespace App\Entity;

use App\Enum\Gender;
use App\Repository\GoalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Goal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(
        targetEntity: User::class,
        inversedBy: 'goal'
    )]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column]
    private ?int $heightCm = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private ?string $initialWeight = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private ?string $targetWeight = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeImmutable $birthDate = null;

    #[ORM\Column(
        type: 'string',
        enumType: Gender::class,
        nullable: true
    )]
    private ?Gender $gender = null;

    #[ORM\Column(options: ['default' => 2000])]
    private int $hydrationGoalMl = 2000;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getHeightCm(): ?int
    {
        return $this->heightCm;
    }

    public function setHeightCm(int $heightCm): static
    {
        $this->heightCm = $heightCm;
        return $this;
    }

    public function getInitialWeight(): ?string
    {
        return $this->initialWeight;
    }

    public function setInitialWeight(string $initialWeight): static
    {
        $this->initialWeight = $initialWeight;
        return $this;
    }

    public function getTargetWeight(): ?string
    {
        return $this->targetWeight;
    }

    public function setTargetWeight(string $targetWeight): static
    {
        $this->targetWeight = $targetWeight;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getHydrationGoalMl(): int
    {
        return $this->hydrationGoalMl;
    }

    public function setHydrationGoalMl(int $hydrationGoalMl): static
    {
        $this->hydrationGoalMl = $hydrationGoalMl;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}