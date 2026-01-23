<?php

namespace App\Controller;

use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
final class StockController extends AbstractController
{
    #[Route(name: 'app_stock_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $drugs = $entityManager
            ->getRepository(Drug::class)
            ->findAll();

        return $this->render('stock/index.html.twig', [
            'drugs' => $drugs,
        ]);
    }

    #[Route('/{id}/update', name: 'app_stock_update', methods: ['GET', 'POST'])]
    public function update(Request $request, Drug $drug, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');
            $quantity = (int) $request->request->get('quantity', 0);

            if ($action === 'increase') {
                $drug->setStockQuantity($drug->getStockQuantity() + $quantity);
            } elseif ($action === 'decrease') {
                $newQuantity = $drug->getStockQuantity() - $quantity;
                $drug->setStockQuantity(max(0, $newQuantity)); // Prevent negative stock
            } elseif ($action === 'set') {
                $drug->setStockQuantity(max(0, $quantity));
            }

            $entityManager->flush();

            $this->addFlash('success', 'Stock updated successfully!');
            return $this->redirectToRoute('app_stock_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('stock/update.html.twig', [
            'drug' => $drug,
        ]);
    }
}

