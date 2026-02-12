<?php

namespace App\Controller\Frontoffice;

use App\Entity\Admin;
use App\Entity\Artiste;
use App\Entity\NormalUser;
use App\Entity\Sponsor;
use App\Entity\User;
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

    #[Route('/dashboard', name: 'app_frontoffice_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('frontoffice/home/base_front.html.twig');
    }

    #[Route('/edit-profile', name: 'app_frontoffice_edit_profile')]
    public function editProfile(): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('frontoffice_signin');
        }

        $id = $user->getId();
        if ($id === null) {
            return $this->redirectToRoute('app_frontoffice_home');
        }

        if ($user instanceof Admin) {
            return $this->redirectToRoute('app_admin_edit', ['id' => $id]);
        }

        if ($user instanceof Artiste) {
            return $this->redirectToRoute('app_artiste_edit', ['id' => $id]);
        }

        if ($user instanceof Sponsor) {
            return $this->redirectToRoute('app_sponsor_edit', ['id' => $id]);
        }

        if ($user instanceof NormalUser) {
            return $this->redirectToRoute('app_normal_user_edit', ['id' => $id]);
        }

        return $this->redirectToRoute('app_frontoffice_home');
    }
}
