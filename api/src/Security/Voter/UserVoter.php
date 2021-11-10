<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    const UPDATE = "update";
    const DELETE = "delete";
    const READ = "read";

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::UPDATE, self::DELETE, self::READ])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $userConnected = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$userConnected instanceof JWTUser) {
            return false;
        }

        /** @var User $user */
        $user = $subject;
        return match ($attribute) {
            self::UPDATE => $userConnected->getUserIdentifier() == $user->getId(),
            self::DELETE => $userConnected->getUserIdentifier() == $user->getId(),
            self::READ => $userConnected->getUserIdentifier() == $user->getId(),
            default => false,
        };
    }
}
