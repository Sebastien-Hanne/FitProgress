<?php

namespace App\Controller;

use App\Entity\CoachProfile;
use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Enum\RequestStatus;
use App\Repository\CoachRequestRepository;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/messages', name: 'app_messages_')]
final class MessageController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ConversationRepository $conversations): Response
    {
        return $this->render('message/index.html.twig', [
            'conversations' => $conversations->findForParticipant($this->currentUser()),
        ]);
    }

    #[Route('/ouvrir/{id}', name: 'open', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function open(CoachProfile $coach, Request $request, CoachRequestRepository $coachRequests, ConversationRepository $conversations, EntityManagerInterface $entityManager): Response
    {
        $user = $this->currentUser();
        if (!$this->isCsrfTokenValid('open_conversation_'.$coach->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        if ($user === $coach->getUser() || !$this->hasApprovedRelationship($user, $coach, $coachRequests)) {
            throw $this->createAccessDeniedException('La messagerie est réservée aux accompagnements acceptés.');
        }
        $conversation = $conversations->findOneForPair($user, $coach);
        if (!$conversation) {
            $conversation = (new Conversation())->setUser($user)->setCoachProfile($coach)
                ->setTitle('Accompagnement avec '.$coach->getUser()?->getName());
            $entityManager->persist($conversation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_messages_show', ['id' => $conversation->getId()]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(Conversation $conversation, CoachRequestRepository $coachRequests, MessageRepository $messages): Response
    {
        $user = $this->currentUser();
        $this->denyUnlessParticipant($conversation, $user, $coachRequests);
        $messages->markIncomingAsRead($conversation, $user);

        return $this->render('message/show.html.twig', ['conversation' => $conversation, 'viewer' => $user]);
    }

    #[Route('/{id}/envoyer', name: 'send', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function send(Conversation $conversation, Request $request, CoachRequestRepository $coachRequests, EntityManagerInterface $entityManager): Response
    {
        $sender = $this->currentUser();
        $this->denyUnlessParticipant($conversation, $sender, $coachRequests);
        if (!$this->isCsrfTokenValid('send_message_'.$conversation->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $content = trim($request->request->getString('content'));
        if ($content === '' || mb_strlen($content) > 1000) {
            $this->addFlash('error', $content === '' ? 'Le message ne peut pas être vide.' : 'Le message ne peut pas dépasser 1 000 caractères.');
            return $this->redirectToRoute('app_messages_show', ['id' => $conversation->getId()]);
        }

        $message = (new Message())->setConversation($conversation)->setSender($sender)->setContent($content);
        $conversation->setLastMessageAt($message->getSentAt());
        $recipient = $conversation->getUser() === $sender ? $conversation->getCoachProfile()?->getUser() : $conversation->getUser();
        if ($recipient instanceof User) {
            $notification = (new Notification())->setUser($recipient)->setType(NotificationType::new_message)
                ->setTitle('Nouveau message')->setContent($sender->getName().' vous a envoyé un message dans FitProgress.');
            $entityManager->persist($notification);
        }
        $entityManager->persist($message);
        $entityManager->flush();

        return $this->redirectToRoute('app_messages_show', ['id' => $conversation->getId()]);
    }

    private function denyUnlessParticipant(Conversation $conversation, User $user, CoachRequestRepository $requests): void
    {
        $isParticipant = $conversation->getUser() === $user || $conversation->getCoachProfile()?->getUser() === $user;
        if (!$isParticipant || !$this->hasApprovedRelationship($conversation->getUser(), $conversation->getCoachProfile(), $requests)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas consulter cette conversation.');
        }
    }

    private function hasApprovedRelationship(?User $user, ?CoachProfile $coach, CoachRequestRepository $requests): bool
    {
        return $user instanceof User && $coach instanceof CoachProfile && null !== $requests->findOneBy([
            'user' => $user, 'coachProfile' => $coach, 'status' => RequestStatus::Approved,
        ]);
    }

    private function currentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) throw $this->createAccessDeniedException();
        return $user;
    }
}
