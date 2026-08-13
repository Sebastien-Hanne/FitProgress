<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\User;
use App\Form\OnboardingGoalType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OnboardingController extends AbstractController
{
    #[Route('/bienvenue/questionnaire', name: 'app_onboarding', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if ($user->getGoal() !== null) {
            return $this->redirectToRoute('dashboard');
        }

        $goal = (new Goal())->setUser($user);
        $form = $this->createForm(OnboardingGoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setGoal($goal);
            $entityManager->persist($goal);
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil de suivi est prêt.');

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('onboarding/questionnaire.html.twig', [
            'onboardingForm' => $form,
        ], new Response(null, $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK));
    }
}
