<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\MatchUp;
use App\Entity\UserHasTrack;
use App\Entity\UserHasMatchup;
use App\Entity\UserTrackFlags;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method UserTrackFlags|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTrackFlags|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTrackFlags[]    findAll()
 * @method UserTrackFlags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTrackFlagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTrackFlags::class);
    }

    public function findAllCreateUTF($limit = null){
        return $this->createQueryBuilder('utf')
            ->andWhere('utf.createFlag = :enabled')
            ->andWhere('u.isActive = :enabled')
            ->innerJoin(UserHasTrack::class,'ut', 'WITH','ut = utf.userTrack')
            ->innerJoin(User::class, 'u','WITH', 'u = ut.user')
            ->setParameters([
                'enabled' => true,
            ])
            ->orderBy('utf.createdAt', 'asc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllUpdateUTF($limit = null){
        return $this->createQueryBuilder('utf')
            ->andWhere('utf.updateFlag = :enabled')
            ->setParameters([
                'enabled' => true,
            ])
            ->orderBy('utf.updatedAt', 'asc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllDeleteUTF($limit = null){
        return $this->createQueryBuilder('utf')
            ->andWhere('utf.deleteFlag = :enabled')
            ->setParameters([
                'enabled' => true,
            ])
            ->orderBy('utf.updatedAt', 'asc')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findMatchByCommonTrack($userTrackFlag)
    {
        return $this->createQueryBuilder('utf')
            ->select('m')
            ->innerJoin(UserHasTrack::class,'ut', 'WITH','ut = utf.userTrack')
            ->innerJoin(UserHasMatchup::class, 'um', 'WITH','um.user = ut.user')
            ->innerJoin(MatchUp::class, 'm', 'WITH','m = um.match')
            ->andWhere('utf = :userTrackFlag')
            ->setParameters([
                'userTrackFlag' => $userTrackFlag,
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
