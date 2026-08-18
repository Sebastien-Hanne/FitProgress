<?php

namespace App\Controller;

use App\Entity\CoachProfile;
use App\Entity\CoachRequest;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Enum\RequestStatus;
use App\Repository\CoachProfileRepository;
use App\Repository\CoachRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/choisir-coach', name: 'app_coach_')]
final class CoachMatchingController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request, CoachProfileRepository $coaches, CoachRequestRepository $requests): Response
    {
        $user = $this->requireUser();
        $search = trim((string) $request->query->get('q'));
        $specialty = trim((string) $request->query->get('specialty'));

        return $this->render('coach/index.html.twig', [
            'coaches' => $coaches->findAvailable($search, $specialty),
            'specialties' => $coaches->findAvailableSpecialties(),
            'search' => $search,
            'selectedSpecialty' => $specialty,
            'currentRequest' => $requests->findCurrentForUser($user),
        ]);
    }

    #[Route('/ma-demande', name: 'status', methods: ['GET'])]
    public function status(CoachRequestRepository $requests): Response
    {
        return $this->render('coach/status.html.twig', [
            'currentRequest' => $requests->findCurrentForUser($this->requireUser()),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'], methods: ['GET'])]
    public function show(CoachProfile $coach, CoachRequestRepository $requests): Response
    {
        if (!$coach->isAvailable() || $coach->getUser()?->isDeleted() !== false) {
            throw $this->createNotFoundException('Ce coach n’est pas disponible.');
        }

        return $this->render('coach/show.html.twig', [
            'coach' => $coach,
            'currentRequest' => $requests->findCurrentForUser($this->requireUser()),
        ]);
    }

    #[Route('/{id}/demande', name: 'request', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function sendRequest(CoachProfile $coach, Request $request, CoachRequestRepository $requests, EntityManagerInterface $entityManager): Response
    {
        $user = $this->requireUser();
        if (!$this->isCsrfTokenValid('coach_request_'.$coach->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }
        if (!$coach->isAvailable() || $coach->getUser()?->isDeleted() !== false) {
            $this->addFlash('error', 'Ce coach n’est plus disponible.');
            return $this->redirectToRoute('app_coach_index');
        }

        $current = $requests->findCurrentForUser($user);
        if ($current?->getCoachProfile() === $coach) {
            $this->addFlash('info', 'Une demande ou un accompagnement existe déjà avec ce coach.');
            return $this->redirectToRoute('app_coach_status');
        }
        if ($current instanceof CoachRequest) {
            $current->setStatus(RequestStatus::Cancelled);
        }

        $subjects = [
            'weight_loss' => 'Perte de poids',
            'muscle_gain' => 'Prise de masse',
            'fitness' => 'Remise en forme',
            'other' => 'Autre',
        ];
        $subject = $subjects[(string) $request->request->get('subject')] ?? 'Autre';
        $details = trim((string) $request->request->get('message'));
        $message = sprintf('[%s]%s', $subject, $details !== '' ? "\n".$details : '');
        $coachRequest = (new CoachRequest())->setUser($user)->setCoachProfile($coach)
            ->setMessage(mb_substr($message, 0, 2000));
        $entityManager->persist($coachRequest);
        $coachUser = $coach->getUser();
        if ($coachUser instanceof User) {
            $notification = (new Notification())->setUser($coachUser)->setType(NotificationType::new_coach_request)
                ->setTitle('Nouvelle demande de coaching')->setContent($user->getName().' souhaite être accompagné par vous.');
            $entityManager->persist($notification);
        }
        $entityManager->flush();
        $this->addFlash('success', $current ? 'Votre changement de coach a bien été demandé.' : 'Votre demande de coaching a bien été envoyée.');

        return $this->redirectToRoute('app_coach_status');
    }

    #[Route('/demande/{id}/annuler', name: 'cancel', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function cancel(CoachRequest $coachRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($coachRequest->getUser() !== $this->requireUser()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('cancel_coach_request_'.$coachRequest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }
        if ($coachRequest->getStatus() !== RequestStatus::Pending) {
            $this->addFlash('error', 'Seule une demande en attente peut être annulée.');
        } else {
            $coachRequest->setStatus(RequestStatus::Cancelled);
            $entityManager->flush();
            $this->addFlash('success', 'Votre demande a été annulée.');
        }

        return $this->redirectToRoute('app_coach_status');
    }

    #[Route('/demande/{id}/arreter', name: 'stop', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function stop(CoachRequest $coachRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($coachRequest->getUser() !== $this->requireUser()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('stop_coaching_'.$coachRequest->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }
        if ($coachRequest->getStatus() !== RequestStatus::Approved) {
            $this->addFlash('error', 'Cet accompagnement n’est pas actif.');
        } else {
            $coachRequest->setStatus(RequestStatus::Cancelled);
            $entityManager->flush();
            $this->addFlash('success', 'Votre accompagnement avec ce coach est terminé.');
        }

        return $this->redirectToRoute('app_coach_status');
    }

    private function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $user;
    }
}
