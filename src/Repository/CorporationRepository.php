<?php

namespace App\Repository;

use App\Entity\Corporation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Corporation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Corporation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Corporation[]    findAll()
 * @method Corporation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CorporationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Corporation::class);
    }

    // /**
    //  * @return Corporation[] Returns an array of Corporation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Corporation
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
