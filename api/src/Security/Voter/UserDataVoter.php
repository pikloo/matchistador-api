<?php

namespace App\Security\Voter;

use App\Entity\UserData;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserDataVoter extends Voter
{
    const UPDATE = "update";
    const CREATE = "create";
    const READ = "read";
    
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::UPDATE, self::CREATE, self::READ])
            && $subject instanceof \App\Entity\UserData;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof JWTUser) {
            return false;
        }

        /** @var UserData $userData */
        $userData = $subject;

        return match ($attribute) {
            self::UPDATE => $userData->getUser()->getId() == $user->getUserIdentifier(),
            self::CREATE => $userData->getUser()->getId() == $user->getUserIdentifier(),
            self::READ => $userData->getUser()->getId() == $user->getUserIdentifier(),
            default => false,
        };

    }
}
