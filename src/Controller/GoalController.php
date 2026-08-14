<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GoalHistory;
use App\Entity\User;
use App\Form\GoalType;
use App\Repository\JournalEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoalController extends AbstractController
{
    #[Route('/cible', name: 'app_goal', methods: ['GET', 'POST'])]
    public function index(Request $request, JournalEntryRepository $entries, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $goal = $user->getGoal();
        if (!$goal instanceof Goal) {
            return $this->redirectToRoute('app_onboarding');
        }

        $previousWeight = $goal->getTargetWeight();
        $previousDate = $goal->getTargetDate();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hasChanged = $previousWeight !== $goal->getTargetWeight() || $previousDate != $goal->getTargetDate();
            if ($hasChanged) {
                $entityManager->persist(new GoalHistory($user, $previousWeight, $previousDate));
            }
            $entityManager->flush();
            $this->addFlash('success', 'Votre objectif a bien été mis à jour.');

            return $this->redirectToRoute('app_goal');
        }

        $journalEntries = $entries->findAllForUser($user);
        $weightedEntries = array_filter($journalEntries, static fn ($entry): bool => $entry->getWeight() !== null);
        $latestEntry = current($weightedEntries) ?: null;
        $currentWeight = $latestEntry?->getWeight() ?? $goal->getInitialWeight();
        $initial = (float) $goal->getInitialWeight();
        $target = (float) $goal->getTargetWeight();
        $current = (float) $currentWeight;
        $distance = abs($initial - $target);
        $progress = $distance > 0 ? (int) round(min(100, max(0, abs($initial - $current) / $distance * 100))) : 100;
        $daysRemaining = $goal->getTargetDate() ? max(0, (int) (new \DateTimeImmutable('today'))->diff($goal->getTargetDate())->format('%r%a')) : null;
        $history = $entityManager->getRepository(GoalHistory::class)->findBy(['user' => $user], ['archivedAt' => 'DESC'], 3);

        return $this->render('goal/index.html.twig', [
            'goal' => $goal,
            'goalForm' => $form,
            'progress' => $progress,
            'currentWeight' => $current,
            'remainingWeight' => abs($current - $target),
            'daysRemaining' => $daysRemaining,
            'history' => $history,
        ]);
    }
}
