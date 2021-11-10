<?php

namespace App\DataPersister;

use App\Entity\Message;
use App\Repository\TalkRepository;
use App\Repository\TalkUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserHasMatchupRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

/**
 *
 */
class MessageDataPersister implements ContextAwareDataPersisterInterface
{
    public function __construct(
        private EntityManagerInterface $_entityManager,
        private UserHasMatchupRepository $userHasMatchupRepository,
        private TalkRepository $talkRepository,
        private TalkUserRepository $talkUserRepository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Message;
    }

    /**
     * @param Message $data
     */
    public function persist($data, array $context = []): void
    {
        if (null === $data->getCreatedAt()) {
            $user = $data->getAuthor();
            if (!$data->getTalk()->isParticipantInTalk($user)) {
                throw new AccessDeniedException();
            }
            $talk = $data->getTalk();
            $participants = $talk->getTalkUsers()->toArray();
            foreach ($participants as $participant) {
                $data->addParticipant($participant->getParticipant());
            }

            $talkUserStatus = $this->talkUserRepository->findStatusByTalkAndExcludeCurrentUser($talk, $user);
            $talkUserStatus->setReadingStatus(false);
            $this->_entityManager->persist($talkUserStatus);
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
