<?php

namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Commande;
use App\Repository\ProductRepository;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    /**
     *page d'accueil de notre site
     * @Route("/", name="home")
     */
    public function home(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();


        return $this->render('home/home.html.twig', [
            'products' => $products

        ]);
    }

    /**
     *
     *
     * @Route("/addCart/{id}", name="addCart")
     */
    public function addCart(PanierService $panierService, $id)
    {
         $panierService->add($id);
         $this->addFlash('success', 'Ajouté au panier 🤩');
         //dd($panierService->getFullPanier());
         return $this->redirectToRoute('home');

    }

    /**
     *
     * @Route("/removeCart/{id}", name="removeCart")
     */
    public function removeCart(PanierService $panierService, $id)
    {
        $panierService->remove($id);
        $this->addFlash('success', 'Ajouté au panier 🤩');
        //dd($panierService->getFullPanier());
        return $this->redirectToRoute('panier');

    }

    /**
     *
     * @Route("/deleteCart/{id}", name="deleteCart")
     */
    public function deleteCart(PanierService $panierService, $id)
    {
        $panierService->delete($id);
        $this->addFlash('success', 'Ajouté au panier 🤩');
        //dd($panierService->getFullPanier());
        return $this->redirectToRoute('panier');

    }

    /**
     *
     * @Route("/destroyCart", name="destroyCart")
     */
    public function destroyCart(PanierService $panierService)
    {
        $panierService->destroy();
        $this->addFlash('success', 'Ajouté au panier 🤩');
        //dd($panierService->getFullPanier());
        return $this->redirectToRoute('home');

    }





    /**
     * @Route("/panier", name="panier")
     */
    public function panier(PanierService $panierService)
    {
        $panier=$panierService->getFullPanier();
        $total=$panierService->getTotal();

        return $this->render('home/panier.html.twig', [
            'total'=>$total,
            'panier'=>$panier
        ]);

    }

    /**
     * @Route("/commande", name="commande")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function commande(PanierService $panierService, EntityManagerInterface $manager)
    {
        $panier =$panierService->getFullPanier();

        $commande=new Commande();

        $commande->setUser($this->getUser());
        $commande->setDate(new \DateTime());

        foreach ($panier as $indice=>$detail)
        {
            $achat=new Achat();

            $achat->setProduct($detail['product']);
            $achat->setQuantity($detail['quantity']);
            $achat->setCommande($commande);

            $manager->persist($achat);

        }

        $manager->persist($commande);
        $manager->flush();
        $this->addFlash('success', 'merci pour votre commande');
         $panierService->destroy();
         return $this->redirectToRoute('home');


    }


}
