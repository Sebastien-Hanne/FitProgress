<?php

namespace App\Controller;

use App\Entity\JournalEntry;
use App\Entity\User;
use App\Entity\Goal;
use App\Form\ProfileAccountType;
use App\Form\ChangePasswordFormType;
use App\Form\ChangeEmailFormType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use App\Repository\JournalEntryRepository;
use App\Service\ProfilePhotoUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function index(JournalEntryRepository $journalEntryRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if (in_array('ROLE_COACH', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return $this->redirectToRoute('app_coach_space_profile');
        }

        $entries = $journalEntryRepository->findAllForUser($user);
        $weighIns = array_values(array_filter(
            $entries,
            static fn (JournalEntry $entry): bool => $entry->getWeight() !== null,
        ));
        $latestEntry = $weighIns[0] ?? null;
        $goal = $user->getGoal();
        $currentWeight = $latestEntry?->getWeight();
        $currentBmi = $latestEntry?->getBmi();

        if ($currentBmi === null && $currentWeight !== null && ($goal?->getHeightCm() ?? 0) > 0) {
            $heightMeters = $goal->getHeightCm() / 100;
            $currentBmi = number_format((float) $currentWeight / ($heightMeters ** 2), 2, '.', '');
        }

        $progress = $this->calculateProgress(
            $goal?->getInitialWeight(),
            $goal?->getTargetWeight(),
            $currentWeight,
        );

        $chartEntries = array_reverse(array_slice($weighIns, 0, 6));

        return $this->render('profile/index.html.twig', [
            'profile' => [
                'memberSince' => $this->formatMemberSince($user->getCreatedAt()),
                'currentWeight' => $currentWeight,
                'bmi' => $currentBmi,
                'progress' => $progress,
                'weighInsCount' => count($weighIns),
                'trackedDays' => count($entries),
                'chartLabels' => array_map(
                    static fn (JournalEntry $entry): string => $entry->getDate()?->format('d/m') ?? '',
                    $chartEntries,
                ),
                'chartValues' => array_map(
                    static fn (JournalEntry $entry): float => (float) $entry->getWeight(),
                    $chartEntries,
                ),
            ],
        ]);
    }

    #[Route('/profile/account', name: 'app_profile_account', methods: ['GET', 'POST'])]
    public function account(Request $request, JournalEntryRepository $journalEntryRepository, EntityManagerInterface $entityManager, ProfilePhotoUploader $photoUploader): Response
    {
        $user = $this->getCurrentUser();
        $goal = $user->getGoal();
        $isCoach = in_array('ROLE_COACH', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true);
        $form = $this->createForm(ProfileAccountType::class, $user, [
            'method' => 'POST',
            'show_health_fields' => !$isCoach,
        ]);
        if (!$isCoach) {
            $form->get('heightCm')->setData($goal?->getHeightCm());
            $form->get('targetWeight')->setData($goal?->getTargetWeight());
            $form->get('birthDate')->setData($goal?->getBirthDate());
            $form->get('gender')->setData($goal?->getGender());
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$isCoach && $goal === null) {
                $goal = (new Goal())->setUser($user);
                $user->setGoal($goal);
                $entityManager->persist($goal);
            }

            if (!$isCoach) {
                $goal->setHeightCm((int) $form->get('heightCm')->getData())->setTargetWeight((string) $form->get('targetWeight')->getData())
                    ->setBirthDate($form->get('birthDate')->getData())->setGender($form->get('gender')->getData());
            }
            if ($photo = $form->get('photoFile')->getData()) {
                $oldPhoto = $user->getPhoto();
                $user->setPhoto($photoUploader->upload($photo));
                $photoUploader->delete($oldPhoto);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour.');

            return $this->redirectToRoute('app_profile_account');
        }

        $latestEntry = $journalEntryRepository->createHistoryQueryBuilder($user)
            ->andWhere('entry.weight IS NOT NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render('profile/account.html.twig', [
            'currentWeight' => $latestEntry?->getWeight(),
            'memberSince' => $this->formatMemberSince($user->getCreatedAt()),
            'accountForm' => $form,
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ProfilePhotoUploader $photoUploader, Security $security): Response
    {
        $user = $this->getCurrentUser();
        if (!$this->isCsrfTokenValid('delete-account', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        if (!$passwordHasher->isPasswordValid($user, $request->request->getString('password'))) {
            $this->addFlash('delete_error', 'Le mot de passe est incorrect. Le compte n’a pas été supprimé.');

            return $this->redirectToRoute('app_profile_privacy');
        }

        $photo = $user->getPhoto();
        // Clear the security token and remember-me cookie while the user still
        // has its Doctrine identifier. Otherwise the deleted user is written
        // back into the session at the end of the request.
        $logoutResponse = $security->logout(false);
        $entityManager->remove($user);
        $entityManager->flush();
        $photoUploader->delete($photo);

        if ($logoutResponse instanceof RedirectResponse) {
            $logoutResponse->setTargetUrl($this->generateUrl('app_login'));

            return $logoutResponse;
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/profile/photo/delete', name: 'app_profile_photo_delete', methods: ['POST'])]
    public function deletePhoto(Request $request, EntityManagerInterface $entityManager, ProfilePhotoUploader $photoUploader): Response
    {
        $user = $this->getCurrentUser();
        if (!$this->isCsrfTokenValid('delete-profile-photo', $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $photo = $user->getPhoto();
        $user->setPhoto(null);
        $entityManager->flush();
        $photoUploader->delete($photo);
        $this->addFlash('success', 'Votre photo de profil a été supprimée.');

        return $this->redirectToRoute('app_profile_account');
    }

    #[Route('/profile/privacy', name: 'app_profile_privacy', methods: ['GET', 'POST'])]
    public function privacy(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getCurrentUser();

        if ($request->isMethod('POST')) {
            if (in_array('ROLE_COACH', $user->getRoles(), true) && !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                throw $this->createAccessDeniedException('Ce réglage est réservé aux clients.');
            }

            if (!$this->isCsrfTokenValid('profile-visibility', $request->request->getString('_token'))) {
                throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
            }

            $user->setIsProfileVisible($request->request->getBoolean('is_profile_visible'));
            $entityManager->flush();
            $this->addFlash('success', 'Votre préférence de confidentialité a été enregistrée.');

            return $this->redirectToRoute('app_profile_privacy');
        }

        return $this->render('profile/privacy.html.twig');
    }

    #[Route('/accessibility', name: 'app_accessibility', methods: ['GET'])]
    public function accessibility(): Response
    {
        return $this->render('profile/accessibility.html.twig');
    }

    #[Route('/profile/password', name: 'app_profile_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getCurrentUser();
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setPasswordChangedAt(new \DateTimeImmutable());
            $entityManager->flush();
            $this->addFlash('success', 'Votre mot de passe a été modifié.');

            return $this->redirectToRoute('app_profile_account');
        }

        return $this->render('profile/change_password.html.twig', ['passwordForm' => $form]);
    }

    #[Route('/profile/email', name: 'app_profile_email', methods: ['GET', 'POST'])]
    public function changeEmail(Request $request, EntityManagerInterface $entityManager, UserRepository $users, MailerInterface $mailer): Response
    {
        $user = $this->getCurrentUser();
        $form = $this->createForm(ChangeEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = mb_strtolower(trim((string) $form->get('newEmail')->getData()));
            if ($newEmail === mb_strtolower((string) $user->getEmail()) || $users->findOneBy(['email' => $newEmail]) !== null) {
                $this->addFlash('email_error', 'Cette adresse e-mail ne peut pas être utilisée.');
            } else {
                $token = bin2hex(random_bytes(32));
                $user->setPendingEmail($newEmail)
                    ->setEmailChangeToken(hash('sha256', $token))
                    ->setEmailChangeExpiresAt(new \DateTimeImmutable('+1 hour'));
                $entityManager->flush();
                $mailer->send((new TemplatedEmail())
                    ->from('noreply@fitprogress.local')->to($newEmail)
                    ->subject('Confirmez votre nouvelle adresse FitProgress')
                    ->htmlTemplate('emails/confirm_email_change.html.twig')
                    ->context(['user' => $user, 'token' => $token]));
                $this->addFlash('success', 'Un lien de confirmation a été envoyé à la nouvelle adresse.');

                return $this->redirectToRoute('app_profile_account');
            }
        }

        return $this->render('profile/change_email.html.twig', ['emailForm' => $form]);
    }

    #[Route('/profile/email/confirm/{token}', name: 'app_profile_email_confirm', methods: ['GET'])]
    public function confirmEmail(string $token, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $user = $this->getCurrentUser();
        $valid = $user->getPendingEmail() !== null
            && $user->getEmailChangeToken() !== null
            && hash_equals($user->getEmailChangeToken(), hash('sha256', $token))
            && $user->getEmailChangeExpiresAt() > new \DateTimeImmutable();
        if (!$valid) {
            $this->addFlash('email_error', 'Ce lien de confirmation est invalide ou expiré.');
            return $this->redirectToRoute('app_profile_account');
        }

        $oldEmail = $user->getEmail();
        $user->setEmail((string) $user->getPendingEmail())
            ->setPendingEmail(null)->setEmailChangeToken(null)->setEmailChangeExpiresAt(null);
        $entityManager->flush();
        if ($oldEmail !== null) {
            $mailer->send((new TemplatedEmail())->from('noreply@fitprogress.local')->to($oldEmail)
                ->subject('Votre adresse e-mail FitProgress a été modifiée')
                ->htmlTemplate('emails/email_changed_notice.html.twig')->context(['user' => $user]));
        }
        $this->addFlash('success', 'Votre nouvelle adresse e-mail est confirmée.');

        return $this->redirectToRoute('app_profile_account');
    }

    private function calculateProgress(?string $initial, ?string $target, ?string $current): int
    {
        if ($initial === null || $target === null || $current === null) {
            return 0;
        }

        $distance = (float) $initial - (float) $target;
        if ($distance === 0.0) {
            return 100;
        }

        $covered = (float) $initial - (float) $current;

        return (int) round(min(100, max(0, ($covered / $distance) * 100)));
    }

    private function formatMemberSince(?\DateTimeImmutable $date): string
    {
        if ($date === null) {
            return 'date inconnue';
        }

        $months = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        return $months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
