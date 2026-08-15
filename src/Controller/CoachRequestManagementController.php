<?php

namespace App\Controller;

use App\Entity\CoachRequest;
use App\Entity\Conversation;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Enum\RequestStatus;
use App\Repository\CoachRequestRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/coach/demandes', name: 'app_coach_requests_')]
final class CoachRequestManagementController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(CoachRequestRepository $requests): Response
    {
        $coach = $this->coachUser();
        return $this->render('coach/requests.html.twig', ['requests' => $requests->findBy(['coachProfile' => $coach->getCoachProfile()], ['createdAt' => 'DESC'])]);
    }

    #[Route('/{id}/accepter', name: 'accept', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function accept(CoachRequest $coachRequest, Request $request, ConversationRepository $conversations, EntityManagerInterface $entityManager): Response
    {
        $coach = $this->coachUser();
        if ($coachRequest->getCoachProfile() !== $coach->getCoachProfile() || $coachRequest->getStatus() !== RequestStatus::Pending || !$this->isCsrfTokenValid('accept_coach_request_'.$coachRequest->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $coachRequest->setStatus(RequestStatus::Approved);
        $conversation = $conversations->findOneForPair($coachRequest->getUser(), $coachRequest->getCoachProfile());
        if (!$conversation) {
            $conversation = (new Conversation())->setUser($coachRequest->getUser())->setCoachProfile($coachRequest->getCoachProfile())->setTitle('Accompagnement avec '.$coach->getName());
            $entityManager->persist($conversation);
        }
        $notification = (new Notification())->setUser($coachRequest->getUser())->setType(NotificationType::request_accepted)->setTitle('Demande acceptée')->setContent($coach->getName().' a accepté votre demande. La messagerie est ouverte.');
        $entityManager->persist($notification);
        $entityManager->flush();
        return $this->redirectToRoute('app_messages_show', ['id' => $conversation->getId()]);
    }

    private function coachUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$user->getCoachProfile()) throw $this->createAccessDeniedException();
        return $user;
    }
}
