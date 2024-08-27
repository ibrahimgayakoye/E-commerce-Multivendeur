<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Subscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
* @implements PasswordUpgraderInterface<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    

    public function findSubscribersPaid(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->from('App\Entity\Subscription','s')
              ->where("u.subscription = s.id ")
              ->andWhere("s.is_paid =1")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findSellers(): array
    {
        $role = "ROLE_SELLER";
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->Where('u.roles like :role')
              ->setParameter('role',"%{$role}%")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findCustomers(): array
    {
        $role = "ROLE_USER";
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->Where('u.roles like :role')
              ->setParameter('role',"%{$role}%")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findSubscribersPaidMonth(): array
    {    $role = "ROLE_USER";
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->from('App\Entity\Subscription','s')
              ->where("u.subscription = s.id ")
              ->andWhere("s.is_paid =1")
              ->andWhere("s.pack in(1,2,3)")
              ->andWhere('u.roles like :role')
              ->setParameter('role',"%{$role}%")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findSubscribersPaid6Month(): array
    {   $role = "ROLE_USER";
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->from('App\Entity\Subscription','s')
              ->where("u.subscription = s.id ")
              ->andWhere("s.is_paid =1")
              ->andWhere("s.pack in(4,5,6)")
              ->andWhere('u.roles like :role')
              ->setParameter('role',"%{$role}%")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findSubscribersNotPaid(): array
    {
        return $this->getEntityManager()->createQueryBuilder()
              ->select('u')
              ->from('App\Entity\Users','u')
              ->from('App\Entity\Subscription','s')
              ->where("u.subscription = s.id ")
              ->andWhere("s.is_paid =0")
              ->getQuery()
              ->getResult()
            ;
    }

    public function findsellerProductsPending($user): array
    {
        return $this->getEntityManager()->createQueryBuilder('u','p')
        ->select('p')
        ->from('App\Entity\Products','p')
        ->from('App\Entity\Users','u')
        ->where("p.user ='$user'")
        ->andWhere("p.statuts=1")
        ->getQuery()
        ->getResult()
      ;

    }


    public function findsellerProductsAccepted($user): array
    {
        return $this->getEntityManager()->createQueryBuilder('u','p')
        ->select('p')
        ->from('App\Entity\Products','p')
        ->from('App\Entity\Users','u')
        ->where("p.user ='$user'")
        ->andWhere("p.statuts=2")
        ->getQuery()
        ->getResult()
      ;

    }


    public function findsellerProductsRected($user): array
    {
        return $this->getEntityManager()->createQueryBuilder('u','p')
        ->select('p')
        ->from('App\Entity\Products','p')
        ->from('App\Entity\Users','u')
        ->where("p.user ='$user'")
        ->andWhere("p.statuts=3")
        ->getQuery()
        ->getResult()
      ;

    }


   

//    /**
//     * @return Users[] Returns an array of Users objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Users
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
