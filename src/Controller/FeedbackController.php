<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FeedbackRepository;
use App\Repository\JournalEntryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'app_feedback', methods: ['GET'])]
    public function index(FeedbackRepository $feedbacks, JournalEntryRepository $journalEntries): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $goal = $user->getGoal();
        $latestEntry = $journalEntries->createHistoryQueryBuilder($user)
            ->andWhere('entry.weight IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        $initial = (float) ($goal?->getInitialWeight() ?? 0);
        $target = (float) ($goal?->getTargetWeight() ?? 0);
        $current = (float) ($latestEntry?->getWeight() ?? $initial);
        $distance = abs($initial - $target);
        $progress = $distance > 0 ? (int) round(min(100, max(0, abs($initial - $current) / $distance * 100))) : 0;

        return $this->render('feedback/index.html.twig', [
            'feedbacks' => $feedbacks->findBy(['user' => $user], ['createdAt' => 'DESC']),
            'targetWeight' => $goal?->getTargetWeight(),
            'goalProgress' => $progress,
        ]);
    }
}
