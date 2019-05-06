<?php

namespace App\Repository;

use App\Entity\SearchEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SearchEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method SearchEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method SearchEntry[]    findAll()
 * @method SearchEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SearchEntryRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SearchEntry::class);
    }

    public function findByQuery(string $query)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.value LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->addOrderBy('s.priority', 'ASC')
            ->addOrderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return SearchEntry[] Returns an array of SearchEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SearchEntry
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
