<?php

namespace App\Controller;

use App\Entity\Countries;
use App\Entity\Pack;
use App\Entity\Subscription;
use App\Form\SubscriptionType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/subscriptions', name: 'app_subscriptions_')]
class SubscriptionController extends AbstractController
{
    #[Route('/{id}', name: 'add')]
    public function add( EntityManagerInterface $em, $id): Response
    {

       

       $countries = $em->getRepository(Countries::class)->findAll();

       $pack = $em->getRepository(Pack::class)->findOneBy(['id'=>$id]);

      


        $form = $this->createForm(SubscriptionType::class, null, [
            'user' => $this->getUser(),
            'country'=> $countries
        ]);


        return $this->render('subscriptions/index.html.twig', [
            'form' => $form->createView(),
            'pack' => $pack,
            
        ]);
    }

    #[Route('/checkout/{id}', name: 'checkout', methods: ['POST'])]
    public function prepareSubscription(Request $request, EntityManagerInterface $em, $id): Response
    {

        
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');  
        }


        $pack = $em->getRepository(Pack::class)->findOneBy(['id'=>$id]);
        $countries = $em->getRepository(Countries::class)->findAll();



       
        $form = $this->createForm(SubscriptionType::class, null, [
            'user' => $this->getUser(),
            'country'=> $countries
        ]);


       

       $date = new DateTime();
       $duration = $pack->getDuration();
       $validity = $date->modify('+'.$duration.' '.'month');
       
       

       

        $form->handleRequest($request);
        $paymentMethod = $form->get('payment')->getData();

        $country1=  $form->get('country1')->getData();
        $country2 =  $form->get('country2')->getData();



       
      
        $subscription = new Subscription();
        $subscription->setReference(uniqid());
        $subscription->setUsers($this->getUser());
        $subscription->setIsPaid(0);
        $subscription->setValidity($validity);
        $subscription->setMethod($paymentMethod);
        $subscription->setPack($pack);

        if($pack->getName()=="Pack Silver"){
            $subscription->addCountry($country1);
        }
        if($pack->getName()=="Pack Silver /6"){
            $subscription->addCountry($country1);
        }
        if($pack->getName()=="Pack Gold"){
            $subscription->addCountry($country1);
            $subscription->addCountry($country2);
        }
        if($pack->getName()=="Pack Gold / 6"){
            $subscription->addCountry($country1);
            $subscription->addCountry($country2);
        }

        if($pack->getName()=="Pack Platinium"){
            foreach($countries as $country){
                $subscription->addCountry($country);
            }
        }

        if($pack->getName()=="Pack Platinium / 6"){
            foreach($countries as $country){
                $subscription->addCountry($country);
            }
        }

        

        
        

        $em->persist($subscription);
        $em->flush();


        return $this->render('subscriptions/recap.html.twig',[
            'form'=>$form->createView(),
            'packName'=>$subscription->getPack()->getName(),
            'packAmount'=>$subscription->getPack()->getAmount(),
            'method'=>$subscription->getMethod(),
            'reference'=>$subscription->getReference()


           
        ]);
       
       

    }

   
}
