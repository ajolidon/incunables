<?php

namespace App\Repository;

use App\Entity\Digitalisation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Digitalisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Digitalisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Digitalisation[]    findAll()
 * @method Digitalisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DigitalisationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Digitalisation::class);
    }

    // /**
    //  * @return Digitalisation[] Returns an array of Digitalisation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Digitalisation
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
