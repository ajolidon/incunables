<?php

namespace App\Repository;

use App\Entity\RelationSubject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method RelationSubject|null find($id, $lockMode = null, $lockVersion = null)
 * @method RelationSubject|null findOneBy(array $criteria, array $orderBy = null)
 * @method RelationSubject[]    findAll()
 * @method RelationSubject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RelationSubjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, RelationSubject::class);
    }

    // /**
    //  * @return RelationSubject[] Returns an array of RelationSubject objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RelationSubject
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
