<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Form\AdminType;
use App\Form\AdminEditType;
use App\Repository\AdminRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route(name: 'app_admin_index', methods: ['GET'])]
    public function index(AdminRepository $adminRepository): Response
    {
        return $this->render('admin/index.html.twig', [
            'admins' => $adminRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = new Admin();
        $form = $this->createForm(AdminEditType::class, $admin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($admin);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/new.html.twig', [
            'admin' => $admin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_show', methods: ['GET'])]
    public function show(Admin $admin): Response
    {
        return $this->render('admin/show.html.twig', [
            'admin' => $admin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        $usernameForm = $this->createFormBuilder($admin)
            ->add('username', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 180])],
            ])
            ->getForm();

        $phoneForm = $this->createFormBuilder($admin)
            ->add('phone', TelType::class, [
                'required' => false,
                'constraints' => [new Assert\Regex(['pattern' => '/^[\\d+\\-\\s]+$/']), new Assert\Length(['max' => 30])],
            ])
            ->getForm();

        $avatarForm = $this->createFormBuilder($admin)
            ->add('avatarUrl', UrlType::class, [
                'required' => false,
            ])
            ->getForm();

        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_edit', ['id' => $admin->getId()], Response::HTTP_SEE_OTHER);
        }

        $phoneForm->handleRequest($request);
        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_edit', ['id' => $admin->getId()], Response::HTTP_SEE_OTHER);
        }

        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_edit', ['id' => $admin->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/edit.html.twig', [
            'admin' => $admin,
            'usernameForm' => $usernameForm->createView(),
            'phoneForm' => $phoneForm->createView(),
            'avatarForm' => $avatarForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Admin $admin, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$admin->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($admin);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
