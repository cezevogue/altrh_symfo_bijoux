<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 * @IsGranted("ROLE_ADMIN")
 */
class ProductController extends AbstractController
{

    /**
     *
     *
     * @Route("/add", name="addProduct")
     */
    public function addProduct(Request $request, EntityManagerInterface $manager)
    {
        // création d'une instance de la classe product
        // On est en ajout (donc en création d'un nouveau produit), on instancie donc un objet vide la classe
        $product = new Product;

        // création d'un formulaire grace à la méthode createForm() héritée de l'abstractController
        // 2arguments obligatoires:
        // 1er Le formulaire sur lequel on se base (le Type)
        // 2nd L'objet instance à remplir
        // 3eme (optionnel)=> tableau d'option
        // Le fait de renseigner ces arguments permet à Symfony d'effectuer les contrôles de validité
        // à savoir les typages de données en liens avec les types d'input de formulaire et le fait que chaque input du type (chaques add() ) correspondent bien à une propriété de la classe
        $form = $this->createForm(ProductType::class, $product, ['add'=>true]);
        // $form est un objet instance de Form
        //traitement de la requête
        $form->handleRequest($request); // Request est la classe qui regroupe nos superglobales
        // $request->request ($_POST)
        // $request->query   ($_GET)
        // dd() pour dump and die qui permet d'afficher un var_dump() en stoppant l'execution du script
        //dd($product);  // $product est à présent rempli de ses données de formulaire

        // condition de soumission de formulaire
        if ($form->isSubmitted() && $form->isValid()) // si formulaire est soumis et valide (aucune erreur de constraints n'a été relevé) . Les 2 conditions doivent être appelées dans cet ordre
        {

            // on récupère toutes les données sur l'input type file (picture)
            $pictureFile = $form->get('picture')->getData();
            //dd($pictureFile);

            $picture_bdd = date("YmdHis") . $pictureFile->getClientOriginalName();
            //dd($picture_bdd);

            try {
                // $this->>getParameter() permet d'accéder aux constantes déclarées dans le services.yaml
                // sur la partie parameter
                $pictureFile->move($this->getParameter('upload_directory'), $picture_bdd);
                // move() = copy() de php procédural. Doit être appelé sur l'objet File
                // 2 argument
                // 1er: l'emplacement de copie
                // 2nd: le nom du fichier

            } catch (FileException $e) {
                dd($e);
            }

            // on réaffecte à présent le nouveau nom du fichier à notre objet $product grace à son setter
            $product->setPicture($picture_bdd);
            //dd($product);
            // EntityManagerInterface $manager obligatoire pour toutes les requêtes d'INSERT INTO, UPDATE, DELETE
            $manager->persist($product); // on lui demande de persister l'objet (préparation de la requête)
            $manager->flush();  // on envoie l'objet en BDD (execute )

            $this->addFlash('success', 'Produit ajouté');

            return $this->redirectToRoute("listProduct");


        }


        // on renvoi la vue de notre formulaire dans le tableau de notre méthode render  grace à la méthode createView() de notre objet $form
        return $this->render('product/addProduct.html.twig', [
            'form' => $form->createView()

        ]);
    }

    /**
     *
     *
     * @Route("/list", name="listProduct")
     */
    public function listProduct(ProductRepository $productRepository)
    {

        // Le repository permet d'effectuer les requêtes de SELECT
        // sa méthode findAll()  equivaut à SELECT * FROM nomdeLentite;
        $products = $productRepository->findAll();
        //dd($products); // $products contient toutes les entrées de la table product en BDD


        return $this->render('product/listProduct.html.twig', [
            'products' => $products

        ]);
    }

    /**
     *
     *
     * @Route("/edit/{id}", name="editProduct")
     */
    public function editProduct(Product $product, Request $request, EntityManagerInterface $manager)
    {

        // lorsque un paramètre id est passé sur l'url et l'on injecte en dépendance une entité voulue (ici Product), symfony rempli automatique l'objet $product de ses données sur l'id passé (SELECT * FROM product WHERE id={id})
        //dd($product);
        // nous sommes en modification donc pas d'instanciation de nouvel objet. (pas de new Product)
        // on récupère ses donnés de la BDD
        $form=$this->createForm(ProductType::class, $product, ['edit'=>true]);

        $form->handleRequest($request);
       // dd($product);

        if ($form->isSubmitted() && $form->isValid()){

            $picture_edit_file=$form->get('picture_edit')->getData();

            // on verifie si le champs picture_edit a été saisi. alors on modifie la propriété picture
            // on copie le nouveau fichier photo et supprime le précédent
            if ($picture_edit_file){

                $picture_bdd=date('YmdHis').$picture_edit_file->getClientOriginalName();

                unlink($this->getParameter('upload_directory').'/'.$product->getPicture());
                $picture_edit_file->move($this->getParameter('upload_directory'), $picture_bdd);

                $product->setPicture($picture_bdd);

            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Produit modifié');

            return $this->redirectToRoute('listProduct');







        }





        return $this->render('product/editProduct.html.twig', [
            'form'=>$form->createView(),
            'product'=>$product

        ]);
    }

    /**
     *
     *
     * @Route("/delete/{id}", name="deleteProduct")
     */
    public function deleteProduct(Product $product, EntityManagerInterface $manager)
    {
        // on supprime la photo du produit dans le dossier d'upload
        unlink($this->getParameter('upload_directory').'/'.$product->getPicture());

        $manager->remove($product);
        $manager->flush();

        $this->addFlash('success', 'Produit supprimé');

        return $this->redirectToRoute('listProduct');
    }


}
