<?php

namespace App\Repository;

use App\Entity\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Orders>
 *
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }


    public function findOrdersPaid(): array
    {
        return $this->getEntityManager()->createQueryBuilder('o')
              ->select('SUM(o.total)')
              ->from('App\Entity\Orders','o')
              ->Where("o.is_withdraw =1")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findOrdersPaidByMonth(): array
    {
        return $this->getEntityManager()->createQueryBuilder('o')
              ->select('SUBSTRING(o.created_at,1,10) as date,(SUM(o.total)*(2/100))')
              ->from('App\Entity\Orders','o')
              ->Where("o.is_withdraw =1")
              ->groupBy('date')
              ->getQuery()
              ->getResult()
            ;
    }
    

//    /**
//     * @return Orders[] Returns an array of Orders objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Orders
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
