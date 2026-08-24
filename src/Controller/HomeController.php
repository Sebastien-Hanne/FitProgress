<?php

namespace App\Controller;

use App\Entity\JournalEntry;
use App\Entity\Notification;
use App\Entity\User;
use App\Enum\NotificationType;
use App\Repository\FeedbackRepository;
use App\Repository\JournalEntryRepository;
use App\Repository\NotificationRepository;
use App\Repository\CoachRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        Request $request,
        JournalEntryRepository $journalEntries,
        FeedbackRepository $feedbacks,
        NotificationRepository $notifications,
        CoachRequestRepository $coachRequests,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $goal = $user->getGoal();
        $entries = $journalEntries->findAllForUser($user);
        $weighIns = array_values(array_filter(
            $entries,
            static fn (JournalEntry $entry): bool => $entry->getWeight() !== null,
        ));
        $latest = $weighIns[0] ?? null;
        $previous = $weighIns[1] ?? null;
        $currentWeight = $latest?->getWeight() ?? $goal?->getInitialWeight();
        $variation = $latest && $previous ? (float) $latest->getWeight() - (float) $previous->getWeight() : 0.0;
        $bmi = $latest?->getBmi();
        if ($bmi === null && $currentWeight !== null && ($goal?->getHeightCm() ?? 0) > 0) {
            $height = $goal->getHeightCm() / 100;
            $bmi = number_format((float) $currentWeight / ($height ** 2), 2, '.', '');
        }

        $progress = $this->calculateProgress($goal?->getInitialWeight(), $goal?->getTargetWeight(), $currentWeight);
        $chartEntries = array_reverse(array_slice($weighIns, 0, 30));
        $daysSinceWeighIn = $latest?->getDate()
            ? max(0, (int) $latest->getDate()->diff(new \DateTimeImmutable('today'))->format('%r%a'))
            : null;
        $today = new \DateTimeImmutable('today');
        $hasJournalToday = $journalEntries->findOneForUserAndDate($user, $today) !== null;
        $reminderCreated = false;

        if (($daysSinceWeighIn === null || $daysSinceWeighIn >= 4)
            && !$notifications->hasTypeSince($user, NotificationType::weigh_in_reminder, $today)
        ) {
            $content = $daysSinceWeighIn === null
                ? 'Ajoutez votre première pesée pour commencer à suivre votre progression.'
                : sprintf('Votre dernière pesée date de %d jours. Pensez à mettre votre poids à jour.', $daysSinceWeighIn);
            $entityManager->persist((new Notification())
                ->setUser($user)
                ->setType(NotificationType::weigh_in_reminder)
                ->setTitle('Pensez à vous peser')
                ->setContent($content));
            $reminderCreated = true;
        }

        if (!$hasJournalToday && !$notifications->hasTypeSince($user, NotificationType::journal_reminder, $today)) {
            $entityManager->persist((new Notification())
                ->setUser($user)
                ->setType(NotificationType::journal_reminder)
                ->setTitle('Votre journal vous attend')
                ->setContent('Vous n’avez pas encore rempli votre journal aujourd’hui. Prenez quelques minutes pour noter votre journée.'));
            $reminderCreated = true;
        }

        if ($reminderCreated) {
            $entityManager->flush();
        }
        $currentCoachRequest = $coachRequests->findCurrentForUser($user);
        $activeCoachProfile = $currentCoachRequest?->getStatus() === \App\Enum\RequestStatus::Approved
            ? $currentCoachRequest->getCoachProfile()
            : null;
        $latestFeedback = $activeCoachProfile
            ? $feedbacks->findOneBy(['user' => $user, 'coachProfile' => $activeCoachProfile], ['createdAt' => 'DESC'])
            : null;
        $latestNotification = $notifications->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
        $coachName = $latestFeedback?->getCoachProfile()?->getUser()?->getName();

        $weightReminderDue = $daysSinceWeighIn === null || $daysSinceWeighIn >= 4;
        if (!$weightReminderDue) {
            $request->getSession()->remove('weight_reminder_dismissed');
        }

        return $this->render('dashboard/index.html.twig', [
            'dashboard' => [
                'currentWeight' => $currentWeight,
                'weightVariation' => $variation,
                'targetWeight' => $goal?->getTargetWeight(),
                'goalProgress' => $progress,
                'bmi' => $bmi,
                'bmiStatus' => $this->getBmiStatus($bmi),
                'bmiColor' => $this->getBmiColor($bmi),
                'bmiBadgeColor' => $this->getBmiBadgeColor($bmi),
                'daysSinceWeighIn' => $daysSinceWeighIn,
                'showWeightReminder' => $weightReminderDue && !$request->getSession()->get('weight_reminder_dismissed', false),
                'weightHistory' => [
                    'labels' => array_map(static fn (JournalEntry $entry): string => $entry->getDate()?->format('d/m') ?? '', $chartEntries),
                    'values' => array_map(static fn (JournalEntry $entry): float => (float) $entry->getWeight(), $chartEntries),
                ],
                'coachFeedback' => $latestFeedback?->getContent(),
                'coachName' => $coachName,
                'latestNotification' => $latestNotification?->getContent(),
            ],
        ]);
    }

    #[Route('/dashboard/dismiss-weight-reminder', name: 'app_dashboard_dismiss_weight_reminder', methods: ['POST'])]
    public function dismissWeightReminder(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('dismiss-weight-reminder', $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $request->getSession()->set('weight_reminder_dismissed', true);

        return $this->json(['dismissed' => true]);
    }

    private function calculateProgress(?string $initial, ?string $target, ?string $current): int
    {
        if ($initial === null || $target === null || $current === null) {
            return 0;
        }

        $distance = (float) $target - (float) $initial;
        if ($distance === 0.0) {
            return 100;
        }

        return (int) round(min(100, max(0, ((float) $current - (float) $initial) / $distance * 100)));
    }

    private function getBmiStatus(?string $bmi): string
    {
        if ($bmi === null) return 'Non renseigné';
        $value = (float) $bmi;
        if ($value < 18.5) return 'Insuffisance pondérale';
        if ($value < 25) return 'Corpulence normale';
        if ($value < 30) return 'Surpoids';

        return 'Obésité';
    }

    private function getBmiColor(?string $bmi): string
    {
        if ($bmi === null) return '#94a3b8';
        $value = (float) $bmi;
        if ($value < 18.5) return '#f59e0b';
        if ($value < 25) return '#059669';
        if ($value < 30) return '#f59e0b';

        return '#e11d48';
    }

    private function getBmiBadgeColor(?string $bmi): string
    {
        return $this->getBmiColor($bmi);
    }

    #[Route('/coach/dashboard', name: 'coach_dashboard', methods: ['GET'])]
    public function coachDashboard(CoachRequestRepository $requests, FeedbackRepository $feedbacks): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) throw $this->createAccessDeniedException();
        $profile = $user->getCoachProfile();
        return $this->render('coach_space/clients.html.twig', [
            'clientRequests' => $profile ? $requests->findApprovedForCoach($profile) : [], 'search' => '',
            'pendingCount' => $profile ? $requests->count(['coachProfile' => $profile, 'status' => \App\Enum\RequestStatus::Pending]) : 0,
            'feedbackCount' => $profile ? $feedbacks->count(['coachProfile' => $profile]) : 0,
        ]);
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): Response
    {
        return new Response('Tableau de bord administrateur en cours de développement.');
    }
}
