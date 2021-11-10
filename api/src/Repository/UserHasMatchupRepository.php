<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\MatchUp;
use App\Entity\MatchUpFlags;
use App\Entity\UserHasMatchup;
use App\Entity\UserMatchUpFlags;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method UserHasMatchup|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserHasMatchup|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserHasMatchup[]    findAll()
 * @method UserHasMatchup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserHasMatchupRepository extends ServiceEntityRepository
{
    const ITEMS_PER_PAGE = 30;

    public function __construct(ManagerRegistry $registry, private Security $security)
    {
        parent::__construct($registry, UserHasMatchup::class);
    }


    public function findInverseUserMatch($user, $match)
    {

        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.match = :match')
            ->andWhere('u.user != :user')
            // $qb->andwhere($qb->expr()->notIn('u.user', $user))
            ->setParameters([
                'user' => $user,
                'match' => $match
            ])
            ->getQuery()
            ->getOneOrNullResult();

        return $qb;
    }

    public function findInverseMatchByUser($user, int $page = 1, int $limit = 30, bool $isActive = true, $orderByScore = null, $orderByUpdatedAt = null ): Paginator
    {
        $currentUser = $this->security->getUser()->getUserIdentifier();
        $firstResult = ($page - 1) * $limit;
        $qb = $this->createQueryBuilder('um')
            ->select('um2')
            ->innerJoin(UserHasMatchup::class, 'um2', 'WITH', 'um.match = um2.match')
            ->innerJoin(User::class, 'u', 'WITH', 'u = um2.user')
            ->innerJoin(MatchUp::class, 'm', 'WITH', 'um.match = m')
            ->andWhere('um.user = :user')
            ->andWhere('um2.user != :user')
            ->andWhere('u.isActive = :enabled');
            //Sécurité Userco
            if(!in_array('ROLE_ADMIN',$this->security->getUser()->getRoles())) $qb->andWhere('um.user = :current_user')
            ->andWhere('m.isActive = :enabled');
            if($orderByScore !== "") $qb->addOrderBy('m.score', $orderByScore);
            if($orderByUpdatedAt !== "") $qb->addOrderBy('m.updatedAt', $orderByUpdatedAt);
            $qb->setParameters([
                'user' => $user,
                'enabled' => $isActive,
                'current_user' => $currentUser
            ]);

        $criteria = Criteria::create()
            ->setFirstResult($firstResult)
            ->setMaxResults($limit);
        $qb->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($qb, false);
        $doctrinePaginator->setUseOutputWalkers(false);
        $paginator = new Paginator($doctrinePaginator);

        return $paginator;
    }

    public function findInverseMatch($user, $limit = null)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u2')
            ->innerJoin(UserHasMatchup::class, 'u2', 'WITH', 'u.match = u2.match')
            ->andWhere('u.user = :user')
            ->andWhere('u2.user != :user')
            ->setParameter('user', $user)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return $qb;
    }

    public function findMatchByUsers($userA, $userB)
    {
        $qb = $this->createQueryBuilder('um')
            ->select(array('partial m.{id}'))
            ->addSelect('count(um.match) AS counter')
            ->innerJoin(MatchUp::class, 'm', 'WITH', 'um.match = m')
            ->groupBy('m.id')
            ->having('count(m.id) > :count')
            ->andWhere('um.user = :userA OR um.user = :userB')
            ->setParameters([
                'userA' => $userA,
                'userB' => $userB,
                'count' => 1
            ]);

        return $qb->getQuery()->getResult();
    }

   
}
