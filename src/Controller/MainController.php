<?php

namespace App\Controller;

use App\Controller\Data\SearchData;
use App\Form\ContactFormType;
use App\Form\SearchForm;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Service\SendMailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'main')]
    public function index(CategoriesRepository $categoriesRepository,ProductsRepository $productsRepository, Request $request): Response
    {
        // tous les produits
        $search = new SearchData();

        $form = $this->createForm(SearchForm::class,$search,[
            'action'=> $this->generateUrl('main'),
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        // On va chercher le numero de page dans l'url
        $page = $request->query->getInt('page',1);

        $products = $productsRepository->findOnlyProducts($search,$page,4);

        

        
        return $this->render('main/index.html.twig', [
            'categories'=> $categoriesRepository->findBy([],['categoryOrder'=>'asc']),
            'products' =>$products,
            'form'=> $form->createView()
        ]);
    }

    #[Route('/Apropos', name: 'apropos')]
    public function Apropos(){
         return $this->render('pages/apropos.html.twig');
    }

    #[Route('/contact', name: 'contact')]
    public function contact(Request $request, SendMailService $mailer ){

        // on cree le formulaire
        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);

        if($contactForm->isSubmitted() && $contactForm->isValid()){

            $contactFormData = $contactForm->getData();
            $subject = 'Sujet du message ' . $contactFormData['subject'];
            $mailer->send($contactFormData['email'],'ibrahimdca11@gmail.com',$subject,'contact',array($contactFormData['message']));
            $this->addFlash('success', 'Votre message a été envoyé');
            return $this->redirectToRoute('main');
           
        }
       
         
         return $this->render('pages/contact.html.twig',[
            'contactForm'=>$contactForm->createView()
         ]);
    }




}
