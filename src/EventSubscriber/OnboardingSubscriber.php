<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OnboardingSubscriber implements EventSubscriberInterface
{
    private const ALLOWED_ROUTES = [
        'app_onboarding',
        'app_logout',
        'app_home',
        'app_login',
        'app_register',
        'app_register_coach',
        'app_forgot_password_request',
        'app_check_email',
        'app_reset_password',
        'app_legal_terms',
        'app_legal_privacy',
    ];

    public function __construct(
        private readonly Security $security,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['redirectIncompleteUser', 0]];
    }

    public function redirectIncompleteUser(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->getString('_route');
        if ($route === '' || str_starts_with($route, '_') || in_array($route, self::ALLOWED_ROUTES, true)) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User || $user->getGoal() !== null) {
            return;
        }

        $roles = $user->getRoles();
        if (in_array('ROLE_COACH', $roles, true) || in_array('ROLE_ADMIN', $roles, true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_onboarding')));
    }
}
