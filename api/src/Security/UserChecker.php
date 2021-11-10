<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserChecker implements UserCheckerInterface
{
    public function __construct(private RequestStack $request){

    }

    public function checkPreAuth(UserInterface $user)
    {      
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActive()) {
            throw new CustomUserMessageAuthenticationException(
                'Ce compte n\'est pas actif. Impossible de se connecter'
            );
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->checkPreAuth($user);
    }
}