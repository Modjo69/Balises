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

    public function spectaclePerDates($start, $end, $today) : array
    {
        return $this->createQueryBuilder('d')
            ->where('d.dateShow >= :start')
            ->andWhere('d.dateShow >= :today')
            ->andWhere('d.dateShow < :end')
            ->setParameter('start', $start)
            ->setParameter('today', $today)
            ->setParameter('end', $end)
            ->orderBy('d.dateShow', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }


    public function dateList($spectacleId) :array
    {
        return $this->createQueryBuilder('d')
            ->where('d.showId >= :spectacleId')
            ->setParameter('spectacleId', $spectacleId)
            ->orderBy('d.dateShow', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
            ;
    }

    public function moreDateList($spectacleId) :array
    {
        return $this->createQueryBuilder('d')
            ->where('d.showId >= :spectacleId')
            ->setParameter('spectacleId', $spectacleId)
            ->orderBy('d.dateShow', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
//    public function searchThreeDates():array
//    {
//        $entityManager = $this->getEntityManager();
//
//        $query = $entityManager->createQuery(
//            'SELECT *
//                FROM App\Entity\ShowDate s
//                WHERE s.dateShow > NOW()
//                ORDER BY s.dateShow DESC LIMIT 3'
//        );
//            return $query->execute();
//    }
}
