<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(PanierRepository $repo, SessionInterface $session): Response
    {
        dump($this->getUser());
        if($this->getUser()){
            // on recupere les paniers lies au user connecte 
            $paniers=$repo->findBy(['user'=>$this->getUser()]); 
            // findBy() est une methode de PanierRepository, cherche dans le panier toutes les ligne ou la cologne user correspond au user connecte
            // montre moi tous les paniers appartenant au user
            
            // total des paniers
            $total = 0;
    
            foreach ($paniers as $panier) {
            $total += $panier->getQuantity() * $panier->getProduit()->getPrix();
            }
        }else{
            // Utilisateur non connecté : panier dans la session
            $paniers = $session->get('panier', []);
            $total = 0;
            foreach ($paniers as $item) {
                $total += $item['quantity'] * $item['produit']['prix'];
            }
        }
            
        dump($paniers);
        return $this->render('panier/index.html.twig', [
                'paniers' => $paniers,
                "total"=>$total
            ]);
    }


    #[Route('/panier/ajouter/{id}', name: 'panier_ajouter')]
    public function ajouter(Request $request, Produit $produit, EntityManagerInterface $em, PanierRepository $repo, SessionInterface $session) :Response 
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

        if ($user){
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

             // on envoie les modifications a Doctrine qui lui envoie les requetes SQL
            $em->flush();

        }else{

            //quand le user n'est pas connecté nous utilisons la session pour stocker temporairement en local et non dans la base de données

            if(isset($panier[$produit->getId()])){ // si le produit existe déja dans le panier
                // le tableau est indexe sur l'id du produit
                // 'panier' est la clé dans laquelle nous stockons le panier ($panier)
                $panier[$produit->getId()]['quantity']+=$quantite;
                
            }else{
                $panier[$produit->getId()]=[
                    'produit'=>[
                        'id'=>$produit->getId(),
                        'nom'=>$produit->getNom(),
                        'prix'=>$produit->getPrix(),
                    ],
                    'quantity'=>$quantite
                ];
            }
            
            $session->set('panier', $panier);

            // on envoie les modifications à Doctrine qui lui envoie les requetes SQL
            // $em->flush(); // flush cest comme execute //
        }
        
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
