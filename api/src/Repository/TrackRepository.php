<?php

namespace App\Repository;

use App\Entity\Track;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Track|null find($id, $lockMode = null, $lockVersion = null)
 * @method Track|null findOneBy(array $criteria, array $orderBy = null)
 * @method Track[]    findAll()
 * @method Track[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Track::class);
    }

    public function findByTracks($tracks)
    {
        
        $tracksToCheck = [];
    foreach ($tracks as $track) {
      $tracksToCheck[] = $track->getName();
    }
        
        // dd($tracks);
        $qb = $this->createQueryBuilder('t')
        ->andWhere('t.name IN ('.$tracks.')')
            // ->setParameters([
            //     ':tracks' => $tracks,
            // ]);
            ;

        return $qb->getQuery()->getResult();
    }
}
