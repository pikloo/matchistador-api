<?php

namespace App\Repository;

use App\Entity\UserDataFlags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserDataFlags|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDataFlags|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDataFlags[]    findAll()
 * @method UserDataFlags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDataFlagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDataFlags::class);
    }

    // /**
    //  * @return UserDataFlags[] Returns an array of UserDataFlags objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserDataFlags
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
