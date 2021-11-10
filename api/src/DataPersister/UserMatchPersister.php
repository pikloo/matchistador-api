<?php

namespace App\DataPersister;

use App\Entity\Talk;
use App\Entity\UserHasMatchUp;
use App\Repository\TalkRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserHasMatchupRepository;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

/**
 *
 */
class UserMatchPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private EntityManagerInterface $_entityManager,
        private UserHasMatchupRepository $userHasMatchupRepository,
        private TalkRepository $talkRepository,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserHasMatchUp;
    }

    /**
     * @param UserHasMatchUp $data
     */
    public function persist($data, array $context = []): void
    {
        $user = $data->getUser();
        $match = $data->getMatch();
        $userStatus = $data->getIsFavorite();
        $userInverse = $this->userHasMatchupRepository->findInverseUserMatch($user, $match);

        if ($userStatus && $userInverse->getIsFavorite()) {
          $talk = new Talk;
          $talk->setMatch($match);
          $talk->addParticipant($user);
          $talk->addParticipant($userInverse->getUser());
          $this->_entityManager->persist($talk);
        }

        $this->_entityManager->persist($data);
        $this->_entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data, array $context = []): void
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }
}
