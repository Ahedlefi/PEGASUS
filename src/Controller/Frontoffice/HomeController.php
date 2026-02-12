<?php

namespace App\Controller\Frontoffice;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_frontoffice_home')]
    public function index(): Response
    {
        return $this->render('frontoffice/home/base_front.html.twig', [
            'controller_name' => 'Frontoffice/HomeController',
        ]);
    }
}
