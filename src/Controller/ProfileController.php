<?php

namespace App\Controller;

use App\Entity\Images;
use App\Entity\OrderDetails;
use App\Entity\Orders;
use App\Entity\Products;
use App\Entity\Statuts;
use App\Entity\Subscription;
use App\Entity\Users;
use App\Entity\WinningProducts;
use App\Form\ProductsFormType;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use App\Repository\SubscriptionRepository;
use App\Service\PictureService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;


#[Route('/profil', name: 'profile_')]
class ProfileController extends AbstractController
{


    #[Route('/', name: 'index')]
    public function dashboard(EntityManagerInterface $em): Response
    {
     
        if(!$this->getUser()){
            return  $this->redirectToRoute('app_login');
      
        }

      

       // selection de l'abonement de l'utilisateur
       if($this->getUser()->getSubscription()){
        $subscription = $em->getRepository(Subscription::class)->findOneBy(['id'=>(string)$this->getUser()->getSubscription()->getId()]); 
        $now = new DateTime();
        if($now > $subscription->getValidity() == true){
            $subscription->setIsPaid(0);
    
            $em->persist($subscription);
            $em->flush();
         }
       }
      
      

       
       
       $sellerBalance = $em->getRepository(Users::class)->find($this->getUser())->getBalance();
       
       $sellerProducts = $em->getRepository(Products::class)->findBy(['user'=>$this->getUser()]);
       $numbersellerProducts = count($sellerProducts);
       
       $sellerProductsPending = $em->getRepository(Users::class)->findsellerProductsPending($this->getUser()->getId());
       $numbersellerProductsPending = count($sellerProductsPending);

       $sellerProductsAccepted = $em->getRepository(Users::class)->findsellerProductsAccepted($this->getUser()->getId());
       $numbersellerProductsAccepted = count($sellerProductsAccepted);

       $sellerProductsRejected = $em->getRepository(Users::class)-> findsellerProductsRected($this->getUser()->getId());
       $numbersellerProductsRejected = count($sellerProductsRejected);

       $CustermerOrders = $em->getRepository(Orders::class)->findby(['users'=>$this->getUser(),'isPaid'=>'true']);
       $numberCustomerOrders = count($CustermerOrders);

       $CustermerOrdersPending = $em->getRepository(Orders::class)->findby(['users'=>$this->getUser(),'isPaid'=>'true','status'=>'1']);
       $numberCustomerOrdersPending = count($CustermerOrdersPending);

       $CustermerOrdersAccepted = $em->getRepository(Orders::class)->findby(['users'=>$this->getUser(),'isPaid'=>'true','status'=>'2']);
       $numberCustomerAccepted = count($CustermerOrdersAccepted);

       $CustermerOrdersRejected = $em->getRepository(Orders::class)->findby(['users'=>$this->getUser(),'isPaid'=>'true','status'=>'3']);
       $numberCustomerRejected = count($CustermerOrdersRejected);

        return $this->render('profile/index.html.twig',[
            'sellerBalance'=>$sellerBalance,
            'numbersellerProductsAccepted'=>$numbersellerProductsAccepted,
            'numbersellerProductsPending'=>$numbersellerProductsPending,
            'numbersellerProductsRejected'=>$numbersellerProductsRejected,
            'numbersellerProducts'=>$numbersellerProducts,
            'numberCustomerOrders' =>$numberCustomerOrders,
            'numberCustomerOrdersPending'=>$numberCustomerOrdersPending,
            'numberCustomerAccepted'=>$numberCustomerAccepted,
            'numberCustomerRejected' =>$numberCustomerRejected,
            
        ]);
    }


    #[Route('/orders', name: 'orders')]
    public function user_orders(EntityManagerInterface $em,OrdersRepository $ordersRepository): Response
    {
     
        if(!$this->getUser()){
            return  $this->redirectToRoute('app_login');
        }
       
        // seletiononer toutes les commandes de l'utilisateur
        $orders = $em->getRepository(Orders::class)->findBy(['users'=>$this->getUser()]);
        // seller products
        $sellerProducts = $em->getRepository(Products::class)->findby(['user'=>$this->getUser()]);
        
         //selectionner les commandes les commandes qui s'afficherons chez le vendeur
        $sellerOrderDetails = $em->getRepository(OrderDetails::class)->findBy(['products'=>$sellerProducts]);

        $sellerOrders = $em->getRepository(\App\Entity\Orders::class)->find(119);
       
        
        // initialisation du tableau pour recuperer les commandes a partir des details
        $datas = [];
        $sellerOrders = [];
        $datas_2 = [];
        $datas_3 =[];

       // recuperer l'id des commandes a partir des details
        foreach($sellerOrderDetails as $s){
            $datas[]=(int)($s->getOrders()->getId());
        }

       // recuperer l'id des commandes dans un tableau
        foreach($datas as $data){
            
            $sellerOrders[] = $ordersRepository->findby(['id'=>(int)$data]);
        }
              
        //recucuperer les commandes dans le tableau $datas_2
        foreach($sellerOrders as $sellerOrder){
            
            $datas_2[] = $sellerOrder[0];
        }



        return $this->render('profile/orders.html.twig',[
            'orders'=>$orders,
            'sellerOrders'=> $datas_2
            
        ]);
    }


