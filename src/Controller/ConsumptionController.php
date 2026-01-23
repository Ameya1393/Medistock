<?php

namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\Drug;
use App\Form\ConsumptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consumption')]
final class ConsumptionController extends AbstractController
{
    #[Route(name: 'app_consumption_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $consumptions = $entityManager
            ->getRepository(Consumption::class)
            ->findBy([], ['consumedAt' => 'DESC'], 50);

        return $this->render('consumption/index.html.twig', [
            'consumptions' => $consumptions,
        ]);
    }

    #[Route('/new', name: 'app_consumption_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $consumption = new Consumption();
        $consumption->setLoggedBy('Staff'); // TODO: Replace with actual logged-in user
        
        $form = $this->createForm(ConsumptionType::class, $consumption);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Decrease stock when consumption is logged
            $drug = $consumption->getDrug();
            $newStock = $drug->getStockQuantity() - $consumption->getQuantity();
            $drug->setStockQuantity(max(0, $newStock)); // Prevent negative stock

            $entityManager->persist($consumption);
            $entityManager->flush();

            $this->addFlash('success', 'Consumption logged successfully!');
            return $this->redirectToRoute('app_consumption_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('consumption/new.html.twig', [
            'consumption' => $consumption,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_consumption_show', methods: ['GET'])]
    public function show(Consumption $consumption): Response
    {
        return $this->render('consumption/show.html.twig', [
            'consumption' => $consumption,
        ]);
    }
}

