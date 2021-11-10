<?php

namespace App\Repository;

use App\Entity\ProfileTheme;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfileTheme|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfileTheme|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfileTheme[]    findAll()
 * @method ProfileTheme[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfileThemeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfileTheme::class);
    }

}
