<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MessagingExtension extends AbstractExtension
{
    public function __construct(private readonly Security $security, private readonly MessageRepository $messages, private readonly NotificationRepository $notifications)
    {
    }

    public function getFunctions(): array
    {
        return [new TwigFunction('unread_message_count', $this->unreadCount(...)), new TwigFunction('unread_notification_count', $this->unreadNotificationCount(...))];
    }

    public function unreadCount(): int
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $this->messages->countUnreadFor($user) : 0;
    }

    public function unreadNotificationCount(): int
    {
        $user = $this->security->getUser();
        return $user instanceof User ? $this->notifications->countUnreadFor($user) : 0;
    }
}
