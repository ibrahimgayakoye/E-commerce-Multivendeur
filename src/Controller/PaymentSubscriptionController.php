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
use App\Entity\Subscription;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentSubscriptionController extends AbstractController
{
   //PAYPAL
  public function getPaypalClient(): PayPalHttpClient
    {
      $clientId = "AQBaSXh1vyfPXbhBfhEu-fa0YvtDC9xC9PBxQuH3Yap9AiL287b9sf7KHDeHtxeEQIr0lUI3OXGc93sL";
      $clientSecret = "ELpbyJdTX2t_now-NW9E50LPgZOaTLUe7cX8cPFvCERpCwe6BLpGjv9xO8VCpXOP0HC4XwV0bhsY95wo";
      $environment = new SandBoxEnvironment($clientId, $clientSecret);
      return new PayPalHttpClient($environment);
    }

   

    #[Route('/subscription/create-session-paypal/{reference}', name:'payment_paypal_subscription', methods:['POST'])]
    public function createSessionPaypal(EntityManagerInterface $em, $reference, UrlGeneratorInterface $url): RedirectResponse
    {
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);

       if(!$subscription){
           return $this->redirectToRoute('app_main');
       }

       $items = [];
      

       
          $items[] = [
             'name'=> $subscription->getPack()->getName(),
             'quantity'=>'1',
             'unit_amount'=>[
              'value'=> $subscription->getPack()->getAmount(),
              'currency_code'=> 'EUR'
             ]
    ];

   

    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body =[
      'intent'=> 'CAPTURE',
      'purchase_units'=>[
         [
            'amount'=>[
              'currency_code'=> 'EUR',
              'value'=> $subscription->getPack()->getAmount()*100,
              'breakdown'=>[
                'item_total'=> [
                  'currency_code'=>'EUR',
                  'value'=> $subscription->getPack()->getAmount()*100
                ]
              ]
                ],
                'items'=>$items
         ]
              ],
              'application_context' =>[
                'return_url'=> $url->generate('payment_subscription_success_paypal',
                ['reference'=>$subscription->getReference()],
                UrlGeneratorInterface:: ABSOLUTE_URL
              ),
              'cancel_url'=> $url->generate(
                'payment_subscription_error_paypal',
                ['reference'=>$subscription->getReference()],
                  UrlGeneratorInterface::ABSOLUTE_URL
                )
              ]
        ];

    $client = $this->getPaypalClient();
    $response = $client->execute($request);

    if($response->statusCode != 201){
        return $this->redirectToRoute('app_main');
    }
    
    $approvalLink = '';
    foreach($response->result->links as $link) {
       if($link->rel === 'approve'){
          $approvalLink = $link->href;
          break;
       }
    }

    if(empty($approvalLink)){
        return $this->redirectToRoute('');
    }

    $subscription->setPaypalOrderId($response->result->id);

    $em->flush();

    return new RedirectResponse($approvalLink);


  

    }


    #[Route('/subscription/success-paypal/{reference}', name:'payment_subscription_success_paypal')]
    public function successPaypal($reference, EntityManagerInterface $em, RequestStack $requestStack): Response
    {
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);
        
        
       

        if(!$subscription->isIsPaid()){
           $subscription->setIsPaid(1);
           $em->flush();
           
        }

       

      

      return $this->render('orders/success.html.twig',[
        'subscription'=> $subscription
      ]);
    }


    #[Route('/subscription/error-paypal/{reference}', name:'payment_subscription_error_paypal')]
    public function errorPaypal(EntityManagerInterface $em, $reference): Response
    {
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);
      if(!$subscription || $subscription->getUser() !== $this->getUser()){
        return $this->redirectToRoute('app_login');
    }

      return $this->render('subscrptions/error.html.twig');
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

    #[Route('/subscription/create-session-stripe/{reference}', name:'payment_stripe_subscription')]

    public function stripeCheckout($reference, EntityManagerInterface $em, UrlGeneratorInterface $url): RedirectResponse
    {
        $productStripe =[];
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);
        
        if(!$subscription){
            return $this->redirectToRoute('app_login');
        }

       
        
          
         
            $productStripe[] = [
               'price_data'=>[
                'currency'=>'mad',
                'unit_amount'=>$subscription->getPack()->getAmount()*100,
                'product_data'=>[
                   'name'=> $subscription->getPack()->getName()
                    ]
                ],
          'quantity'=> '1'
      ];

  

       
        Stripe::setApiKey('sk_test_51Jw8FlKNgWxmv3tU6OWDt9pGbKWmPcVtrKqd0vpms8qUcBuohW38QpEmGPBaMxpN5cFWmW3VlxAItnv7zfN25IvH00qfs1eHsr');
        
        
        
        $checkout_session = Session::create([
          'customer_email'=> $this->getUser()->getEmail(),
           'payment_method_types' => ['card'],
          'line_items' => [[
             $productStripe
          ]],
          'mode' => 'payment',
          'success_url' => $url->generate('payment_subscription_success',[
            'reference' => $subscription->getReference()
          ],UrlGeneratorInterface::ABSOLUTE_URL),
          'cancel_url' => $url->generate('payment_subscription_error',
          ['reference'=>$subscription->getReference()],
            UrlGeneratorInterface::ABSOLUTE_URL
          )
        ]);

        $subscription->setStripeSessionId((string)$checkout_session->id);

        $em->flush();


        return new RedirectResponse($checkout_session->url);
        
        
    }

    #[Route('/subscription/success/{reference}', name:'payment_subscription_success')]
    public function StripeSuccess($reference, EntityManagerInterface $em,RequestStack $requestStack): RedirectResponse
    {
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);
        
       $pack =$subscription->getPack();

      if(!$subscription->isIsPaid()){
         $subscription->setIsPaid(1);
         $this->getUser()->setPack($pack);
         $em->flush();
         return $this->redirectToRoute('profile_index');
        

      }

      return $this->redirectToRoute('profile_index');

   
    }

    #[Route('/subscription/error/{reference}', name:'payment_subscription_error')]
    public function StripeError($reference, EntityManagerInterface $em): Response
    {
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['reference'=> $reference]);
        
        if(!$subscription || $subscription->getUser() !== $this->getUser()){
            return $this->redirectToRoute('app_main');
        }
      return $this->render('subscriptions/error.html.twig');
    }


}