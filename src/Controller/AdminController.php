<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Material;
use App\Form\CategoryType;
use App\Form\MaterialType;
use App\Repository\CategoryRepository;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{


    /**
     *
     *
     * @Route("/category", name="category")
     * @Route("/editCategory/{id}", name="editCategory")
     */
    public function category(Request $request, EntityManagerInterface $manager, CategoryRepository $categoryRepository, $id = null)
    {
        // on récupère toutes les catégories en table de BDD grace à la méthode findAll() du categoryRepository
        $categories = $categoryRepository->findAll();
        //dd($categories)

        // création d'un nouvel objet instance de Category pour l'ajout
        if ($id) {  // si $id n'est pas null on est sur la route editCategory
            $category = $categoryRepository->find($id);

        } else { // sinon on est sur la route category donc en création

            $category = new Category();
        }


        // Création du formulaire en liens avec Category
        $form = $this->createForm(CategoryType::class, $category);

        // on appelle la méthode handleRequest sur notre objet formulaire pour récupérer les données provenants du formulaire et charger l'objet Category
        $form->handleRequest($request);

        // condition de soumission et de validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // L'objet category est rempli de toutes ses information (pas besoin d'utiliser certains de ses setters pour lui attribuer des valeurs)
            // On demande au manager de préparer la requête
            $manager->persist($category);
            //  On execute
            $manager->flush();
            // message en session
            if ($id) {
                $this->addFlash('success', 'Catégorie modifiée');

            } else {

                $this->addFlash('success', 'Catégorie ajoutée');
            }


            // return d'une redirection sur le twig appelé category (en name de public fonction)
            return $this->redirectToRoute('category');

        }

        // on renvoie la vue du formulaire grace à la méthode createView()
        return $this->render('admin/category.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories

        ]);
    }

    /**
     *
     *
     * @Route("/deleteCategory/{id}", name="deleteCategory")
     */
    public function deleteCategory(Category $category, EntityManagerInterface $manager)
    {
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('success', 'categorie supprimée');


        return $this->redirectToRoute('category');
    }


    /**
     *
     *
     * @Route("/material", name="material")
     * @Route("/editMaterial/{id}", name="editMaterial")
     */
    public function material(Request $request, EntityManagerInterface $manager, MaterialRepository $materialRepository, $id=null)
    {
    $materials=$materialRepository->findAll();

    if ($id){

        $material=$materialRepository->find($id);

    }else{

        $material=new Material();
    }


    $form=$this->createForm(MaterialType::class, $material);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()){
        $manager->persist($material);
        $manager->flush();

        if ($id){

            $this->addFlash('success', 'Matière modifiée');
        }else{

            $this->addFlash('success', 'Matière créée');
        }

        return $this->redirectToRoute('material');


    }



        return $this->render('admin/material.html.twig', [
         'form'=>$form->createView(),
            'materials'=>$materials

        ]);
    }


    /**
     *
     *
     * @Route("/deleteMaterial/{id}", name="deleteMaterial")
     */
    public function deleteMaterial(Material $material, EntityManagerInterface $manager)
    {

        $manager->remove($material);
        $manager->flush();

        $this->addFlash('success', 'Matière supprimée');
        return $this->redirectToRoute('material');
    }


}
