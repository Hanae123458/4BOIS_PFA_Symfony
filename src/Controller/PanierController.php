<?php
namespace App\Controller;

use App\Entity\Panier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class PanierController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/panier', name: 'panier_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $panier = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user]);

        return $this->render('panier.html.twig', [
            'panier' => $panier
        ]);
    }

    #[Route('/panier/ajouter', name: 'panier_ajouter', methods: ['POST'])]
    public function ajouter(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $nomProduit = $request->request->get('nom_produit');

        $panierExist = $em->getRepository(Panier::class)->findOneBy([
            'utilisateur' => $user,
            'nomProduit' => $nomProduit
        ]);

        if ($panierExist) {
            $panierExist->setQuantite($panierExist->getQuantite() + 1);
            $em->flush();
            $this->addFlash('success', 'Quantité mise à jour dans votre panier');
        } else {
            $panier = new Panier();
            $panier->setUtilisateur($user)
                   ->setNomProduit($nomProduit)
                   ->setQuantite(1)
                   ->setDateAjout(new \DateTime());
            
            $em->persist($panier);
            $em->flush();
            $this->addFlash('success', 'Produit ajouté au panier');
        }

         return $this->redirectToRoute('app_products');
    }

    #[Route('/panier/plus/{id}', name: 'panier_plus')]
    public function plus(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        $panier->setQuantite($panier->getQuantite() + 1);
        $em->flush();

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/moins/{id}', name: 'panier_moins')]
    public function moins(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        if ($panier->getQuantite() > 1) {
            $panier->setQuantite($panier->getQuantite() - 1);
            $em->flush();
        } else {
            $em->remove($panier);
            $em->flush();
            $this->addFlash('warning', 'Produit retiré du panier');
        }

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/supprimer/{id}', name: 'panier_supprimer')]
    public function supprimer(int $id, EntityManagerInterface $em): Response
    {
        $panier = $em->getRepository(Panier::class)->find($id);
        
        if (!$panier) {
            throw $this->createNotFoundException('Produit non trouvé dans le panier');
        }

        $em->remove($panier);
        $em->flush();
        $this->addFlash('danger', 'Produit supprimé du panier');

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/vider', name: 'panier_vider')]
    public function vider(EntityManagerInterface $em): Response
    {
        $user = $this->security->getUser();
        $paniers = $em->getRepository(Panier::class)->findBy(['utilisateur' => $user]);

        foreach ($paniers as $panier) {
            $em->remove($panier);
        }
        $em->flush();

        $this->addFlash('danger', 'Panier vidé avec succès');
        return $this->redirectToRoute('panier_index');
    }
}