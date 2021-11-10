<?php

namespace App\DataPersister;

use App\Entity\UserData;
use App\Service\MatchGenerator;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

/**
 *
 */
class UserDataPersister implements ContextAwareDataPersisterInterface
{
    const POST = 'post';
    const PATCH = 'patch';

    public function __construct(
        private EntityManagerInterface $_entityManager,
        private MatchGenerator $matchGenerator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof UserData;
    }

    /**
     * @param UserData $data
     */
    public function persist($data, array $context = []): void
    {
        $user = $data->getUser();

        if ($data->getPositionLng() || $data->getPositionLat()) {
            $location = sprintf('POINT(%f %f)',  $data->getPositionLng(), $data->getPositionLat());
            $data->setLocation($location);
        }

        if (isset($context["item_operation_name"]) && $context["item_operation_name"] === self::PATCH) {
            $previousData = $context["previous_data"];
            if (
                $previousData->getPositionLng() !== $data->getPositionLng()
                ||  $previousData->getPositionLat() !== $data->getPositionLat()
                || $previousData->getSexualOrientation() !== $data->getSexualOrientation()
                || $previousData->getGender() !== $data->getGender()
            ) {
                $this->matchGenerator->usersFinder($user, $data, 30);
            }
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
