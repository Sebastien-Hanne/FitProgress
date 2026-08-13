<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        LoggerInterface $logger,
        Security $security,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setRoles(['ROLE_USER']);
            $user->setProxyEmail('user_' . bin2hex(random_bytes(16)) . '@fitprogress.local');

            $entityManager->persist($user);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from('noreply@fitprogress.local')
                ->to($user->getEmail())
                ->subject('Bienvenue sur FitProgress !')
                ->htmlTemplate('emails/welcome.html.twig')
                ->context([
                    'user' => $user,
                ]);

            try {
                $mailer->send($email);
            } catch (\Throwable $e) {
                $logger->error('Impossible d’envoyer l’email de bienvenue FitProgress', [
                    'user_id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'exception' => $e->getMessage()
                ]);
            }

            $security->login($user, AppAuthenticator::class, 'main');
            $this->addFlash('success', 'Votre compte est créé. Répondez à ces quatre questions pour personnaliser votre suivi.');

            return $this->redirectToRoute('app_onboarding');
        }

        // Si le formulaire est soumis mais INVALIDE, on renvoie un statut HTTP 422 pour Turbo/UX
        $response = new Response(
            null,
            ($form->isSubmitted() && !$form->isValid()) 
                ? Response::HTTP_UNPROCESSABLE_ENTITY 
                : Response::HTTP_OK
        );

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView()
        ], $response);
    }
}
