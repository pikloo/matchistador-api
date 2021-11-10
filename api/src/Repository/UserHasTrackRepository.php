<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Track;
use App\Entity\MatchUp;
use App\Entity\UserHasTrack;
use App\Entity\UserHasMatchup;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method UserHasTrack|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasTrack|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasTrack[]    findAll()
 * @method UserHasTrack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasTrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private Security $security)
    {
        parent::__construct($registry, UserHasTrack::class);
    }


    public function findTracksToDeleteByUser($tracks, $user)
    {
        set_time_limit(0);

        $qb = $this->createQueryBuilder('ut');
        // ->select('t')
        $qb->where($qb->expr()->notIn('ut.track', ':tracks'))
        // ->innerJoin(Track::class, 't', 'WITH', 'ut.track = t')
            ->andWhere('ut.user = :user')
            ->andWhere('ut.isActive = :isActive')
            ->setParameters([
                'tracks' => $tracks,
                'user' => $user,
                'isActive' => true
            ]);

        // dump($qb->getQuery()->getResult());

        return $qb->getQuery()->getResult();
    }

    public function findCommonsTracksBetweenUsers($userA, $userB, $limit = null)
    {
        $qb = $this->createQueryBuilder('ut')
            ->select('t')
            ->innerJoin(Track::class, 't', 'WITH', 'ut.track = t')
            ->groupBy('t.id')
            ->having('count(t.id) > :count')
            ->andWhere('ut.user = :userA OR ut.user = :userB')
            ->setMaxResults($limit)
            ->setParameters([
                'userA' => $userA,
                'userB' => $userB,
                'count' => 1,
            ]);

        return $qb->getQuery()->getResult();
    }

    public function findCommonsTracksBetweenUsersCollection($userA, $userB, int $page = 1, int $limit = 30,): Paginator
    {
        $currentUser = ($this->security->getUser()) ? $this->security->getUser()->getUserIdentifier() : null;

        $firstResult = ($page - 1) * $limit;
        $qb = $this->createQueryBuilder('ut')
            ->select('t')
            ->innerJoin(Track::class, 't', 'WITH', 'ut.track = t')
            ->groupBy('t.id')
            ->having('count(t.id) > :count');
        $qb->andWhere('ut.user = :userA OR ut.user = :userB');
        //!SECURITY
        if (null !== $currentUser && !in_array('ROLE_ADMIN', $this->security->getUser()->getRoles())) {
            // $qb->innerJoin(UserHasTrack::class, 'ut2', 'WITH', 'ut2 = ut')
            // ->andWhere('ut2.user = :currentUser')
            //     ->setParameters([
            //         'currentUser' => $currentUser,
            //         // 'userA' => $userA,
            //     ])
                ;
        }
        $qb->setMaxResults($limit)
            ->setParameter(":userA", $userA)
            ->setParameter(":userB", $userB)
            ->setParameter(":count", 1);


        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults($limit);
        $qb->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($qb, false);
        // $doctrinePaginator->setUseOutputWalkers(true);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }

    public function findTrackByUser($user)
    {
        return $this->createQueryBuilder('ut')
            ->select('t')
            ->innerJoin(Track::class, 't', 'WITH', 'ut.track = t')
            ->andWhere('ut.user = :user')
            ->andWhere('ut.isActive = :enabled')
            ->setParameters([
                'user' => $user,
                'enabled' => true,
            ])
            ->getQuery()->getResult();
    }
}
