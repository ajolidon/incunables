<?php

namespace App\Repository;

use App\Entity\Imprint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Imprint|null find($id, $lockMode = null, $lockVersion = null)
 * @method Imprint|null findOneBy(array $criteria, array $orderBy = null)
 * @method Imprint[]    findAll()
 * @method Imprint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImprintRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Imprint::class);
    }

    // /**
    //  * @return Imprint[] Returns an array of Imprint objects
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
    public function findOneBySomeField($value): ?Imprint
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
