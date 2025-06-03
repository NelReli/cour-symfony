<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(PanierRepository $repo): Response
    {
        // on recupere les paniers lies au user connecte 
        $paniers=$repo->findBy(['user'=>$this->getUser()]); 
        // findBy() est une methode de PanierRepository, cherche dans le panier toutes les ligne ou la cologne user correspond au user connecte
        // montre moi tous les paniers appartenant au user
        


        dump($this->getUser());
        dump($paniers);
        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers,
        ]);
    }


    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(Request $request, Produit $produit, EntityManagerInterface $em, PanierRepository $repo) :Response 
    {
        // Request : represente les requettes HTTP (GET POST PUT DELETE)
        // Produit : represente le produit que l'on veut ajouter au panier
        // EntityManagerInterface : represente la connexion à la base de données(UPDATE DELETE INSERT SELECT)
        // PanierRepository : permet de récupérer le panier de l'utilisateur connecté

        $user=$this->getUser(); // je recupere le user qui est connecté

        // on récupère la quantité demandé par le user via url (METHODE GET)
        // si aucune quantité n'es pas renseigné on va prendre 1 par defaut
        $quantite=max(1, $request->query->get('quantite',1));
        dump($request->query); // recuperer les renseignement envoye via url
        dump($quantite);

        // on cherche si une ligne de panier existe deja pour cet utilisateur et ce produit
        $ligne=$repo->findOneBy(['user'=>$user,'produit'=>$produit]);

        // methode findOneBy() prend un tableau en argument dans les parentheses, c'est un tableau associatif, la cle est le nom d'un champ ou d'une propriété de l'entité
        // et la valeur est la valeur de ce champs ou cette propriété

        if($ligne){
            // si une ligne existe déja dans le repository
            // on ajoute la quantité demandé à quantité existante
            $ligne->setQuantity($ligne->getQuantity()+$quantite);
            $produit->setStock($produit->getStock()-$quantite);
            
        }else{
            // sinon (le produit n'est pas encore dans le panier)
            // on cree un objet panier
            $ligne=new Panier();
            // on associe cette ligne au user connecté
            $ligne->setUser($user);
            // on associe le produit a cette ligne
            $ligne->setProduit($produit);
            // on definie la quantité
            $ligne->setQuantity($quantite);
            $produit->setStock($produit->getStock()-$quantite);


            // on indique a Doctrine qu'on veut sauvegarder cette ligne de panier 
            $em->persist($ligne); // persist cest comme prepare //

        }
        // on envoie les modifications à Doctrine qui lui envoie les requetes SQL
        $em->flush(); // flush cest comme execute //

        // message flash
        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        return $this->redirect($request->headers->get('referer')); // renvoi a la page precedente, le user va vers la page d'ou il vient
    }



    #[Route('/panier/retirer/{id}', name: 'panier_retirer')]
    public function retirer()
    {

    }


    #[Route('/panier/vider/{id}', name: 'panier_vider')]
    public function vider()
    {

    }


    #[Route('/panier/modifierQuantite/{id}', name: 'panier_modifierQuantite')]
    public function modifierQuantite()
    {

    }
}
