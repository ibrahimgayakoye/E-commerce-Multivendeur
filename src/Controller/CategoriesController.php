<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/{slug}', name: 'list')]
    public function list(Categories $category, ProductsRepository $productsRepository, Request $request, CategoriesRepository $categoriesRepository): Response
    {   
        //les categories
        $categories = $categoriesRepository->findAll();

        // On va chercher le numero de page dans l'url
        $page = $request->query->getInt('page',1);

        // On va chercher la liste des produits de la categorie
        $products = $productsRepository->findProductsPaginated($page,$category->getSlug(),1);
       
        return $this->render('categories/list.html.twig',[
            'category'=> $category,
            'products'=> $products,
            'categories'=> $categories
        ]
    );
    }

    
}
