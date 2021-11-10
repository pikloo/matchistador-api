<?php

namespace App\Security\Voter;

use App\Entity\Talk;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TalkVoter extends Voter
{
    const UPDATE = "update";
    const DELETE = "delete";
    const READ = "read";

    public function __construct(private UserRepository $userRepository) {
    }

    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::UPDATE, self::DELETE, self::READ])
            && $subject instanceof \App\Entity\Talk;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $userConnected = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$userConnected instanceof JWTUser) {
            return false;
        }

        /** @var Talk $talk */
        $talk = $subject;
        $user = $this->userRepository->find($userConnected->getUserIdentifier());
        return match ($attribute) {
            self::UPDATE => in_array($user, $talk->getAllParticipants()),
            self::DELETE => in_array($user, $talk->getAllParticipants()),
            self::READ => in_array($user, $talk->getAllParticipants()),
            default => false,
        };
    }
}