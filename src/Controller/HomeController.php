<?php

namespace App\Controller;

use App\Entity\JournalEntry;
use App\Entity\User;
use App\Repository\FeedbackRepository;
use App\Repository\JournalEntryRepository;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/dashboard', name: 'dashboard', methods: ['GET'])]
    public function dashboard(
        JournalEntryRepository $journalEntries,
        FeedbackRepository $feedbacks,
        NotificationRepository $notifications,
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
        $latestFeedback = $feedbacks->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
        $latestNotification = $notifications->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
        $coachName = $latestFeedback?->getCoachProfile()?->getUser()?->getName();

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
                'showWeightReminder' => $daysSinceWeighIn === null || $daysSinceWeighIn >= 5,
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
    public function coachDashboard(): Response
    {
        return new Response('Tableau de bord coach en cours de développement.');
    }

    #[Route('/admin/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function adminDashboard(): Response
    {
        return new Response('Tableau de bord administrateur en cours de développement.');
    }
}
