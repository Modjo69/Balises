<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function getPaginationOrderUsers($limit, $off_asc, $off_desc, $champ, $order)
    {
        
        switch ($champ) {
            case "zipCode":
                $field = 'theater.zipCode';
                break;
            case "name":
                $field = 'theater.name';
                break;
            case "email":
                $field = 'user.email';
                break;
            default:
                $field = 'theater.id';
        }

        if ($order == 'up') {
            $order="DESC";
            $off=$off_desc;
        } else {
            $order="ASC";
            $off=$off_asc;
        }

        
        return $this->createQueryBuilder('user')
        ->join('user.theater', 'theater')
        ->setFirstResult($off)
        ->setMaxResults($limit)
        ->orderBy($field, $order)
        ->getQuery()
        ->getResult()
        ;
    }    
    
    
}
