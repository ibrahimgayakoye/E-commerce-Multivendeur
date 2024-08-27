<?php


namespace App\Controller\Admin;

use App\Entity\Countries;
use App\Form\CoutriesFormType;
use App\Repository\CountriesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/countries', name:'admin_countries_')]
class CountriesController extends AbstractController
{
    #[Route('/', name:'index')]
    public function index(CountriesRepository $countriesRepository): Response
    {
        $countries = $countriesRepository->findAll();

        return $this->render('admin/countries/index.html.twig', compact('countries'));
    }

    #[Route('/ajout', name:'add')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {

        // on cree une categorie
        $countries = new Countries();

        
        //On cree le formulaire
        $countrieForm = $this->createForm(CoutriesFormType::class,$countries);

        // on traite la requete du formulaire
        $countrieForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($countrieForm->isSubmitted() && $countrieForm->isValid()){
           

            $em->persist($countries);
            $em->flush();

            $this->addFlash('success','pays ajoute avec success');
            return $this->redirectToRoute('admin_countries_index');

        }

    
          return $this->render('admin/countries/add.html.twig', compact('countrieForm'));
    }



    #[Route('/edition/{id}', name:'edit')]
    public function edit(Countries $country,Request $request,EntityManagerInterface $em): Response
    {

        //On cree le formulaire
        $countrieForm = $this->createForm(CoutriesFormType::class,$country);

        // on traite la requete du formulaire
        $countrieForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($countrieForm->isSubmitted() && $countrieForm->isValid()){
           
            


            $em->persist($country);
            $em->flush();

            $this->addFlash('success',' Pays modifier avec succes');
            return $this->redirectToRoute('admin_countries_index');



        }
          return $this->render('admin/countries/edit.html.twig', compact('countrieForm'));
    }


    #[Route('/supprimer/{id}', name:'delete')]
    public function delete(Countries $country,EntityManagerInterface $em): Response
    {
        $em->remove($country);
        $em->flush();
        return $this->redirectToRoute('admin_categories_index');
    }

}