<?php

namespace App\Controller;

use App\Entity\Drug;
use App\Form\DrugType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/drug')]
final class DrugController extends AbstractController
{
    #[Route(name: 'app_drug_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $drugs = $entityManager
            ->getRepository(Drug::class)
            ->findAll();

        return $this->render('drug/index.html.twig', [
            'drugs' => $drugs,
        ]);
    }

    #[Route('/new', name: 'app_drug_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $drug = new Drug();
        $form = $this->createForm(DrugType::class, $drug);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($drug);
            $entityManager->flush();

            return $this->redirectToRoute('app_drug_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('drug/new.html.twig', [
            'drug' => $drug,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_drug_show', methods: ['GET'])]
    public function show(Drug $drug): Response
    {
        return $this->render('drug/show.html.twig', [
            'drug' => $drug,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_drug_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Drug $drug, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DrugType::class, $drug);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_drug_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('drug/edit.html.twig', [
            'drug' => $drug,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_drug_delete', methods: ['POST'])]
    public function delete(Request $request, Drug $drug, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$drug->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($drug);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_drug_index', [], Response::HTTP_SEE_OTHER);
    }
}
