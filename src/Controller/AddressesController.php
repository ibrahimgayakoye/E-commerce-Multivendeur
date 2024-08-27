<?php
namespace App\Controller;

use App\Entity\Addresses;
use App\Form\AddressesType;
use App\Repository\AddressesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/profil/addresses', name: 'address_')]
class AddressesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(AddressesRepository $addressesRepository)
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }

        $addresses = $addressesRepository->findBy(['user'=> $this->getUser()]);
       
        


        return $this->render('addresses/index.html.twig',[
            'addresses'=>$addresses
        ]);
           
      
    }


    #[Route('/ajout', name: 'add')]
    public function ajout(Request $request, EntityManagerInterface $em)
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
        // On cree une nouvelle addresse
        $addresse = new Addresses();

        //On cree le formulaire 
        $addresseForm = $this->createForm(AddressesType::class, $addresse);

        // on traite la requete du formulaire
        $addresseForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($addresseForm->isSubmitted() && $addresseForm->isValid()){
           
            $addresse->setUser($this->getUser());
           // on recupere les image
           $em->persist($addresse);
           $em->flush();

           $this->addFlash('success','Addresse ajouté avec success');
           
           // On redirige vers le menu

           return $this->redirectToRoute('address_index');
}
        
        return $this->render('addresses/ajout.html.twig',[
            'addressForm'=>$addresseForm->createView()
        ]);
    }

    #[Route('/editition/{id}', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $em, Addresses $addresse)
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
        //On cree le formulaire 
        $addresseForm = $this->createForm(AddressesType::class, $addresse);

        // on traite la requete du formulaire
        $addresseForm->handleRequest($request);

        //On verifie si le formulaire soumis ET Valide
        if($addresseForm->isSubmitted() && $addresseForm->isValid()){

            // on recupere les image
           $em->persist($addresse);
           $em->flush();

           $this->addFlash('success','Addresse modifié avec success');
           
           // On redirige vers le menu

           return $this->redirectToRoute('address_index');
}
        
        return $this->render('addresses/edit.html.twig',[
            'addressForm'=>$addresseForm->createView()
        ]);
       
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(AddressesRepository $addressesRepository, $id,EntityManagerInterface $em)
    {
        if(!$this->getUser()){
            return  $this->redirectToRoute('main');
        }
       $addresse = $addressesRepository->find($id);
       $em->remove($addresse);
       $em->flush();
    
       //On redirige vers la page du panier
       return $this->redirectToRoute('address_index');
    }

    
}