<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Users;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentController extends AbstractController
{
   //PAYPAL
  public function getPaypalClient(): PayPalHttpClient
    {
      $clientId = "AQBaSXh1vyfPXbhBfhEu-fa0YvtDC9xC9PBxQuH3Yap9AiL287b9sf7KHDeHtxeEQIr0lUI3OXGc93sL";
      $clientSecret = "ELpbyJdTX2t_now-NW9E50LPgZOaTLUe7cX8cPFvCERpCwe6BLpGjv9xO8VCpXOP0HC4XwV0bhsY95wo";
      $environment = new SandBoxEnvironment($clientId, $clientSecret);
      return new PayPalHttpClient($environment);
    }

   

    #[Route('/order/create-session-paypal/{reference}', name:'payment_paypal', methods:['POST'])]
    public function createSessionPaypal(EntityManagerInterface $em, $reference, UrlGeneratorInterface $url): RedirectResponse
    {
       $order = $em->getRepository(Orders::class)->findOneBy(['reference'=>$reference]);

       if(!$order){
           return $this->redirectToRoute('cart_index');
       }

       $items = [];
       $itemTotal = 0;

       foreach($order->getOrderDetails()->getValues() as $product){
       
          $items[] = [
             'name'=> $product->getProducts()->getName(),
             'quantity'=>$product->getQuantity(),
             'unit_amount'=>[
              'value'=> $product->getPrice(),
              'currency_code'=> 'EUR'
             ]
    ];

    $itemTotal += $product->getPrice() * $product->getQuantity();

    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body =[
      'intent'=> 'CAPTURE',
      'purchase_units'=>[
         [
            'amount'=>[
              'currency_code'=> 'EUR',
              'value'=> $itemTotal,
              'breakdown'=>[
                'item_total'=> [
                  'currency_code'=>'EUR',
                  'value'=> $itemTotal
                ]
              ]
                ],
                'items'=>$items
         ]
              ],
              'application_context' =>[
                'return_url'=> $url->generate('payment_success_paypal',
                ['reference'=>$order->getReference()],
                UrlGeneratorInterface:: ABSOLUTE_URL
              ),
              'cancel_url'=> $url->generate(
                'payment_error',
                ['reference'=>$order->getReference()],
                  UrlGeneratorInterface::ABSOLUTE_URL
                )
              ]
        ];

    $client = $this->getPaypalClient();
    $response = $client->execute($request);

    if($response->statusCode != 201){
        return $this->redirectToRoute('cart_index');
    }
    
    $approvalLink = '';
    foreach($response->result->links as $link) {
       if($link->rel === 'approve'){
          $approvalLink = $link->href;
          break;
       }
    }

    if(empty($approvalLink)){
        return $this->redirectToRoute('cart_index');
    }

    $order->setPaypalOrderId($response->result->id);

    $em->flush();

    return new RedirectResponse($approvalLink);


  }

    }


    #[Route('/order/success-paypal/{reference}', name:'payment_success_paypal')]
    public function successPaypal($reference, EntityManagerInterface $em, RequestStack $requestStack): Response
    {
      $order = $em->getRepository(Orders::class)->findOneBy(['reference'=> $reference]);
        
        if(!$order || $order->getUser() !== $this->getUser()){
            return $this->redirectToRoute('cart_index');
        }

        $panier = $requestStack->getSession()->get('panier',[]);

        if(!$order->isIsPaid()){
          
           $requestStack->getSession()->remove('panier');
           $order->setIsPaid(1);
           $em->flush();
        }

      return $this->render('orders/success.html.twig',[
        'order'=> $order
      ]);
    }


    #[Route('/order/error-paypal/{reference}', name:'payment_error_paypal')]
    public function errorPaypal(EntityManagerInterface $em, $reference): Response
    {
      $order = $em->getRepository(Orders::class)->findOneBy(['reference'=> $reference]);
      if(!$order || $order->getUser() !== $this->getUser()){
        return $this->redirectToRoute('cart_index');
    }

      return $this->render('orders/error.html.twig');
    }

    #[Route('/order/cash-payment/{reference}', name:'payment_cash')]

    public function CashPayment($reference, EntityManagerInterface $em, RequestStack $requestStack): Response
    {
       
       
      $order = $em->getRepository(Orders::class)->findOneBy(['reference'=>$reference]);

      if(!$order){
          return $this->redirectToRoute('cart_index');
      }

      $panier = $requestStack->getSession()->get('panier',[]);

        if(!$order->isIsPaid()){
           $requestStack->getSession()->remove('panier');
           $order->setIsPaid(0);
           $em->flush();
        }

      return $this->render('orders/success_cash.html.twig',[
        'order'=> $order
      ]);
        
        
    }

    //Stripe

    #[Route('/order/create-session-stripe/{reference}', name:'payment_stripe')]

    public function stripeCheckout($reference, EntityManagerInterface $em, UrlGeneratorInterface $url): RedirectResponse
    {
        $productStripe =[];
        $order = $em->getRepository(Orders::class)->findOneBy(['reference'=> $reference]);
        
        if(!$order){
            return $this->redirectToRoute('cart_index');
        }

       
        foreach($order->getOrderDetails()->getValues() as $product){
          $productData = $em->getRepository(Products::class)->findOneBy(['name'=>$product->getProducts()->getName()]);
          
         
            $productStripe[] = [
               'price_data'=>[
                'currency'=>'mad',
                'unit_amount'=>$productData->getPrice()*100,
                'product_data'=>[
                   'name'=> $product->getProducts()->getName()
                    ]
                ],
          'quantity'=> $product->getQuantity()
      ];

  }

       
        Stripe::setApiKey('sk_test_51Jw8FlKNgWxmv3tU6OWDt9pGbKWmPcVtrKqd0vpms8qUcBuohW38QpEmGPBaMxpN5cFWmW3VlxAItnv7zfN25IvH00qfs1eHsr');
        
        
        
        $checkout_session = Session::create([
          'customer_email'=> $this->getUser()->getEmail(),
           'payment_method_types' => ['card'],
          'line_items' => [[
             $productStripe
          ]],
          'mode' => 'payment',
          'success_url' => $url->generate('payment_success',[
            'reference' => $order->getReference()
          ],UrlGeneratorInterface::ABSOLUTE_URL),
          'cancel_url' => $url->generate('payment_error',
          ['reference'=>$order->getReference()],
            UrlGeneratorInterface::ABSOLUTE_URL
          )
        ]);

        $order->setStripSessionId((string)$checkout_session->id);

        $em->flush();


        return new RedirectResponse($checkout_session->url);
        
        
    }

    #[Route('/order/success/{reference}', name:'payment_success')]
    public function StripeSuccess($reference, EntityManagerInterface $em,RequestStack $requestStack): Response
    {
      $order = $em->getRepository(Orders::class)->findOneBy(['reference'=> $reference]);
        
      $panier = $requestStack->getSession()->get('panier',[]);

      if(!$order->isIsPaid()){
        $order->setIsPaid(1);
         $em->flush();
         $requestStack->getSession()->remove('panier');

      }

      if($order->isIsPaid()){
        foreach($order->getOrderDetails()->getValues() as $product){

          $userBalance= $product->getProducts()->getUser()->getBalance();
          $userBalance += (($product->getProducts()->getPrice()*$product->getQuantity())-($product->getProducts()->getPrice()*$product->getQuantity()*(2/100)));
      }
     
      //SellerId
      $userId =  $product->getProducts()->getUser()->getId();
     // find the Seller
      $user =$em->getRepository(Users::class)->find($userId);
      $user->setBalance($userBalance);
      $em->flush();
      }

    return $this->render('orders/success.html.twig',[
      'order'=> $order
    ]);
    }

    #[Route('/order/error/{reference}', name:'payment_error')]
    public function StripeError($reference, EntityManagerInterface $em): Response
    {
      $order = $em->getRepository(Orders::class)->findOneBy(['reference'=> $reference]);
        
        if(!$order || $order->getUser() !== $this->getUser()){
            return $this->redirectToRoute('cart_index');
        }
      return $this->render('orders/error.html.twig');
    }

    

    

    



}