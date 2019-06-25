<?php

namespace App\Repository;

use App\Entity\ShowDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ShowDate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShowDate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShowDate[]    findAll()
 * @method ShowDate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShowDateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ShowDate::class);
    }


    public function findByDates($start, $end): array
    {

        return $this->createQueryBuilder('s')
            ->andWhere('s.dateShow >= :start')
            ->andWhere('s.dateShow < :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult()
            ;
    }
    // /**
    //  * @return ShowDate[] Returns an array of ShowDate objects
    //  */
/*
    public function findByDate($date)
    {

        return $this->createQueryBuilder('s')
            ->andWhere(Date_format(s.dateShow, "%Y-%m-%d") = ':val')
            ->setParameter('val', $date)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
*/

    /*
    public function findOneBySomeField($value): ?ShowDate
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
