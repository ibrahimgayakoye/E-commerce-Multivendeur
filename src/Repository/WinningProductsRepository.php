<?php

namespace App\Repository;

use App\Entity\WinningProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WinningProducts>
 *
 * @method WinningProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method WinningProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method WinningProducts[]    findAll()
 * @method WinningProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WinningProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WinningProducts::class);
    }


    


//    /**
//     * @return WinningProducts[] Returns an array of WinningProducts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('w.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?WinningProducts
//    {
//        return $this->createQueryBuilder('w')
//            ->andWhere('w.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
