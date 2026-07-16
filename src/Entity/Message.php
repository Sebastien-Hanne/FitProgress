<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(
        targetEntity: Conversation::class,
        inversedBy: 'messages'
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;


    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: 'sentMessages'
    )]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;


    #[ORM\Column(length: 1000)]
    private ?string $content = null;


    #[ORM\Column(options: ['default' => false])]
    private bool $isRead = false;


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $sentAt = null;


    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;


    public function __construct()
    {
        $this->sentAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }


    public function setConversation(?Conversation $conversation): static
    {
        $this->conversation = $conversation;
        return $this;
    }


    public function getSender(): ?User
    {
        return $this->sender;
    }


    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
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


    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }


    public function setSentAt(\DateTimeImmutable $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }


    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}