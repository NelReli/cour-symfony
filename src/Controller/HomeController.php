<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CategoryRepository;
use App\Entity\Produit;


final class HomeController extends AbstractController
{
        #[Route('/', name: 'home')]
        public function index(ProduitRepository $produitRepository, CategoryRepository $categoryRepository): Response
        {
            return $this->render('home/index.html.twig', [
                'produits' => $produitRepository->findAll(),
                'coucou' => $categoryRepository->findAll(),
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
