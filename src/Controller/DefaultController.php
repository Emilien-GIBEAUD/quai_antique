<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route("/")]
    public function home():Response
    {
        return new Response("Page home ! /api/doc/  : accès à la doc de l'API.");
    }
}
