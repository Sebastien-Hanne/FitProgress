<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $proxyEmail = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenAt = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isDeleted = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isProfileVisible = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $updatedAt = null;

    // --- RELATIONS ---

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?CoachProfile $coachProfile = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Goal $goal = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: JournalEntry::class, orphanRemoval: true)]
    private Collection $journalEntries;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CoachRequest::class, orphanRemoval: true)]
    private Collection $coachRequests;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Session::class, orphanRemoval: true)]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Conversation::class, orphanRemoval: true)]
    private Collection $conversations;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $sentMessages;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class, orphanRemoval: true)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Feedback::class, orphanRemoval: true)]
    private Collection $feedbacks;

    #[ORM\Column]
    private bool $isVerified = false;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->journalEntries = new ArrayCollection();
        $this->coachRequests = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Utile pour les listes déroulantes dans les formulaires Symfony
    public function __toString(): string
    {
        return $this->name ?? $this->email ?? 'Utilisateur inconnu';
    }

    // --- GETTERS & SETTERS BASIQUES ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getProxyEmail(): ?string
    {
        return $this->proxyEmail;
    }

    public function setProxyEmail(string $proxyEmail): static
    {
        $this->proxyEmail = $proxyEmail;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->resetToken = $resetToken;
        return $this;
    }

    public function getResetTokenAt(): ?\DateTimeImmutable
    {
        return $this->resetTokenAt;
    }

    public function setResetTokenAt(?\DateTimeImmutable $resetTokenAt): static
    {
        $this->resetTokenAt = $resetTokenAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->isDeleted = $isDeleted;
        return $this;
    }

    public function isProfileVisible(): bool
    {
        return $this->isProfileVisible;
    }

    public function setIsProfileVisible(bool $isProfileVisible): static
    {
        $this->isProfileVisible = $isProfileVisible;
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

    // --- RELATIONS ---

    public function getCoachProfile(): ?CoachProfile
    {
        return $this->coachProfile;
    }

    public function setCoachProfile(?CoachProfile $coachProfile): static
    {
        $this->coachProfile = $coachProfile;

        if ($coachProfile !== null && $coachProfile->getUser() !== $this) {
            $coachProfile->setUser($this);
        }

        return $this;
    }

    public function getGoal(): ?Goal
    {
        return $this->goal;
    }

    public function setGoal(?Goal $goal): static
    {
        $this->goal = $goal;

        if ($goal !== null && $goal->getUser() !== $this) {
            $goal->setUser($this);
        }

        return $this;
    }
    public function getJournalEntries(): Collection
    {
        return $this->journalEntries;
    }

    public function getCoachRequests(): Collection
    {
        return $this->coachRequests;
    }

    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}