<?php

namespace App\Controller;

use App\Entity\CoachProfile;
use App\Entity\User;
use App\Form\CoachRegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class CoachRegistrationController extends AbstractController
{
    #[Route('/register/coach', name: 'app_register_coach')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ): Response {

        $user = new User();
        $form = $this->createForm(CoachRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // 1. Attribution du rôle Coach
            $user->setRoles(['ROLE_COACH']);

            // 2. Hachage du mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // 3. Génération du proxy email
            $user->setProxyEmail(
                'coach_' . bin2hex(random_bytes(8)) . '@fitprogress.local'
            );

            // 4. Création du profil visible dans l'annuaire des coachs
            $specialties = array_values(array_filter(array_map(
                'trim',
                (array) $request->request->all('specialities')
            )));
            $coachProfile = (new CoachProfile())
                ->setUser($user)
                ->setSpecialties($specialties !== [] ? implode(', ', $specialties) : 'Coaching sportif')
                ->setBio('Coach FitProgress disponible pour vous accompagner vers vos objectifs.')
                ->setIsAvailable(true);
            $user->setCoachProfile($coachProfile);

            // 5. Sauvegarde en BDD
            $entityManager->persist($user);
            $entityManager->persist($coachProfile);
            $entityManager->flush();

            $logger->info('Nouveau coach inscrit : ' . $user->getEmail());

            $this->addFlash('success', 'Votre compte coach a été créé avec succès !');
            return $this->redirectToRoute('app_login');
        }

        // Code 422 si formulaire soumis mais invalide
        $statusCode = ($form->isSubmitted() && !$form->isValid())
            ? Response::HTTP_UNPROCESSABLE_ENTITY
            : Response::HTTP_OK;

        return $this->render('registration/coach_register.html.twig', [
            'registrationForm' => $form->createView(),
        ], new Response(null, $statusCode));
    }
}
