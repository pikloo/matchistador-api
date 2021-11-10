<?php

namespace App\Security\Voter;

use App\Entity\UserProfile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserProfileVoter extends Voter
{
    const UPDATE = "update";
    const DELETE = "delete";
    const READ = "read";

    
    protected function supports(string $attribute, $subject): bool
    {

        return in_array($attribute, [self::UPDATE, self::DELETE, self::READ])
            && $subject instanceof UserProfile;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user  instanceof JWTUser) {
            return false;
        }

        $userProfile = $subject;

        return match ($attribute) {
          self::UPDATE => $user->getUserIdentifier() == $userProfile->getUser()->getId(),
          self::DELETE => $user->getUserIdentifier() == $userProfile->getUser()->getId(),
          self::READ => $user->getUserIdentifier() == $userProfile->getUser()->getId(),
          default => false,
      };
    }
}
