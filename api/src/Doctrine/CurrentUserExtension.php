<?php

namespace App\Doctrine;

use App\Entity\Talk;
use App\Entity\Track;
use App\Entity\Message;
use App\Entity\UserProfile;
use App\Entity\UserHasTrack;
use App\Entity\UserHasMatchup;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;

final class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security,
        private UserRepository $userRepository,
        private RequestStack $request
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $resourceArray = [
            UserHasTrack::class,
            UserProfile::class,
        ];
        if ($this->security->isGranted('ROLE_ADMIN') || null === $user = $this->security->getUser()) {
            return;
        }

        $user = $this->userRepository->find($user->getUserIdentifier());

        if (in_array($resourceClass, $resourceArray)) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere(sprintf('%s.user = :current_user', $rootAlias));
            $queryBuilder->setParameter('current_user', $user);
        }

        if ($resourceClass === Message::class) {
            $rootAlias = $queryBuilder->getRootAliases()[0];
            $queryBuilder->setParameter('current_user', $user);
            $queryBuilder->innerJoin($rootAlias . '.messageUsers', 'mu', 'WITH', 'mu.participant = :current_user');
        }

    }
}
