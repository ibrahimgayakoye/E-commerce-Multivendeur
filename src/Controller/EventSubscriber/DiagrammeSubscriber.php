<?php

namespace App\Controller\EventSubscriber;

use App\Entity\Orders;
use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;
use App\Entity\Pack;
use App\Entity\Subscription;
use App\Entity\Users;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DiagrammeSubscriber implements EventSubscriberInterface{

    private $environment;

    private $manager;

    private $requestStack;
    private $tokenStorage;

    public function __construct( Environment $environment, EntityManagerInterface $manager,TokenStorageInterface $tokenStorage)
    {
        $this->environment = $environment;
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage ;
       

    }

    /**
     * Injection de la variable globale carts à Twig
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event) {
    
         
        
      //le nombre d'abonne nayant pas payer
      $subscribersNotPaid = $this->manager->getRepository(Users::class)->findSubscribersNotPaid();
      $numbersubscribersNotPaid = count($subscribersNotPaid);

      // nombre d'abonnees ayant pris le pack pour un mois
      $subscribersPaidMonth = $this->manager->getRepository(Users::class)->findSubscribersPaidMonth();
      $numbersubscribersPaidMonth = count($subscribersPaidMonth);

       // nombre d'abonnees ayant pris le pack pour un 6 mois
        $subscribersPaid6Month = $this->manager->getRepository(Users::class)->findSubscribersPaid6Month();
        $numbersubscribersPaid6Month = count($subscribersPaid6Month);

        // le nombre de client inscrit par date
        $customersByMonths = $this->manager->getRepository(Subscription::class)->findSubscribedCustomerByMonth();
        
        $dates=[];
        $numbers=[];

        foreach($customersByMonths as $customersByMonth){
            $dates[] = $customersByMonth['dateSubscription'];
            $numbers[] = $customersByMonth['1'];
        }

        $datesRevenu=[];
        $revenus = [];

        // les  revenus des commandes de la plateform
        $ordersRevenus = $this->manager->getRepository(Orders::class)->findOrdersPaidByMonth();
        foreach($ordersRevenus as $ordersRevenu){
            $datesRevenu[] = $ordersRevenu['date'];
            $revenus[] = (int)$ordersRevenu['1'];
        }

        $datesSubscription =[];
        $subscriptionAmount=[];

        // les revenus des  inscriptions par date
        $subscriptionsRevenus = $this->manager->getRepository(Subscription::class)->findSubcriptionsRevenuByDate();
        foreach($subscriptionsRevenus as $subscriptionsRevenu){
            $datesSubscription[] = $subscriptionsRevenu['datePayment'];
            $subscriptionAmount[] = (int)$subscriptionsRevenu['1'];
        }

       
        



 
        $this->environment->addGlobal('subscribersNotPaid',json_decode($numbersubscribersNotPaid));
        $this->environment->addGlobal('subscribersPaidMonth',json_decode($numbersubscribersPaidMonth));
        $this->environment->addGlobal('numbersubscribersPaid6Month', json_decode($numbersubscribersPaid6Month));
        $this->environment->addGlobal('dates', json_encode($dates));
        $this->environment->addGlobal('numbers', json_encode($numbers));
        $this->environment->addGlobal('datesRevenu',json_encode($datesRevenu));
        $this->environment->addGlobal('revenus', json_encode($revenus));
       

        $this->environment->addGlobal('datesSubscription',json_encode($datesSubscription));
        $this->environment->addGlobal('subscriptionAmount', json_encode($subscriptionAmount));

        
        
       // dans une vue twig, on peut faire {{ dump(cart) }}
    }

    /**
     *
     * @return array
     */
    public static function getSubscribedEvents(): array {
        return [
            'kernel.controller' => 'onKernelController',
        ];
    }
}