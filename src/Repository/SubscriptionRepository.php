<?php

namespace App\Repository;

use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subscription>
 *
 * @method Subscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subscription[]    findAll()
 * @method Subscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subscription::class);
    }

    public function findSubcriptionsRevenu(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
              ->select('s')
              ->from('App\Entity\Subscription','s')
              ->where("s.stripeSessionId is not null")
              ->orWhere("s.paypalOrderId is not null")
              ->getQuery()
              ->getResult()
            ;
    }


    public function findSubcriptionsRevenuByDate(): array
    {
        return $this->getEntityManager()->createQueryBuilder('s','p')
              ->select('SUBSTRING(s.created_at,1,10) as datePayment, SUM(p.amount)')
              ->from('App\Entity\Subscription', 's')
              ->join('s.pack','p')
              ->where("s.stripeSessionId is not null")
              ->orWhere("s.paypalOrderId is not null")
              ->groupBy('datePayment')
              ->getQuery()
              ->getResult()
            ;
    }

    

    public function findSubscribedCustomerByMonth(): array
    {
        $role = "ROLE_USER";
        return $this->getEntityManager()->createQueryBuilder('s','u')
              ->select('SUBSTRING(s.created_at,1,10) as dateSubscription, count(u)')
              ->from('App\Entity\Subscription', 's')
              ->join('s.users','u')
              ->Where("s.stripeSessionId is not null")
              ->orWhere("s.paypalOrderId is not null")
              ->andWhere('u.roles like :role')
              ->setParameter('role',"%{$role}%")
              ->groupBy('dateSubscription')
              ->getQuery()
              ->getResult()
            ;
    }

   

//    /**
//     * @return Subscription[] Returns an array of Subscription objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Subscription
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
