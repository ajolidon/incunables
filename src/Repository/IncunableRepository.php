<?php

namespace App\Repository;

use App\Entity\Incunable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Incunable|null find($id, $lockMode = null, $lockVersion = null)
 * @method Incunable|null findOneBy(array $criteria, array $orderBy = null)
 * @method Incunable[]    findAll()
 * @method Incunable[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncunableRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Incunable::class);
    }

    public function findNewest(int $amount = 5)
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.lastModified', 'DESC')
            ->setMaxResults($amount)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Incunable[] Returns an array of Incunable objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Incunable
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
