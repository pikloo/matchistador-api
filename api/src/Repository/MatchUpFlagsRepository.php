<?php

namespace App\Repository;

use App\Entity\MatchUp;
use App\Entity\UserHasMatchup;
use App\Entity\MatchUpFlags;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method MatchUpFlags|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchUpFlags|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchUpFlags[]    findAll()
 * @method MatchUpFlags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchUpFlagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchUpFlags::class);
    }

    public function findAllMatchsToCalculate($limit = 1000)
    {
        return $qb = $this->createQueryBuilder('mf')
            ->select('m')
            ->innerJoin(MatchUp::class, 'm', 'WITH', 'm = mf.match')
            ->andWhere('mf.calculFlag = :enabled')
            ->setParameters([
                'enabled' => true,
            ])
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
