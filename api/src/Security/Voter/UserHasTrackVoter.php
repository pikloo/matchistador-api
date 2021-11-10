<?php

namespace App\Security\Voter;

use App\Entity\UserHasTrack;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserHasTrackVoter extends Voter
{
    const UPDATE = "update";
    const READ = "read";
    
    protected function supports(string $attribute, $subject): bool
    {

        return in_array($attribute, [self::UPDATE, self::READ])
            && $subject instanceof UserHasTrack;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof JWTUser) {
            return false;
        }

         /** @var UserHasTrack $userData */
        $userHasTrack = $subject;

        return match ($attribute) {
            self::UPDATE => $userHasTrack->getUser()->getId() == $user->getUserIdentifier(),
            self::READ => $userHasTrack->getUser()->getId() == $user->getUserIdentifier(),
            default => false,
        };

    }
}
