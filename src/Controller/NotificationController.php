<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
final class NotificationController extends AbstractController
{
    #[Route('', name: 'app_notifications', methods: ['GET'])]
    public function index(NotificationRepository $repository): Response
    {
        $user = $this->getCurrentUser();

        return $this->render('notification/index.html.twig', [
            'notifications' => $repository->findBy(['user' => $user], ['createdAt' => 'DESC']),
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

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
