<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(
            SecurityRequestAttributes::LAST_USERNAME,
            $email
        );

        return new Passport(
            new UserBadge($email, function (string $identifier) {
                $user = $this->userRepository->findOneBy(['email' => $identifier]);
                if ($user === null || $user->isDeleted()) {
                    throw new CustomUserMessageAuthenticationException('Identifiants invalides.');
                }

                return $user;
            }),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge(
                    'authenticate',
                    $request->getPayload()->getString('_csrf_token')
                ),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response {
        $user = $token->getUser();

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('admin_dashboard')
            );
        }

        if (in_array('ROLE_COACH', $user->getRoles(), true)) {
            return new RedirectResponse(
                $this->urlGenerator->generate('coach_dashboard')
            );
        }

        if ($user->getGoal() === null) {
            return new RedirectResponse($this->urlGenerator->generate('app_onboarding'));
        }

        return new RedirectResponse(
            $this->urlGenerator->generate('dashboard')
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
