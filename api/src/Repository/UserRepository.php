<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserData;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    
    const FEMALE = "female";
    const MALE = "male";
    const BOTH = "both";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function findUsersInScope($location, $orientationToSearch, $limit = null, $distance = 500000)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u as user')
            // ->select(array('partial u.{id}, ud.id'))
            // ->join('ud.user', 'u', 'WITH', 'u.isActive = :status')

            ->innerJoin(UserData::class, 'ud', 'WITH', 'ud.user = u')
            // ->innerJoin(Track::class, 't', 'WITH', 'ut.track = t')
            ->addSelect('ST_Distance(:location2, ud.location) AS distance')
            // ->groupBy('t.id, distance, ud')
            // ->having('count(t.id) > :count')
            ->orderBy('distance', 'ASC')
            ->andWhere('u.isActive = :status')
            ->andWhere('ST_Distance(:location1, ud.location) < :dist');
        if ($orientationToSearch[0] === "female" && $orientationToSearch[1] === "male") {
            $qb->andWhere('ud.gender = :gender')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->setParameters([
                    'gender' => self::MALE,
                    1 => self::FEMALE,
                    2 => self::BOTH
                ]);
        }
        if ($orientationToSearch[0] === "male" && $orientationToSearch[1] === "female") {
            $qb->andWhere('ud.gender = :gender')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->setParameters([
                    'gender' => self::FEMALE,
                    1 => self::MALE,
                    2 => self::BOTH
                ]);
        }
        if ($orientationToSearch[0] === "female" && $orientationToSearch[1] === "female") {
            $qb->andWhere('ud.gender = :gender')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->setParameters([
                    'gender' => self::FEMALE,
                    1 => self::FEMALE,
                    2 => self::BOTH
                ]);
        }
        if ($orientationToSearch[0] === "male" && $orientationToSearch[1] === "male") {
            $qb->andWhere('ud.gender = :gender')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->setParameters([
                    'gender' => self::MALE,
                    1 => self::MALE,
                    2 => self::BOTH
                ]);
        }
        if ($orientationToSearch[0] === "female" && $orientationToSearch[1] === "both") {
            $qb->orWhere('ud.gender = :gender1')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->orWhere('ud.gender = :gender2')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?3'),
                    $qb->expr()->eq('ud.sexualOrientation', '?4')
                ))
                ->setParameters([
                    'gender1' => self::MALE,
                    'gender2' => self::FEMALE,
                    1 => self::FEMALE,
                    2 => self::BOTH,
                    3 => self::FEMALE,
                    4 => self::BOTH,
                ]);
        }
        if ($orientationToSearch[0] === "male" && $orientationToSearch[1] === "both") {
            $qb->orWhere('ud.gender = :gender1')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?1'),
                    $qb->expr()->eq('ud.sexualOrientation', '?2')
                ))
                ->orWhere('ud.gender = :gender2')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ud.sexualOrientation', '?3'),
                    $qb->expr()->eq('ud.sexualOrientation', '?4')
                ))
                ->setParameters([
                    'gender1' => self::MALE,
                    'gender2' => self::FEMALE,
                    1 => self::MALE,
                    2 => self::BOTH,
                    3 => self::MALE,
                    4 => self::BOTH,
                ]);
        }
        // $qb->setParameters([
        //     'location1' => $location,
        //     'location2' => $location,
        //     'dist' => $distance,
        //     'status' =>true,
        //     'count' => 1
        // ]);
        // $qb->andWhere('ud != :userData');
        $qb->setParameter(":location1", $location)
            ->setParameter(":location2", $location)
            ->setParameter(":dist", $distance)
            ->setParameter(":status", true)
            // ->setParameter(":userData", $userData)
            // ->setParameter(":count", 1)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
