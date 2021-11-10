<?php

namespace App\Repository;

use App\Entity\UserData;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method UserData|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserData|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserData[]    findAll()
 * @method UserData[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDataRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserData::class);
    }

    public function findUsersDatasByTokenGenerator($token, $limit = 20){
        return $this->createQueryBuilder('ud')
                ->andWhere('ud.activation_token = :token')
                ->setParameter(":token", $token)
                ->setMaxResults($limit)->getQuery()
                ->getResult();
    }
}
