<?php

namespace App\Controller;

use App\Entity\Feedback;
use App\Entity\Notification;
use App\Entity\Session;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Enum\RequestStatus;
use App\Enum\SessionStatus;
use App\Form\CoachFeedbackType;
use App\Form\CoachProfileType;
use App\Form\CoachSessionType;
use App\Repository\CoachRequestRepository;
use App\Repository\FeedbackRepository;
use App\Repository\JournalEntryRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/coach', name: 'app_coach_space_')]
final class CoachSpaceController extends AbstractController
{
    #[Route('/profil', name: 'profile', methods: ['GET', 'POST'])]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        $profile = $this->coach()->getCoachProfile();
        $form = $this->createForm(CoachProfileType::class, $profile)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil coach a été mis à jour.');
            return $this->redirectToRoute('app_coach_space_profile');
        }
        $activeCount = $entityManager->getRepository(\App\Entity\CoachRequest::class)->count(['coachProfile' => $profile, 'status' => RequestStatus::Approved]);
        return $this->render('coach_space/profile.html.twig', ['profile' => $profile, 'profileForm' => $form, 'activeCount' => $activeCount]);
    }

    #[Route('/clients', name: 'clients', methods: ['GET'])]
    public function clients(Request $request, CoachRequestRepository $requests, FeedbackRepository $feedbacks): Response
    {
        $profile = $this->coach()->getCoachProfile();
        $search = trim($request->query->getString('q'));
        $filter = $request->query->getString('filter') === 'history' ? 'history' : 'active';
        $clientRequests = $requests->findApprovedForCoach($profile, $search);

        return $this->render('coach_space/clients.html.twig', [
            'clientRequests' => $clientRequests,
            'displayedClientRequests' => $filter === 'history'
                ? array_values(array_filter($clientRequests, static fn ($item): bool => $item->getUser()->getJournalEntries()->count() > 1))
                : $clientRequests,
            'search' => $search,
            'filter' => $filter,
            'pendingCount' => $requests->count(['coachProfile' => $profile, 'status' => RequestStatus::Pending]),
            'feedbackCount' => $feedbacks->count(['coachProfile' => $profile]),
        ]);
    }

    #[Route('/clients/{id}', name: 'client_show', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function client(#[MapEntity(id: 'id')] User $client, Request $request, JournalEntryRepository $entries, FeedbackRepository $feedbacks, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('VIEW_USER_FITNESS_DATA', $client);
        $coach = $this->coach()->getCoachProfile();
        $feedback = (new Feedback())->setUser($client)->setCoachProfile($coach);
        $form = $this->createForm(CoachFeedbackType::class, $feedback)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $notification = (new Notification())->setUser($client)->setType(NotificationType::new_feedback)
                ->setTitle('Nouveau commentaire de suivi')->setContent($coach->getUser()->getName().' a ajouté un commentaire à votre suivi.');
            $entityManager->persist($feedback); $entityManager->persist($notification); $entityManager->flush();
            $this->addFlash('success', 'Le commentaire de suivi a été ajouté.');
            return $this->redirectToRoute('app_coach_space_client_show', ['id' => $client->getId()]);
        }
        return $this->render('coach_space/client_show.html.twig', [
            'client' => $client, 'entries' => $entries->findAllForUser($client),
            'feedbacks' => $feedbacks->findBy(['user' => $client, 'coachProfile' => $coach], ['createdAt' => 'DESC']), 'feedbackForm' => $form,
        ]);
    }

    #[Route('/clients/{clientId}/journal/{entryId}', name: 'client_journal_show', requirements: ['clientId' => '\d+', 'entryId' => '\d+'], methods: ['GET'])]
    public function clientJournal(int $clientId, int $entryId, JournalEntryRepository $entries, EntityManagerInterface $entityManager): Response
    {
        $client = $entityManager->find(User::class, $clientId);
        if (!$client instanceof User) throw $this->createNotFoundException();
        $this->denyAccessUnlessGranted('VIEW_USER_FITNESS_DATA', $client);
        $entry = $entries->findOneForUser($client, $entryId);
        if (!$entry) throw $this->createNotFoundException('Entrée du journal introuvable.');
        return $this->render('journal/show.html.twig', ['entry' => $entry, 'coachView' => true, 'client' => $client]);
    }

    #[Route('/feedback', name: 'feedback', methods: ['GET'])]
    public function feedback(CoachRequestRepository $requests): Response
    {
        return $this->render('coach_space/feedback.html.twig', ['clientRequests' => $requests->findApprovedForCoach($this->coach()->getCoachProfile())]);
    }

    #[Route('/schedule', name: 'schedule', methods: ['GET'])]
    public function schedule(SessionRepository $sessions): Response
    {
        return $this->render('coach_space/schedule.html.twig', [
            'sessions' => $sessions->findForCoach($this->coach()->getCoachProfile()),
        ]);
    }

    #[Route('/schedule/new', name: 'schedule_new', methods: ['GET', 'POST'])]
    public function scheduleNew(Request $request, CoachRequestRepository $requests, EntityManagerInterface $entityManager): Response
    {
        $coach = $this->coach()->getCoachProfile();
        $clients = array_map(static fn ($coachRequest): User => $coachRequest->getUser(), $requests->findApprovedForCoach($coach));
        $session = (new Session())
            ->setCoachProfile($coach)
            ->setTitle('Séance de coaching')
            ->setStartAt(new \DateTimeImmutable('tomorrow 09:00'));
        $form = $this->createForm(CoachSessionType::class, $session, ['clients' => $clients])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($session);
            $entityManager->flush();
            $this->addFlash('success', 'La séance a été planifiée.');

            return $this->redirectToRoute('app_coach_space_schedule');
        }

        return $this->render('coach_space/session_form.html.twig', [
            'sessionForm' => $form,
            'pageTitle' => 'Planifier une séance',
            'submitLabel' => 'Planifier la séance',
        ]);
    }

    #[Route('/schedule/{id}/edit', name: 'schedule_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function scheduleEdit(Session $session, Request $request, CoachRequestRepository $requests, EntityManagerInterface $entityManager): Response
    {
        $coach = $this->coach()->getCoachProfile();
        $this->assertSessionOwner($session);
        if ($session->getStatus() !== SessionStatus::Scheduled) {
            throw $this->createAccessDeniedException('Seule une séance planifiée peut être modifiée.');
        }
        $clients = array_map(static fn ($coachRequest): User => $coachRequest->getUser(), $requests->findApprovedForCoach($coach));
        $form = $this->createForm(CoachSessionType::class, $session, ['clients' => $clients])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La séance a été modifiée.');

            return $this->redirectToRoute('app_coach_space_schedule');
        }

        return $this->render('coach_space/session_form.html.twig', [
            'sessionForm' => $form,
            'pageTitle' => 'Modifier la séance',
            'submitLabel' => 'Enregistrer les modifications',
        ]);
    }

    #[Route('/schedule/{id}/cancel', name: 'schedule_cancel', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function scheduleCancel(Session $session, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->changeSessionStatus($session, SessionStatus::Cancelled, $request, $entityManager, 'cancel');
        $this->addFlash('success', 'La séance a été annulée.');

        return $this->redirectToRoute('app_coach_space_schedule');
    }

    #[Route('/schedule/{id}/complete', name: 'schedule_complete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function scheduleComplete(Session $session, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->changeSessionStatus($session, SessionStatus::Completed, $request, $entityManager, 'complete');
        $this->addFlash('success', 'La séance est marquée comme terminée.');

        return $this->redirectToRoute('app_coach_space_schedule');
    }

    private function changeSessionStatus(Session $session, SessionStatus $status, Request $request, EntityManagerInterface $entityManager, string $action): void
    {
        $this->assertSessionOwner($session);
        if ($session->getStatus() !== SessionStatus::Scheduled
            || !$this->isCsrfTokenValid($action.'_session_'.$session->getId(), $request->request->getString('_token'))
        ) {
            throw $this->createAccessDeniedException();
        }
        $session->setStatus($status);
        $entityManager->flush();
    }

    private function assertSessionOwner(Session $session): void
    {
        if ($session->getCoachProfile() !== $this->coach()->getCoachProfile()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function coach(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$user->getCoachProfile()) throw $this->createAccessDeniedException();
        return $user;
    }
}
