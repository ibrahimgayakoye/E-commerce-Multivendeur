<?php


namespace App\Controller\Admin;

use App\Entity\Countries;
use App\Entity\Pack;
use App\Form\CoutriesFormType;
use App\Form\PackFormType;
use App\Repository\CountriesRepository;
use App\Repository\PackRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/packs', name:'admin_packs_')]
class PacksController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(PackRepository $packRepository): Response
    {
        $packs = $packRepository->findAll();

        return $this->render('admin/packs/index.html.twig', compact('packs'));
    }

    #[Route('/ajout', name:'add')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

        // on cree une categorie
        $pack = new Pack();

        
        //On cree le formulaire
        $packForm = $this->createForm(PackFormType::class,$pack);

        // on traite la requete du formulaire
        $packForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($packForm->isSubmitted() && $packForm->isValid()){
           

            $em->persist($pack);
            $em->flush();

            $this->addFlash('success','pack ajoute avec success');
            return $this->redirectToRoute('admin_packs_index');



        }

    
          return $this->render('admin/packs/add.html.twig', compact('packForm'));
    }



    #[Route('/edition/{id}', name:'edit')]
    public function edit(Pack $pack,Request $request,EntityManagerInterface $em): Response
    {

        //On cree le formulaire
        $packForm = $this->createForm(PackFormType::class,$pack);

        // on traite la requete du formulaire
        $packForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($packForm->isSubmitted() && $packForm->isValid()){
           
            


            $em->persist($pack);
            $em->flush();

            $this->addFlash('success',' Pays modifier avec succes');
            return $this->redirectToRoute('admin_packs_index');



        }

    
          return $this->render('admin/packs/edit.html.twig', compact('packForm'));
    }


   // #[Route('/supprimer/{id}', name:'delete')]
    //public function delete(Countries $country,EntityManagerInterface $em): Response
    //{
      //  $em->remove($country);
        //$em->flush();
        //return $this->redirectToRoute('admin_categories_index');
    //}

}