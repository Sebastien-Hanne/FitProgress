<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\RequestStatus;
use App\Repository\CoachRequestRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** Central rule for every coach endpoint exposing a user's private fitness data. */
final class UserDataVoter extends Voter
{
    public const VIEW = 'VIEW_USER_FITNESS_DATA';

    public function __construct(private readonly CoachRequestRepository $coachRequests)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $viewer = $token->getUser();
        if (!$viewer instanceof User) {
            return false;
        }
        if ($viewer === $subject || in_array('ROLE_ADMIN', $viewer->getRoles(), true)) {
            return true;
        }
        if (!$subject->isProfileVisible() || !in_array('ROLE_COACH', $viewer->getRoles(), true)) {
            return false;
        }

        $coachProfile = $viewer->getCoachProfile();
        if ($coachProfile === null) {
            return false;
        }

        return $this->coachRequests->findOneBy([
            'user' => $subject,
            'coachProfile' => $coachProfile,
            'status' => RequestStatus::Approved,
        ]) !== null;
    }
}
