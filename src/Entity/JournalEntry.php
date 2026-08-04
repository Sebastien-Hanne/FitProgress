<?php

namespace App\Entity;

use App\Enum\EnergyLevel;
use App\Enum\Mood;
use App\Repository\JournalEntryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: JournalEntryRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'unique_user_date', columns: ['user_id', 'date'])]
class JournalEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'journalEntries')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\OneToMany(
        mappedBy: 'journalEntry',
        targetEntity: Meal::class,
        orphanRemoval: true,
        cascade: ['persist', 'remove']
    )]
    private Collection $meals;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    #[Assert\Positive(message: 'Le poids doit être supérieur à zéro.')]
    #[Assert\LessThanOrEqual(500, message: 'Le poids renseigné est trop élevé.')]
    private ?string $weight = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    private ?string $bmi = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'L’hydratation ne peut pas être négative.')]
    #[Assert\LessThanOrEqual(20000, message: 'L’hydratation renseignée est trop élevée.')]
    private ?int $waterIntakeMl = null;

    #[ORM\Column(nullable: true)]
    private ?int $steps = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'La durée d’activité ne peut pas être négative.')]
    #[Assert\LessThanOrEqual(1440, message: 'La durée d’activité ne peut pas dépasser une journée.')]
    private ?int $activityMinutes = null;

    #[ORM\Column(
        type: 'smallint',
        enumType: EnergyLevel::class,
        nullable: true
    )]
    private ?EnergyLevel $energyLevel = null;

    #[ORM\Column(
        type: 'string',
        enumType: Mood::class,
        nullable: true
    )]
    private ?Mood $mood = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 4, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: 'La durée de sommeil ne peut pas être négative.')]
    #[Assert\LessThanOrEqual(24, message: 'La durée de sommeil ne peut pas dépasser 24 heures.')]
    private ?string $sleepHours = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\Range(min: 1, max: 5, notInRangeMessage: 'La qualité du sommeil doit être comprise entre 1 et 5.')]
    private ?int $sleepQuality = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $coachComment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->meals = new ArrayCollection();
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

    public function getMeals(): Collection
    {
        return $this->meals;
    }

    public function addMeal(Meal $meal): static
    {
        if (!$this->meals->contains($meal)) {
            $this->meals->add($meal);
            $meal->setJournalEntry($this);
        }

        return $this;
    }

    public function removeMeal(Meal $meal): static
    {
        if ($this->meals->removeElement($meal)) {
            if ($meal->getJournalEntry() === $this) {
                $meal->setJournalEntry(null);
            }
        }

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function setWeight(?string $weight): static
    {
        $this->weight = $weight;
        return $this;
    }

    public function getBmi(): ?string
    {
        return $this->bmi;
    }

    public function setBmi(?string $bmi): static
    {
        $this->bmi = $bmi;
        return $this;
    }

    public function recalculateBmi(?int $heightCm): void
    {
        if ($this->weight === null || $heightCm === null || $heightCm <= 0) {
            $this->bmi = null;
            return;
        }

        $heightMeters = $heightCm / 100;
        $this->bmi = number_format((float) $this->weight / ($heightMeters ** 2), 2, '.', '');
    }

    public function getTotalCalories(): int
    {
        return array_sum($this->meals->map(
            static fn (Meal $meal): int => $meal->getCalories() ?? 0
        )->toArray());
    }

    public function getWaterIntakeMl(): ?int
    {
        return $this->waterIntakeMl;
    }

    public function setWaterIntakeMl(?int $waterIntakeMl): static
    {
        $this->waterIntakeMl = $waterIntakeMl;
        return $this;
    }

    public function getSteps(): ?int
    {
        return $this->steps;
    }

    public function setSteps(?int $steps): static
    {
        $this->steps = $steps;
        return $this;
    }

    public function getActivityMinutes(): ?int
    {
        return $this->activityMinutes;
    }

    public function setActivityMinutes(?int $activityMinutes): static
    {
        $this->activityMinutes = $activityMinutes;
        return $this;
    }

    public function getEnergyLevel(): ?EnergyLevel
    {
        return $this->energyLevel;
    }

    public function setEnergyLevel(?EnergyLevel $energyLevel): static
    {
        $this->energyLevel = $energyLevel;
        return $this;
    }

    public function getMood(): ?Mood
    {
        return $this->mood;
    }

    public function setMood(?Mood $mood): static
    {
        $this->mood = $mood;
        return $this;
    }

    public function getSleepHours(): ?string
    {
        return $this->sleepHours;
    }

    public function setSleepHours(?string $sleepHours): static
    {
        $this->sleepHours = $sleepHours;
        return $this;
    }

    public function getSleepQuality(): ?int
    {
        return $this->sleepQuality;
    }

    public function setSleepQuality(?int $sleepQuality): static
    {
        $this->sleepQuality = $sleepQuality;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;
        return $this;
    }

    public function getCoachComment(): ?string
    {
        return $this->coachComment;
    }

    public function setCoachComment(?string $coachComment): static
    {
        $this->coachComment = $coachComment;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
