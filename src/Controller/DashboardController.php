<?php

namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $drugs = $entityManager->getRepository(Drug::class)->findAll();
        $lowStockDrugs = array_filter($drugs, fn(Drug $drug) => $drug->isLowStock());
        
        $totalStockItems = array_sum(array_map(fn(Drug $drug) => $drug->getStockQuantity(), $drugs));
        
        $recentConsumptions = $entityManager
            ->getRepository(Consumption::class)
            ->findBy([], ['consumedAt' => 'DESC'], 10);

        return $this->render('dashboard/index.html.twig', [
            'title' => 'MediStock Dashboard',
            'totalDrugs' => count($drugs),
            'totalStockItems' => $totalStockItems,
            'lowStockCount' => count($lowStockDrugs),
            'lowStockDrugs' => array_slice($lowStockDrugs, 0, 5), // Show top 5
            'recentConsumptions' => $recentConsumptions,
        ]);
    }
}

