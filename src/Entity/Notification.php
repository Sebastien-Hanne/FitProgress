<?php

namespace App\Entity;

use App\Enum\NotificationType;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'notifications'
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;


    #[ORM\Column(
        type: 'string',
        enumType: NotificationType::class
    )]
    private NotificationType $type;


    #[ORM\Column(length: 150)]
    private ?string $title = null;


    #[ORM\Column(type: 'text')]
    private ?string $content = null;


    #[ORM\Column(options: ['default' => false])]
    private bool $isRead = false;


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;


    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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


    public function getType(): NotificationType
    {
        return $this->type;
    }


    public function setType(NotificationType $type): static
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


    public function getContent(): ?string
    {
        return $this->content;
    }


    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }


    public function isRead(): bool
    {
        return $this->isRead;
    }


    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}