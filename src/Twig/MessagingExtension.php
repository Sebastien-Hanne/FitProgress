<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\CoachRequestRepository;
use App\Enum\RequestStatus;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MessagingExtension extends AbstractExtension
{
    public function __construct(private readonly Security $security, private readonly MessageRepository $messages, private readonly NotificationRepository $notifications, private readonly CoachRequestRepository $coachRequests)
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
        if (!$user instanceof User) {
            return 0;
        }

        $currentRequest = $this->coachRequests->findCurrentForUser($user);
        $hasActiveCoaching = $this->security->isGranted('ROLE_COACH')
            ? $user->getCoachProfile() !== null && $this->coachRequests->count([
                'coachProfile' => $user->getCoachProfile(),
                'status' => RequestStatus::Approved,
            ]) > 0
            : $currentRequest?->getStatus() === RequestStatus::Approved;

        return $hasActiveCoaching
            ? $this->notifications->countUnreadFor($user)
            : $this->notifications->countUnreadWithoutCoachingHistory($user);
    }
}
