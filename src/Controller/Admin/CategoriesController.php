<?php


namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Form\CategoriesFormType;
use App\Repository\CategoriesRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories', name:'admin_categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([],['categoryOrder' =>'asc']);

        return $this->render('admin/categories/index.html.twig', compact('categories'));
    }

    #[Route('/ajout', name:'add')]
    public function create(Request $request, SluggerInterface $sluggerInterface, EntityManagerInterface $em): Response
    {

        // on cree une categorie
        $categorie = new Categories();

        
        //On cree le formulaire
        $categorieForm = $this->createForm(CategoriesFormType::class,$categorie);

        // on traite la requete du formulaire
        $categorieForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($categorieForm->isSubmitted() && $categorieForm->isValid()){
           
            // On genere le slug
            $slug = $sluggerInterface->slug($categorie->getName());
            $categorie->setSlug($slug);


            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success','Produit ajoute avec succes');
            return $this->redirectToRoute('admin_categories_index');



        }

    
          return $this->render('admin/categories/edit.html.twig', compact('categorieForm'));
    }



    #[Route('/edition/{id}', name:'edit')]
    public function edit(Categories $categorie,Request $request, SluggerInterface $sluggerInterface, EntityManagerInterface $em): Response
    {

        //On cree le formulaire
        $categorieForm = $this->createForm(CategoriesFormType::class,$categorie);

        // on traite la requete du formulaire
        $categorieForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($categorieForm->isSubmitted() && $categorieForm->isValid()){
           
            // On genere le slug
            $slug = $sluggerInterface->slug($categorie->getName());
            $categorie->setSlug($slug);


            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success',' Categorie modifier avec succes');
            return $this->redirectToRoute('admin_categories_index');



        }

    
          return $this->render('admin/categories/edit.html.twig', compact('categorieForm'));
    }



    #[Route('/supprimer/{id}', name:'delete')]
    public function delete(Categories $categorie,EntityManagerInterface $em): Response
    {
        $em->remove($categorie);
        $em->flush();
        return $this->redirectToRoute('admin_categories_index');
    }

    




    
    

}