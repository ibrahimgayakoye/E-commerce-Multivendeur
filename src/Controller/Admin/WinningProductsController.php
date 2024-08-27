<?php

namespace App\Controller\Admin;

use App\Entity\Videos;
use App\Entity\Images;
use App\Entity\Products;
use App\Entity\Statuts;
use App\Entity\WinningProducts;
use App\Form\ProductsFormType;
use App\Form\SendProductFormType;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use App\Service\VideoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/winning',name:'admin_winning_products_')]
class WinningProductsController extends AbstractController
{
    #[Route('/products',name:'index')]
    public function index(ProductsRepository $productsRepository): Response
    {
        $produits = $productsRepository->findAll();
        return $this->render('admin/winning/index.html.twig', compact('produits'));
    }





    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product, SluggerInterface $slugger, EntityManagerInterface $em, Request $request, PictureService $pictureService, VideoService $videoService ): Response 
    {   

        
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

             // on recupere les videos
             $videos = $productForm->get('videos')->getData();

             foreach($videos as $video){
                // on definit le  dossier de destination
                $folder ='videos';

                // on Appelle le service d'ajouts de video

                $fichier = $videoService->add($video, $folder);

                $vid = new Videos();
                $vid->setName($fichier);
                $product->addVideo($vid);

             }
            

            // On genere le slug
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);
            $product->setIsSend(1);

            //On arrondit le prix
            //$prix = $product->getPrice() * 100;
           // $product->setPrice($prix);

            $em->persist($product);
            $em->flush();

            $this->addFlash('success','Produit modifier avec succes');
            
            // On redirige vers le menu principale

            return $this->redirectToRoute('admin_winning_products_index');
            
        }



        return $this->render('admin/winning/edit.html.twig',[
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }
    
   

    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Images $image, Request $request, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
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

    
    #[Route('/supression/video/{id}',name:'delete_video',methods:['DELETE'])]
    public function deleteVideo(Videos $video, Request $request, EntityManagerInterface $em, VideoService $videoService): JsonResponse
    {
        // on recupere le contenu de la requete
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete_video' . $video->getId(), $data['_token'])){
            // le token est valid
            // on recupere le nom de la video
            $nom = $video->getName();

            if($videoService->delete($nom, 'videos')){
                // On supprime l'image de la base de données
                $em->remove($video);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
        }

   

    #[Route('/details/{slug}', name:'details')]
    public function details(Products $product): Response
    {
       
        return $this->render('admin/winning/details.html.twig', compact('product'));
    }

    #[Route('/send_products_form/{id}', name:'send_form')]
    public function send_products_form(Request $request,EntityManagerInterface $em, $id): Response
    { 
        $product = $em->getRepository(Products::class)->find($id);
        $form = $this->createForm(SendProductFormType::class, null,[
            'action' => $this->generateUrl('admin_winning_products_send', ['id'=>$id]),
            'method'=>'POST',
        ]);
        $form->handleRequest($request);

    
        return $this->render('admin/winning/send_products.html.twig', [
           'form'=>$form->createView(),
           'product'=>$product->getId(),
           
        ]);

        
    }

    #[Route('/rejected/{id}',name:'rejected')]
    public function rejected($id,EntityManagerInterface $em): RedirectResponse
    {
        $produits = $em->getRepository(Products::class)->find($id);
        $status =$em->find(Statuts::class,Statuts::ANNULE);
        $produits->setStatuts($status);
        $em->persist($produits);
        $em->flush();
               
        return $this->redirectToRoute('admin_products_index');
    }

    #[Route('/send/{id}', name:'send', methods:['POST'])]
    public function send_products(Request $request,EntityManagerInterface $em, $id ): RedirectResponse
    { 

        $product = $em->getRepository(Products::class)->find($id);
         
        
        $form = $this->createForm(SendProductFormType::class,null);

        $form->handleRequest($request);

            
                $countries = $form->get('countries')->getData();
                $packs = $form->get('packs')->getData();
                
                $winningproduct = new WinningProducts();
                $winningproduct->setName($product->getName());
               foreach($countries as $countrie){
                $winningproduct->addCountry($countrie);
               }
    
               foreach($packs as $pack){
                $winningproduct->addPack($pack);
               }
    
               $winningproduct->setProduct($product);
               $status =$em->find(Statuts::class,Statuts::TERMINE);
               $product->setStatuts($status);
              
    
                
    
                $em->persist($winningproduct);
                $em->flush();

                $this->addFlash('success', 'Votre produit a bien ete envoyer');
          
       
        //obtenir la liste des packs des abonnes

    
        return $this->redirectToRoute('admin_winning_products_index');

        
    }

    


    

   




    

    
}