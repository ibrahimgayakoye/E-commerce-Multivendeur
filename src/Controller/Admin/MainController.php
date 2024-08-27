<?php

namespace App\Controller\Admin;

use App\Entity\OrderDetails;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Statuts;
use App\Entity\Subscription;
use App\Entity\Users;
use App\Repository\OrdersRepository;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name:'admin_')]
class MainController extends AbstractController
{
    #[Route('/', name:'index')]
    public function dashboard(EntityManagerInterface $em): Response

    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }

        //seletiononer toutes les commandes de l'utilisateur
        $orders = $em->getRepository(Orders::class)->findAll();
        $numberOrders = count($orders);
        

        //selectioner le nombre d'utilisateur total
        $users = $em->getRepository(Users::class)->findAll();
        $numberUsers = count($users);
        
        // slectionner le nombre total de produit
        $products = $em->getRepository(Products::class)->findAll();
        $numberProducts = count($products);

        // le nombre total d'inscrit sur la plate-form
        $subscribers = $em->getRepository(Subscription::class)->findAll();
        $numberSubscribers = count($subscribers);

        //le nombre d'abonnees ayant payer
        $subscribersPaid = $em->getRepository(Users::class)->findSubscribersPaid();
        $numbersubscribersPaid = count($subscribersPaid);
        
        //le nombre d'abonne nayant pas payer
        $subscribersNotPaid = $em->getRepository(Users::class)->findSubscribersNotPaid();
        $numbersubscribersNotPaid = count($subscribersNotPaid);
        
        // nombre de commandes totales
        $orders = $em->getRepository(Orders::class)->findAll();
        $numberOrders = count($orders);

        // le nombre de vendeurs
        $sellers = $em->getRepository(Users::class)->findSellers();
        $numberSellers = count($sellers);

        // le nombre de clients
        $sellers = $em->getRepository(Users::class)->findSellers();
        $numberSellers = count($sellers);



         // le nombre de clients
         $customers = $em->getRepository(Users::class)->findCustomers();
         $numberCustomers = count($customers);

         // les revenus par abonnement de la plateforme
         $subscriptions = $em->getRepository(Subscription::class)->findSubcriptionsRevenu();

         $totalSubscriptionRevenuTab = [];

         foreach($subscriptions as $subscription){
            $totalSubscriptionRevenuTab[]= $subscription->getPack()->getAmount();
         }

         $totalSubscriptionRevenu = array_sum($totalSubscriptionRevenuTab);

         // le total en argent des commandes livrer
         $ordersMoney = $em->getRepository(Orders::class)->findOrdersPaid();
         $totalOrdersMoney =$ordersMoney[0][1];
         $totalOrdersRevenue = ($totalOrdersMoney*(2/100));

         // le nombre de client inscrit par mois
         //$customersByMonth = $em->getRepository(Subscription::class)->findSubscribedCustomerByMonth();
         //dd($customersByMonth);
       
        
         
         
               
        return $this->render('admin/index.html.twig',[
            'numberOrders'=>$numberOrders,
            'numberUsers'=>$numberUsers,
            'numberProducts'=>$numberProducts,
            'numberSubscribers'=>$numberSubscribers,
            'numbersubscribersNotPaid'=> json_decode($numbersubscribersNotPaid),
            'numbersubscribersPaid'=> json_decode($numbersubscribersPaid),
            'numberOrders'=> $numberOrders,
            'numberSellers'=> $numberSellers,
            'numberCustomers'=>$numberCustomers,
            'totalSubscriptionRevenu'=>$totalSubscriptionRevenu,
            'totalOrdersRevenu'=> $totalOrdersRevenue,
            

        ]);
    }

    #[Route('/orders', name:'orders')]
    public function index(EntityManagerInterface $em): Response

    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
        // seletiononer toutes les commandes de l'utilisateur
        $orders = $em->getRepository(Orders::class)->findAll();
               
        return $this->render('admin/orders/index.html.twig',[
            'orders'=>$orders
        ]);
    }

    #[Route('/orders/withdraw/{reference}', name:'orders_withdraw')]
    public function withdraw($reference,EntityManagerInterface $em): RedirectResponse

    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
            
        }

        $order = $em->getRepository(Orders::class)->findOneBy(['reference'=>$reference]);

        $sellerId = $order->getOrderDetails()->getValues()[0]->getProducts()->getUser()->getId();

        $seller = $em->getRepository(Users::class)->find($sellerId);
        

        $sellerBalance =$seller->getBalance();
       

        
        $orderTotal =$order->getTotal();
       

        if($order->isIsWithdraw()== false){
            // seletiononer  la commande de l'utilisateur
        $sellerBalance -= ($orderTotal-($orderTotal*(2/100)));
        
       $seller->setBalance($sellerBalance);
       $order->setIsWithdraw(1);
       $em->persist($seller);
       $em->flush();
        }else {
            throw new Exception("le montant a deja ete retirer");
        }

       return $this->redirectToRoute('admin_orders');
   
    }


    #[Route('/suscribers', name:'subscribers')]
    public function suscribers(SubscriptionRepository $subscriptionRepository ): Response

    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
        // seletiononer tout les abonnes
        $subscribers = $subscriptionRepository->findAll();


        return $this->render('admin/subscribers/index.html.twig',[
            'subscribers'=>$subscribers
        ]);
    }


    #[Route('/commande/details/{reference}', name: 'details')]
    public function orders(EntityManagerInterface $em,$reference): Response
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }

        $order = $em->getRepository(Orders::class)->findOneBy(['reference'=>$reference]);
        if(!$order){
            return $this->redirectToRoute('profile_index');
        }

        $data =[];

        foreach($order->getOrderDetails()->getValues() as $product){
             
           
           
            $data[]= [
                'quantity'=>$product->getQuantity(),
                'product'=>$product->getProducts()
            ];
        }

        return $this->render('admin/details.html.twig',compact('data'));
    }


    #[Route('admin/orders/update/{id}', name: 'update_status')]
    public function updateStatus(EntityManagerInterface $em, OrdersRepository $orderRepository,int $id){
       //Recuperation de l'id de la commande sur laquelle on clique
       $order= $orderRepository->find($id);
       
       //Recuperation de l'id de du statut et permutation
       if($order->getStatus()->getId() == 1){
        $statut =$em->find(Statuts::class,Statuts::TERMINE);
        $order->setStatus($statut);
        $em->persist($statut);
        $em->flush();
        
       }
       
       elseif($order->getStatus()->getId() == 2){
        $status =$em->find(Statuts::class,Statuts::ANNULE);
        $order->setStatus($status);
        $em->persist($status);
        $em->flush();
       }

       elseif($order->getStatus()->getId() == 3){
        $status =$em->find(Statuts::class,Statuts::EN_COURS);
        $order->setStatus($status);
        $em->persist($status);
        $em->flush();
    }
       
       return  $this->redirectToRoute('admin_orders');

    }
    


}