    #[Route('/commande/details/{reference}', name: 'details')]
    public function user_orders_details(EntityManagerInterface $em,$reference): Response
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

        return $this->render('profile/details.html.twig',compact('data'));
    }


    #[Route('/products',name:'products_index')]
    public function user_products(ProductsRepository $productsRepository,EntityManagerInterface $em): Response
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('app_login');
        }
        // seletiononer toutes les produits du vendeur
        $produits = $em->getRepository(Products::class)->findBy(['user'=>$this->getUser()],['created_at'=>'DESC']);

        return $this->render('profile/products/index.html.twig', compact('produits'));
    }

    #[Route('/ajout', name: 'products_add')]
    public function product_create(Request $request,EntityManagerInterface $em, SluggerInterface $slugger,PictureService $pictureService ): Response 
    {   
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
        $this->denyAccessUnlessGranted('ROLE_SELLER');

        //On cree un "nouveau produit"
        $product = new Products();


        //On cree le formulaire 
        $productForm = $this->createForm(ProductsFormType::class, $product);

        // on traite la requete du formulaire
        $productForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($productForm->isSubmitted() && $productForm->isValid()){

             // on recupere les images

             $images = $productForm->get('images')->getData();

             foreach($images as $image){
                // on definit le dossier de destination
                $folder = 'products';

                // on appelle le service d'ajouts
                $fichier= $pictureService->add($image,$folder,300,300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);

                
             }

             
            // On genere le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //On arrondit le prix
            $prix = ceil($product->getPrice());
            $product->setUser($this->getUser());
            $status =$em->find(Statuts::class,Statuts::EN_COURS);
            $product->setStatuts($status);

           

            $em->persist($product);
            $em->flush();

            $this->addFlash('success','Produit ajoute avec succes');
            
            // On redirige vers le menu

            return $this->redirectToRoute('profile_products_index');
            
        }




        return $this->render('profile/products/add.html.twig',[
            'productForm' => $productForm->createView()
        ]);
    }

    #[Route('/edition/{id}', name: 'products_edit')]
    public function product_edit(Products $product, SluggerInterface $slugger, EntityManagerInterface $em, Request $request, PictureService $pictureService ): Response 
    {   
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }

        // On verifie si l'utilisateur peut editer avec Le Voter
        $this->denyAccessUnlessGranted("ROLE_SELLER");

        
        $prix = ceil($product->getPrice());
        $product->setPrice($prix);

        // On cree le formulaire
        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

         //On verifie si le formulaire soumis ET Valide
         if($productForm->isSubmitted() && $productForm->isValid()){
             
            $images = $productForm->get('images')->getData();

             foreach($images as $image){
                // on definit le dossier de destination
                $folder = 'products';

                // on appelle le service d'ajouts
                $fichier= $pictureService->add($image,$folder,300,300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);

                
             }
            

            // On genere le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            //On arrondit le prix
            //$prix = $product->getPrice() * 100;
           // $product->setPrice($prix);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success','Produit modifier avec succes');
            
            // On redirige vers le menu principale

            return $this->redirectToRoute('profile_products_index');
            
        }



        return $this->render('profile/products/edit.html.twig',[
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    #[Route('/suppression/image/{id}', name: 'products_delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('app_login');
        }
        // On récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])){
            // Le token csrf est valide
            // On récupère le nom de l'image
            $nom = $image->getName();

            if($pictureService->delete($nom, 'products', 300, 300)){
                // On supprime l'image de la base de données
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }

    #[Route('/winning_products', name: 'winning_products')]
    public function wining_products(EntityManagerInterface $em, SubscriptionRepository $subscriptionRepository): Response
    {
     
        if(!$this->getUser()){
            return  $this->redirectToRoute('app_login');
        }
       $userId = $this->getUser()->getSubscription()->getId();

       // selection de l'abonement de l'utilisateur
       $subscrition = $subscriptionRepository->findOneBy(['id'=>(string)$userId]);

       // selection des pays de l'abonnement
       $subcriptionCountries = $subscrition->getCountries()->getValues();
       // seletiononer tous les produits gagnants
        $winningproducts = $em->getRepository(WinningProducts::class)->findAll();
         $countries=[];
        
         $winningProductsSamePack =[];
         $data=[];
         foreach($winningproducts as $winningproduct){
           $pack = array_unique($winningproduct->getPacks()->getValues());
           if(in_array($subscrition->getPack(),$pack)){
              $winningProductsSamePack[]= $winningproduct;        } 
           
  }

  foreach($winningProductsSamePack as $winningProductSamePack){
    $countries = array_unique($winningProductSamePack->getCountries()->getValues());
    foreach($subcriptionCountries as $subcriptionCountrie){
        if(in_array($subcriptionCountrie,$countries)){
            $data[]= ($winningProductSamePack);
         }
    }
     
  }

  

       $winningproducts = array_unique($data);

        return $this->render('profile/winning_products.html.twig',[
            'subscription'=>$subscrition,
            'winningproducts'=>$winningproducts
            
        ]);
    }
    
}
