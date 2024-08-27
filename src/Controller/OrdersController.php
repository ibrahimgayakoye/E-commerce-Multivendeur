<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Addresses;
use App\Entity\OrderDetails;
use App\Entity\Statuts;
use App\Form\AddresseType;
use App\Form\OrderType;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Faker\Provider\ar_EG\Address;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commandes', name: 'app_orders_')]
class OrdersController extends AbstractController
{
    #[Route('/ajout', name: 'add')]
    public function add(SessionInterface $session, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);

        if ($panier === []) {
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('main');
        }

        // On initialise des variables
        $data = [];
        $total = 0;

        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);

            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];

            $total += $product->getPrice() * $quantity;
        }


        $form = $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);


        return $this->render('orders/index.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
            'total' => $total
        ]);
    }

    #[Route('/prepare', name: 'prepare', methods: ['POST'])]
    public function prepareOrder(Request $request, EntityManagerInterface $em, SessionInterface $session, ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

       

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $panier = $session->get('panier', []);

        if ($panier === []) {
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('main');
        }


        $form =  $this->createForm(OrderType::class, null, [
            'user' => $this->getUser()
        ]);

        $form->handleRequest($request);
        $delivery = $form->get('addresses')->getData();
         if($delivery == null){
            throw new Exception('Veuillez ajouter une addresse');
         }

        $deliveryForOrder = $delivery->getFirstname() . ' ' . $delivery->getLastname();
        $deliveryForOrder .= '</br>' . $delivery->getPhone();
        if ($delivery->getCompany()) {
            $deliveryForOrder .= ' - ' . $delivery->getCompany();
        }
        $deliveryForOrder .= '</br>' . $delivery->getAddress();
        $deliveryForOrder .= '</br>' . $delivery->getZipCode() . ' - ' . $delivery->getCity();
        $deliveryForOrder .= '</br>' . $delivery->getCountry();

        $paymentMethod = $form->get('payment')->getData();
       
        $order = new Orders();
        $order->setReference(uniqid());
        $order->setUsers($this->getUser());
        $order->setIsPaid(0);
        $order->setDelivery($deliveryForOrder);
        $order->setMethod($paymentMethod);



        $data =[];
        $total = 0;
        // On parcours le panier pour creer les details de la commande
        foreach ($panier as $item => $quantity) {
            $orderDetails = new OrderDetails();

            // On va chercher le produit
            $product = $productsRepository->find($item);
            $data[] = [
                'product' => $product,
                'quantity' => $quantity
            ];

            $price = $product->getPrice();

            $total += $product->getPrice() * $quantity;

            // On cree le details de la commande
            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price * $quantity);
            $orderDetails->setQuantity($quantity);
           
            $order->addOrderDetail($orderDetails);
           
        }
       

        $order->setTotal($total);
        $status =$em->find(Statuts::class,Statuts::EN_COURS);
        $order->setStatus($status);
        


        $em->persist($order);
        $em->flush();
        

        return $this->render('orders/recap.html.twig',[
            'reference'=> $order->getReference(),
            'method'=>$order->getMethod(),
            'delivery'=>$order->getDelivery(),
            'data'=> $data,
            'form'=>$form->createView(),
            'total'=> $total

        ]);
    }

   
}
