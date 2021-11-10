<?php

namespace App\Repository;

use App\Entity\MatchUp;
use App\Entity\UserHasTrack;
use App\Entity\UserHasMatchup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method MatchUp|null find($id, $lockMode = null, $lockVersion = null)
 * @method MatchUp|null findOneBy(array $criteria, array $orderBy = null)
 * @method MatchUp[]    findAll()
 * @method MatchUp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchUpRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MatchUp::class);
    }

    public function findMatchToDelete($limit = null)
    {
        return $this->createQueryBuilder('m')
        ->andWhere('m.isActive = :enabled')
        ->andWhere('m.score = :score')
        ->setParameters([
            'enabled' => true,
            'score' => 0
        ])
        ->setMaxResults($limit)
        ->getQuery()
        ->getResult();

    }
}
