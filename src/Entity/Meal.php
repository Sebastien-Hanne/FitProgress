<?php

namespace App\Entity;

use App\Enum\MealType;
use App\Repository\MealRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(
        targetEntity: JournalEntry::class,
        inversedBy: 'meals'
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?JournalEntry $journalEntry = null;

    #[ORM\Column(
        type: 'string',
        enumType: MealType::class
    )]
    private MealType $type;

    #[ORM\Column(length: 150)]
    private ?string $title = null;

    #[ORM\Column(nullable: true)]
    private ?int $calories = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJournalEntry(): ?JournalEntry
    {
        return $this->journalEntry;
    }

    public function setJournalEntry(?JournalEntry $journalEntry): static
    {
        $this->journalEntry = $journalEntry;
        return $this;
    }

    public function getType(): MealType
    {
        return $this->type;
    }

    public function setType(MealType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getCalories(): ?int
    {
        return $this->calories;
    }

    public function setCalories(?int $calories): static
    {
        $this->calories = $calories;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}