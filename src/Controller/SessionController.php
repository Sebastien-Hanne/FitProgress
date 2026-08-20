<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SessionController extends AbstractController
{
    #[Route('/sessions', name: 'app_user_sessions', methods: ['GET'])]
    public function index(SessionRepository $sessions): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User || $this->isGranted('ROLE_COACH')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions->findForUser($user),
        ]);
    }
}
