<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Repository\NotificationRepository;
use App\Repository\CoachRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
final class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notifications', methods: ['GET'])]
    public function index(NotificationRepository $repository, CoachRequestRepository $coachRequests): Response
    {
        $user = $this->getCurrentUser();
        $currentRequest = $coachRequests->findCurrentForUser($user);
        $hasActiveCoaching = $this->isGranted('ROLE_COACH')
            ? $user->getCoachProfile() !== null && $coachRequests->count([
                'coachProfile' => $user->getCoachProfile(),
                'status' => \App\Enum\RequestStatus::Approved,
            ]) > 0
            : $currentRequest?->getStatus() === \App\Enum\RequestStatus::Approved;

        return $this->render('notification/index.html.twig', [
            'notifications' => $hasActiveCoaching
                ? $repository->findBy(['user' => $user], ['createdAt' => 'DESC'])
                : $repository->findWithoutCoachingHistory($user),
        ]);
    }

    #[Route('/read-all', name: 'app_notifications_read_all', methods: ['POST'])]
    public function readAll(Request $request, NotificationRepository $repository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('notifications-read-all', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }

        foreach ($repository->findBy(['user' => $this->getCurrentUser(), 'isRead' => false]) as $notification) {
            $notification->setIsRead(true);
        }
        $entityManager->flush();

        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/{id}/read', name: 'app_notifications_read', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function read(Notification $notification, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($notification->getUser() !== $this->getCurrentUser() || !$this->isCsrfTokenValid('notification-read-'.$notification->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $notification->setIsRead(true);
        $entityManager->flush();
        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/{id}/delete', name: 'app_notifications_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Notification $notification, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($notification->getUser() !== $this->getCurrentUser()
            || !$this->isCsrfTokenValid('notification-delete-'.$notification->getId(), $request->request->getString('_token'))
        ) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($notification);
        $entityManager->flush();

        return $this->redirectToRoute('app_notifications');
    }

    #[Route('/{id}/open', name: 'app_notifications_open', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function open(Notification $notification, EntityManagerInterface $entityManager): Response
    {
        if ($notification->getUser() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $entityManager->flush();
        }

        return match ($notification->getType()) {
            NotificationType::new_message, NotificationType::request_accepted => $this->redirectToRoute('app_messages_index'),
            NotificationType::session_scheduled, NotificationType::session_modified, NotificationType::session_cancelled => $this->redirectToRoute('app_user_sessions'),
            NotificationType::new_feedback => $this->redirectToRoute('app_feedback'),
            NotificationType::new_coach_request => $this->redirectToRoute('app_coach_requests_index'),
            default => $this->redirectToRoute('app_notifications'),
        };
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
