<?php

namespace App\Controller;

use App\Entity\JournalEntry;
use App\Entity\User;
use App\Form\JournalEntryType;
use App\Repository\JournalEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/journal')]
class JournalController extends AbstractController
{
    #[Route('', name: 'app_journal_today', methods: ['GET', 'POST'])]
    public function today(
        Request $request,
        JournalEntryRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getCurrentUser();
        $today = new \DateTimeImmutable('today');
        $entry = $repository->findOneForUserAndDate($user, $today);

        if ($entry === null) {
            $entry = (new JournalEntry())->setUser($user)->setDate($today);
        }

        $form = $this->createForm(JournalEntryType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry->recalculateBmi($user->getGoal()?->getHeightCm());
            $entityManager->persist($entry);
            $entityManager->flush();

            $this->addFlash('success', 'Votre journal du jour a été enregistré.');
            return $this->redirectToRoute('app_journal_today');
        }

        return $this->render('journal/index.html.twig', [
            'entry' => $entry,
            'form' => $form,
            'hydrationGoal' => $user->getGoal()?->getHydrationGoalMl() ?? 2000,
            'hasHeight' => $user->getGoal()?->getHeightCm() !== null,
        ]);
    }

    #[Route('/history', name: 'app_journal_history', methods: ['GET'])]
    public function history(
        Request $request,
        JournalEntryRepository $repository,
        PaginatorInterface $paginator,
    ): Response {
        $period = $this->normalizePeriod($request->query->getString('period', '30'));
        $metric = in_array($request->query->getString('metric', 'weight'), ['weight', 'bmi'], true)
            ? $request->query->getString('metric', 'weight')
            : 'weight';
        $since = match ($period) {
            '7' => new \DateTimeImmutable('-6 days'),
            '30' => new \DateTimeImmutable('-29 days'),
            '90' => new \DateTimeImmutable('-3 months'),
            default => null,
        };

        $user = $this->getCurrentUser();
        $filteredEntries = $repository->createHistoryQueryBuilder($user, $since)->getQuery()->getResult();
        $measurementEntries = array_values(array_filter(
            $filteredEntries,
            static fn (JournalEntry $entry): bool => $metric === 'bmi' ? $entry->getBmi() !== null : $entry->getWeight() !== null,
        ));
        $measurements = array_map(
            static fn (JournalEntry $entry): float => (float) ($metric === 'bmi' ? $entry->getBmi() : $entry->getWeight()),
            $measurementEntries,
        );
        $latestMeasurement = $measurements[0] ?? null;
        $oldestMeasurement = $measurements !== [] ? $measurements[array_key_last($measurements)] : null;
        $variation = $latestMeasurement !== null && $oldestMeasurement !== null ? $latestMeasurement - $oldestMeasurement : null;
        $chartEntries = array_reverse(array_slice($measurementEntries, 0, 7));
        $chartMeasurements = array_map(
            static fn (JournalEntry $entry): float => (float) ($metric === 'bmi' ? $entry->getBmi() : $entry->getWeight()),
            $chartEntries,
        );
        $chartMin = $chartMeasurements !== [] ? min($chartMeasurements) : 0.0;
        $chartRange = max(0.1, ($chartMeasurements !== [] ? max($chartMeasurements) : 0.0) - $chartMin);

        $entries = $paginator->paginate(
            $repository->createHistoryQueryBuilder($user, $since),
            max(1, $request->query->getInt('page', 1)),
            $period === 'all' ? max(1, count($filteredEntries)) : 10,
        );

        return $this->render('journal/history.html.twig', [
            'entries' => $entries,
            'period' => $period,
            'metric' => $metric,
            'today' => new \DateTimeImmutable('today'),
            'recentEntries' => array_slice($filteredEntries, 0, 5),
            'chartEntries' => $chartEntries,
            'chartMin' => $chartMin,
            'chartRange' => $chartRange,
            'stats' => [
                'min' => $measurements !== [] ? min($measurements) : null,
                'max' => $measurements !== [] ? max($measurements) : null,
                'average' => $measurements !== [] ? array_sum($measurements) / count($measurements) : null,
                'variation' => $variation,
            ],
        ]);
    }

