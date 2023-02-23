<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{


    /**
     *
     *
     * @Route("/category", name="category")
     */
    public function category()
    {


        return $this->render('admin/category.html.twig', [


        ]);
    }
}
