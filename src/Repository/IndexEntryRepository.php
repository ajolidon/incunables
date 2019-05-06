<?php

namespace App\Repository;

use App\Entity\IndexEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method IndexEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndexEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndexEntry[]    findAll()
 * @method IndexEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndexEntryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, IndexEntry::class);
    }

    // /**
    //  * @return IndexEntry[] Returns an array of IndexEntry objects
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
    public function findOneBySomeField($value): ?IndexEntry
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
