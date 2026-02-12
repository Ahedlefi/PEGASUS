<?php

namespace App\Controller;

use App\Entity\NormalUser;
use App\Form\NormalUserType;
use App\Form\NormalUserEditType;
use App\Repository\NormalUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/normal/user')]
final class NormalUserController extends AbstractController
{
    #[Route(name: 'app_normal_user_index', methods: ['GET'])]
    public function index(NormalUserRepository $normalUserRepository): Response
    {
        return $this->render('normal_user/index.html.twig', [
            'normal_users' => $normalUserRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_normal_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $normalUser = new NormalUser();
        $form = $this->createForm(NormalUserEditType::class, $normalUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($normalUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_normal_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('normal_user/new.html.twig', [
            'normal_user' => $normalUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_normal_user_show', methods: ['GET'])]
    public function show(NormalUser $normalUser): Response
    {
        return $this->render('normal_user/show.html.twig', [
            'normal_user' => $normalUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_normal_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, NormalUser $normalUser, EntityManagerInterface $entityManager): Response
    {
        $usernameForm = $this->createFormBuilder($normalUser)
            ->add('username', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 180])],
            ])
            ->getForm();

        $phoneForm = $this->createFormBuilder($normalUser)
            ->add('phone', TelType::class, [
                'required' => false,
                'constraints' => [new Assert\Regex(['pattern' => '/^[\\d+\\-\\s]+$/']), new Assert\Length(['max' => 30])],
            ])
            ->getForm();

        $avatarForm = $this->createFormBuilder($normalUser)
            ->add('avatarUrl', UrlType::class, [
                'required' => false,
            ])
            ->getForm();

        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_normal_user_edit', ['id' => $normalUser->getId()], Response::HTTP_SEE_OTHER);
        }

        $phoneForm->handleRequest($request);
        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_normal_user_edit', ['id' => $normalUser->getId()], Response::HTTP_SEE_OTHER);
        }

        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_normal_user_edit', ['id' => $normalUser->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('normal_user/edit.html.twig', [
            'normal_user' => $normalUser,
            'usernameForm' => $usernameForm->createView(),
            'phoneForm' => $phoneForm->createView(),
            'avatarForm' => $avatarForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_normal_user_delete', methods: ['POST'])]
    public function delete(Request $request, NormalUser $normalUser, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$normalUser->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($normalUser);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_normal_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
