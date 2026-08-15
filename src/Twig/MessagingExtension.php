<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MessagingExtension extends AbstractExtension
{
    public function __construct(private readonly Security $security, private readonly MessageRepository $messages)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('unread_message_count', $this->unreadCount(...))];
    }

    public function unreadCount(): int
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $this->messages->countUnreadFor($user) : 0;
    }
}
