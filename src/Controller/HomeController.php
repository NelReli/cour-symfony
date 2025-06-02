<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Entity\Produit;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
        #[Route('/', name: 'home')]
        public function index(Request $request, ProduitRepository $produitRepository, CategoryRepository $categoryRepository): Response
        {

            $produits=$produitRepository->findAll();
            dump($produits);
            // methode find($id)
            $oneProduit=$produitRepository->find(1);
            dump($oneProduit);

            $selectedProduit=null;
            if($request->isMethod('POST')){ // si form est POST
                $formType=$request->get('form'); 
                if($formType==='select_produit'){ // le name form dans le formulaire à une valeur de select_produit
                    $idProduit=$request->get('produit'); // récuère l'id du produit
                    $selectedProduit = $produitRepository->find($idProduit);
                }
            }

            return $this->render('home/index.html.twig', [
                'produits' => $produitRepository->findAll(),
                'coucou' => $categoryRepository->findAll(),
                'oneProduit' => $oneProduit,
                'selectedProduit' => $selectedProduit
            ]);
        }


        #[Route('/produit/{id}', name: 'show', methods: ['GET'])]
        public function show(Produit $produit):Response
        {
            return $this->render('Show/index.html.twig', [
                'produit' => $produit,
            ]);
        }


        #[Route('/apropos', name: 'apropos')]
        public function apropos(): Response
        {
            return $this->render('apropos/index.html.twig');
        }

}
