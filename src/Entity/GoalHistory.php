<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class GoalHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $targetWeight;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $targetDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $archivedAt;

    public function __construct(User $user, string $targetWeight, ?\DateTimeImmutable $targetDate)
    {
        $this->user = $user;
        $this->targetWeight = $targetWeight;
        $this->targetDate = $targetDate;
        $this->archivedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getUser(): ?User { return $this->user; }
    public function getTargetWeight(): string { return $this->targetWeight; }
    public function getTargetDate(): ?\DateTimeImmutable { return $this->targetDate; }
    public function getArchivedAt(): \DateTimeImmutable { return $this->archivedAt; }
}
