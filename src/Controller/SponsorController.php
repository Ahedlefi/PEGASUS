<?php

namespace App\Controller;

use App\Entity\Sponsor;
use App\Form\SponsorType;
use App\Form\SponsorEditType;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/sponsor')]
final class SponsorController extends AbstractController
{
    #[Route(name: 'app_sponsor_index', methods: ['GET'])]
    public function index(SponsorRepository $sponsorRepository): Response
    {
        return $this->render('sponsor/index.html.twig', [
            'sponsors' => $sponsorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sponsor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $sponsor = new Sponsor();
        $form = $this->createForm(SponsorEditType::class, $sponsor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($sponsor);
            $entityManager->flush();

            return $this->redirectToRoute('app_sponsor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sponsor/new.html.twig', [
            'sponsor' => $sponsor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sponsor_show', methods: ['GET'])]
    public function show(Sponsor $sponsor): Response
    {
        return $this->render('sponsor/show.html.twig', [
            'sponsor' => $sponsor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sponsor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        $usernameForm = $this->createFormBuilder($sponsor)
            ->add('username', TextType::class, [
                'constraints' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 180])],
            ])
            ->getForm();

        $phoneForm = $this->createFormBuilder($sponsor)
            ->add('phone', TelType::class, [
                'required' => false,
                'constraints' => [new Assert\Regex(['pattern' => '/^[\\d+\\-\\s]+$/']), new Assert\Length(['max' => 30])],
            ])
            ->getForm();

        $avatarForm = $this->createFormBuilder($sponsor)
            ->add('avatarUrl', UrlType::class, [
                'required' => false,
            ])
            ->getForm();

        $usernameForm->handleRequest($request);
        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sponsor_edit', ['id' => $sponsor->getId()], Response::HTTP_SEE_OTHER);
        }

        $phoneForm->handleRequest($request);
        if ($phoneForm->isSubmitted() && $phoneForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sponsor_edit', ['id' => $sponsor->getId()], Response::HTTP_SEE_OTHER);
        }

        $avatarForm->handleRequest($request);
        if ($avatarForm->isSubmitted() && $avatarForm->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sponsor_edit', ['id' => $sponsor->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sponsor/edit.html.twig', [
            'sponsor' => $sponsor,
            'usernameForm' => $usernameForm->createView(),
            'phoneForm' => $phoneForm->createView(),
            'avatarForm' => $avatarForm->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_sponsor_delete', methods: ['POST'])]
    public function delete(Request $request, Sponsor $sponsor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sponsor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($sponsor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sponsor_index', [], Response::HTTP_SEE_OTHER);
    }
}
