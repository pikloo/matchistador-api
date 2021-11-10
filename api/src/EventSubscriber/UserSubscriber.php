<?php

namespace App\EventSubscriber;

use App\Entity\Track;
use App\Entity\UserHasTrack;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<string, array<int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['resolveMe', EventPriorities::PRE_READ],
        ];
    }

    public function resolveMe(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->attributes->get('_route') !== 'api_users_get_item') return;

        if ($request->attributes->get('id') !== 'me') return;

        if ($this->tokenStorage->getToken() === null) throw new AuthenticationException();
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof JWTUser) return;

        $request->attributes->set('id', $user->getUserIdentifier());
    }

}
