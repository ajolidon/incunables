<?php

namespace App\Repository;

use App\Entity\IncunableRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IncunableRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncunableRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncunableRelation[]    findAll()
 * @method IncunableRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncunableRelationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IncunableRelation::class);
    }

    // /**
    //  * @return WorkRelation[] Returns an array of WorkRelation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?WorkRelation
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