    #[Route('/{id}', name: 'app_journal_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(int $id, JournalEntryRepository $repository): Response
    {
        $entry = $repository->findOneForUser($this->getCurrentUser(), $id);
        if (!$entry) throw $this->createNotFoundException('Entrée du journal introuvable.');
        return $this->render('journal/show.html.twig', ['entry' => $entry, 'coachView' => false]);
    }

    #[Route('/{id}/edit', name: 'app_journal_edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        Request $request,
        JournalEntryRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getCurrentUser();
        $entry = $repository->findOneForUser($user, $id);

        if ($entry === null) {
            throw $this->createNotFoundException('Entrée du journal introuvable.');
        }

        if ($entry->getDate()?->format('Y-m-d') !== (new \DateTimeImmutable('today'))->format('Y-m-d')) {
            $this->addFlash('error', 'Cette entrée est en lecture seule : seules les données du jour peuvent être modifiées.');

            return $this->redirectToRoute('app_journal_history');
        }

        $form = $this->createForm(JournalEntryType::class, $entry);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entry->recalculateBmi($user->getGoal()?->getHeightCm());
            $entityManager->flush();

            $this->addFlash('success', 'L’entrée du journal a été modifiée.');
            return $this->redirectToRoute('app_journal_history');
        }

        return $this->render('journal/index.html.twig', [
            'entry' => $entry,
            'form' => $form,
            'hydrationGoal' => $user->getGoal()?->getHydrationGoalMl() ?? 2000,
            'hasHeight' => $user->getGoal()?->getHeightCm() !== null,
            'editing' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_journal_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        JournalEntryRepository $repository,
        EntityManagerInterface $entityManager,
    ): Response {
        $entry = $repository->findOneForUser($this->getCurrentUser(), $id);

        if ($entry === null) {
            throw $this->createNotFoundException('Entrée du journal introuvable.');
        }

        if (!$this->isCsrfTokenValid('delete-journal-'.$entry->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton de suppression invalide.');
        }

        $entityManager->remove($entry);
        $entityManager->flush();
        $this->addFlash('success', 'L’entrée du journal a été supprimée.');

        return $this->redirectToRoute('app_journal_history');
    }

    #[Route('/export.csv', name: 'app_journal_export', methods: ['GET'])]
    public function export(JournalEntryRepository $repository): StreamedResponse
    {
        $user = $this->getCurrentUser();
        if (in_array('ROLE_COACH', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            throw $this->createAccessDeniedException('L’export du journal est réservé aux clients.');
        }

        $entries = $repository->findAllForUser($user);

        $response = new StreamedResponse(static function () use ($entries): void {
            $output = fopen('php://output', 'wb');
            if ($output === false) {
                return;
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Date', 'Poids (kg)', 'IMC', 'Hydratation (ml)', 'Activité (min)', 'Sommeil (h)', 'Qualité sommeil', 'Humeur', 'Énergie', 'Calories', 'Repas', 'Réflexions'], ';', '"', '', "\n");

            foreach ($entries as $entry) {
                $meals = $entry->getMeals()->map(
                    static fn ($meal): string => sprintf('%s : %s (%d kcal)', $meal->getType()->label(), $meal->getTitle(), $meal->getCalories() ?? 0)
                )->toArray();

                fputcsv($output, [
                    $entry->getDate()?->format('d/m/Y'),
                    $entry->getWeight(),
                    $entry->getBmi(),
                    $entry->getWaterIntakeMl(),
                    $entry->getActivityMinutes(),
                    $entry->getSleepHours(),
                    $entry->getSleepQuality(),
                    $entry->getMood()?->label(),
                    $entry->getEnergyLevel()?->label(),
                    $entry->getTotalCalories(),
                    implode(' | ', $meals),
                    $entry->getNotes(),
                ], ';', '"', '', "\n");
            }

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="journal-fitprogress.csv"');

        return $response;
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, ['7', '30', '90', 'all'], true) ? $period : '30';
    }
}
