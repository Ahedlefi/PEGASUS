<?php

namespace App\Controller;

use App\Entity\Artiste;
use App\Form\ArtisteType;
use App\Form\ArtisteEditType;
use App\Repository\ArtisteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/artiste')]
final class ArtisteController extends AbstractController
{
    #[Route(name: 'app_artiste_index', methods: ['GET'])]
    public function index(ArtisteRepository $artisteRepository): Response
    {
        return $this->render('artiste/index.html.twig', [
            'artistes' => $artisteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_artiste_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $artiste = new Artiste();
        $form = $this->createForm(ArtisteEditType::class, $artiste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($artiste);
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('artiste/new.html.twig', [
            'artiste' => $artiste,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_show', methods: ['GET'])]
    public function show(Artiste $artiste): Response
    {
        return $this->render('artiste/show.html.twig', [
            'artiste' => $artiste,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_artiste_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Artiste $artiste, EntityManagerInterface $entityManager): Response
    {
        $usernameForm = $this->createFormBuilder($artiste)
            ->add('username', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 180])],
            ])
            ->getForm();

        $phoneForm = $this->createFormBuilder($artiste)
            ->add('phone', TelType::class, [
                'required' => false,
                'constraints' => [new Assert\Regex(['pattern' => '/^[\\d+\\-\\s]+$/']), new Assert\Length(['max' => 30])],
            ])
            ->getForm();

        $avatarForm = $this->createFormBuilder($artiste)
            ->add('avatarUrl', UrlType::class, [
                'required' => false,
            ])
            ->getForm();

        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_edit', ['id' => $artiste->getId()], Response::HTTP_SEE_OTHER);
        }

        $phoneForm->handleRequest($request);
        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_edit', ['id' => $artiste->getId()], Response::HTTP_SEE_OTHER);
        }

        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_artiste_edit', ['id' => $artiste->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('artiste/edit.html.twig', [
            'artiste' => $artiste,
            'usernameForm' => $usernameForm->createView(),
            'phoneForm' => $phoneForm->createView(),
            'avatarForm' => $avatarForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_artiste_delete', methods: ['POST'])]
    public function delete(Request $request, Artiste $artiste, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$artiste->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($artiste);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_artiste_index', [], Response::HTTP_SEE_OTHER);
    }
}
