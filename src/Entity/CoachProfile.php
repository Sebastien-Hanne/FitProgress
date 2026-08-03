<?php

namespace App\Entity;

use App\Enum\CertifStatus;
use App\Enum\CoachingStyle;
use App\Repository\CoachProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoachProfileRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CoachProfile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'coachProfile')]
    #[ORM\JoinColumn(nullable: false, unique: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $bio = null;

    #[ORM\Column(type: 'string', enumType: CoachingStyle::class, nullable: true)]
    private ?CoachingStyle $coachingStyle = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $specialties = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $experienceYears = null;

    #[ORM\Column(type: 'string', enumType: CertifStatus::class)]
    private CertifStatus $certificateStatus = CertifStatus::Pending;

    #[ORM\Column(options: ['default' => false])]
    private bool $isAvailable = false;

    // Renommé pour la cohérence (Franglais -> Anglais)
    #[ORM\Column(type: 'smallint', options: ['default' => 15])]
    private int $maxCapacity = 15;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // --- AJOUT DES COLLECTIONS MANQUANTES ---
    #[ORM\OneToMany(mappedBy: 'coachProfile', targetEntity: Certificate::class, orphanRemoval: true)]
    private Collection $certificates;

    #[ORM\OneToMany(mappedBy: 'coachProfile', targetEntity: CoachRequest::class, orphanRemoval: true)]
    private Collection $coachRequests;

    #[ORM\OneToMany(mappedBy: 'coachProfile', targetEntity: Conversation::class, orphanRemoval: true)]
    private Collection $conversations;

    #[ORM\OneToMany(mappedBy: 'coachProfile', targetEntity: Session::class, orphanRemoval: true)]
    private Collection $sessions;

    #[ORM\OneToMany(mappedBy: 'coachProfile', targetEntity: Feedback::class, orphanRemoval: true)]
    private Collection $feedbacks;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        // Initialisation obligatoire des collections
        $this->certificates = new ArrayCollection();
        $this->coachRequests = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->sessions = new ArrayCollection();
        $this->feedbacks = new ArrayCollection();
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

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): static
    {
        $this->bio = $bio;
        return $this;
    }

    public function getCoachingStyle(): ?CoachingStyle
    {
        return $this->coachingStyle;
    }

    public function setCoachingStyle(?CoachingStyle $coachingStyle): static
    {
        $this->coachingStyle = $coachingStyle;
        return $this;
    }

    public function getSpecialties(): ?string
    {
        return $this->specialties;
    }

    public function setSpecialties(?string $specialties): static
    {
        $this->specialties = $specialties;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getExperienceYears(): ?int
    {
        return $this->experienceYears;
    }

    public function setExperienceYears(?int $experienceYears): static
    {
        $this->experienceYears = $experienceYears;
        return $this;
    }

    public function getCertificateStatus(): CertifStatus
    {
        return $this->certificateStatus;
    }

    public function setCertificateStatus(CertifStatus $certificateStatus): static
    {
        $this->certificateStatus = $certificateStatus;
        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    public function getMaxCapacity(): int
    {
        return $this->maxCapacity;
    }

    public function setMaxCapacity(int $maxCapacity): static
    {
        $this->maxCapacity = $maxCapacity;
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

    /**
     * @return Collection<int, Certificate>
     */
    public function getCertificates(): Collection
    {
        return $this->certificates;
    }

    public function addCertificate(Certificate $certificate): static
    {
        if (!$this->certificates->contains($certificate)) {
            $this->certificates->add($certificate);
            $certificate->setCoachProfile($this);
        }

        return $this;
    }

    public function removeCertificate(Certificate $certificate): static
    {
        if ($this->certificates->removeElement($certificate)) {
            // set the owning side to null (unless already changed)
            if ($certificate->getCoachProfile() === $this) {
                $certificate->setCoachProfile(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CoachRequest>
     */
    public function getCoachRequests(): Collection
    {
        return $this->coachRequests;
    }

    public function addCoachRequest(CoachRequest $coachRequest): static
    {
        if (!$this->coachRequests->contains($coachRequest)) {
            $this->coachRequests->add($coachRequest);
            $coachRequest->setCoachProfile($this);
        }

        return $this;
    }

    public function removeCoachRequest(CoachRequest $coachRequest): static
    {
        if ($this->coachRequests->removeElement($coachRequest)) {
            // set the owning side to null (unless already changed)
            if ($coachRequest->getCoachProfile() === $this) {
                $coachRequest->setCoachProfile(null);
            }
        }

        return $this;
    }

    /** @return Collection<int, Conversation> */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): static
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setCoachProfile($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): static
    {
        if ($this->conversations->removeElement($conversation) && $conversation->getCoachProfile() === $this) {
            $conversation->setCoachProfile(null);
        }

        return $this;
    }

    /** @return Collection<int, Session> */
    public function getSessions(): Collection
    {
        return $this->sessions;
    }

    public function addSession(Session $session): static
    {
        if (!$this->sessions->contains($session)) {
            $this->sessions->add($session);
            $session->setCoachProfile($this);
        }

        return $this;
    }

    public function removeSession(Session $session): static
    {
        if ($this->sessions->removeElement($session) && $session->getCoachProfile() === $this) {
            $session->setCoachProfile(null);
        }

        return $this;
    }

    /** @return Collection<int, Feedback> */
    public function getFeedbacks(): Collection
    {
        return $this->feedbacks;
    }

    public function addFeedback(Feedback $feedback): static
    {
        if (!$this->feedbacks->contains($feedback)) {
            $this->feedbacks->add($feedback);
            $feedback->setCoachProfile($this);
        }

        return $this;
    }

    public function removeFeedback(Feedback $feedback): static
    {
        if ($this->feedbacks->removeElement($feedback) && $feedback->getCoachProfile() === $this) {
            $feedback->setCoachProfile(null);
        }

        return $this;
    }
}
