<?php

namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports')]
final class ReportsController extends AbstractController
{
    #[Route('/low-stock', name: 'app_reports_low_stock', methods: ['GET'])]
    public function lowStock(EntityManagerInterface $entityManager): Response
    {
        $drugs = $entityManager
            ->getRepository(Drug::class)
            ->findAll();

        $lowStockDrugs = array_filter($drugs, fn(Drug $drug) => $drug->isLowStock());

        return $this->render('reports/low_stock.html.twig', [
            'lowStockDrugs' => $lowStockDrugs,
        ]);
    }

    #[Route('/usage', name: 'app_reports_usage', methods: ['GET'])]
    public function usage(Request $request, EntityManagerInterface $entityManager): Response
    {
        $drugId = $request->query->get('drug_id');
        $startDate = $request->query->get('start_date');
        $endDate = $request->query->get('end_date');

        $drugs = $entityManager->getRepository(Drug::class)->findAll();
        $selectedDrug = $drugId ? $entityManager->getRepository(Drug::class)->find($drugId) : null;

        $qb = $entityManager->getRepository(Consumption::class)->createQueryBuilder('c');

        if ($selectedDrug) {
            $qb->andWhere('c.drug = :drug')
               ->setParameter('drug', $selectedDrug);
        }

        if ($startDate) {
            $qb->andWhere('c.consumedAt >= :startDate')
               ->setParameter('startDate', new \DateTimeImmutable($startDate));
        }

        if ($endDate) {
            $qb->andWhere('c.consumedAt <= :endDate')
               ->setParameter('endDate', new \DateTimeImmutable($endDate . ' 23:59:59'));
        }

        $consumptions = $qb->orderBy('c.consumedAt', 'DESC')->getQuery()->getResult();

        // Group by drug for summary
        $usageByDrug = [];
        foreach ($consumptions as $consumption) {
            $drugName = $consumption->getDrug()->getName();
            if (!isset($usageByDrug[$drugName])) {
                $usageByDrug[$drugName] = [
                    'drug' => $consumption->getDrug(),
                    'totalQuantity' => 0,
                    'count' => 0,
                ];
            }
            $usageByDrug[$drugName]['totalQuantity'] += $consumption->getQuantity();
            $usageByDrug[$drugName]['count']++;
        }

        return $this->render('reports/usage.html.twig', [
            'drugs' => $drugs,
            'selectedDrug' => $selectedDrug,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'consumptions' => $consumptions,
            'usageByDrug' => $usageByDrug,
        ]);
    }
}

