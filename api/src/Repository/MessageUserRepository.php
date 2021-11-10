<?php

namespace App\Repository;

use App\Entity\MessageUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MessageUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MessageUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MessageUser[]    findAll()
 * @method MessageUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MessageUser::class);
    }

    public function findStatusByMessageAndUser($message, $user): ?MessageUser
    {
        return $this->createQueryBuilder('mu')
            ->where('mu.message = :message')
            ->andWhere('mu.participant = :user')
            ->setParameters([
                'message' => $message,
                'user' => $user
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }
}
