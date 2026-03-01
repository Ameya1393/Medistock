<?php

namespace App\Controller;

use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/alerts')]
#[IsGranted('ROLE_ADMIN')]
final class AlertsController extends AbstractController
{
    #[Route(name: 'app_alerts_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $drugs = $entityManager
            ->getRepository(Drug::class)
            ->findAll();

        $lowStockDrugs = array_filter($drugs, fn(Drug $drug) => $drug->isLowStock());

        return $this->render('alerts/index.html.twig', [
            'lowStockDrugs' => $lowStockDrugs,
            'totalDrugs' => count($drugs),
            'lowStockCount' => count($lowStockDrugs),
        ]);
    }
}









