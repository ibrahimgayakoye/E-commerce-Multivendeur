<?php

namespace App\Controller\EventSubscriber;

use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;
use App\Entity\Pack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CartSubscriber implements EventSubscriberInterface{

    private $environment;

    private $manager;

    private $requestStack;
    private $tokenStorage;

    public function __construct( Environment $environment, EntityManagerInterface $manager, RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->environment = $environment;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage ;
       

    }

    /**
     * Injection de la variable globale carts à Twig
     *
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event) {
    
         
        
         $packs = $this->manager->getRepository(Pack::class)->findAll();
         $panier = $this->requestStack->getSession()->get('panier',[]);

         $panier = count($panier);
         
         
 
        //
        //$this->environment->addGlobal('cart', $this->manager->getRepository(Cart::class)->findAll());
        // Injection de la variable cart dans Twig
        $this->environment->addGlobal('cart', $panier);
        $this->environment->addGlobal('packs',$packs);
       
        
        
       

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