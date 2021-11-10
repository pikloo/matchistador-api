<?php

namespace App\Security\Voter;

use App\Entity\Message;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MessageVoter extends Voter
{
    const UPDATE = "update";
    const DELETE = "delete";
    const READ = "read";

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::UPDATE, self::DELETE, self::READ])
            && $subject instanceof \App\Entity\Message;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $userConnected = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$userConnected instanceof JWTUser) {
            return false;
        }

        /** @var Message $message */
        $message = $subject;
        return match ($attribute) {
            self::UPDATE => $userConnected->getUserIdentifier() == $message->getAuthor()->getId(),
            self::DELETE => $userConnected->getUserIdentifier() == $message->getAuthor()->getId(),
            self::READ => $userConnected->getUserIdentifier() == $message->getAuthor()->getId(),
            default => false,
        };
    }
}