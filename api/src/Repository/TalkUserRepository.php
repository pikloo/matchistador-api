<?php

namespace App\Repository;

use App\Entity\TalkUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TalkUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method TalkUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method TalkUser[]    findAll()
 * @method TalkUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TalkUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TalkUser::class);
    }

    public function findStatusByTalkAndUser($talk, $user): ?TalkUser
    {
        return $this->createQueryBuilder('tu')
            ->where('tu.talk = :talk')
            ->andWhere('tu.participant = :user')
            ->setParameters([
                'talk' => $talk,
                'user' => $user
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findStatusByTalkAndExcludeCurrentUser($talk, $user): ?TalkUser
    {
        return $this->createQueryBuilder('tu')
            ->where('tu.talk = :talk')
            ->andWhere('tu.participant != :user')
            ->setParameters([
                'talk' => $talk,
                'user' => $user
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
