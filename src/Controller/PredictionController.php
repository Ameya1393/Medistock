<?php

namespace App\Controller;

use App\Entity\Drug;
use App\Service\ConsumptionPredictionService;
use App\Service\LowStockPredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/predictions')]
final class PredictionController extends AbstractController
{
    #[Route('/consumption', name: 'app_prediction_consumption', methods: ['GET'])]
    public function consumption(
        Request $request,
        EntityManagerInterface $entityManager,
        ConsumptionPredictionService $predictionService
    ): Response {
        $drugId = $request->query->get('drug_id');
        $days = (int) ($request->query->get('days', 7));
        
        $drugs = $entityManager->getRepository(Drug::class)->findAll();
        $selectedDrug = $drugId ? $entityManager->getRepository(Drug::class)->find($drugId) : null;

        $predictions = [];
        $historical = [];
        
        if ($selectedDrug) {
            $predictions = $predictionService->predictConsumption($selectedDrug, $days);
            $historical = $predictionService->getHistoricalData($selectedDrug, 30);
        }

        return $this->render('prediction/consumption.html.twig', [
            'drugs' => $drugs,
            'selectedDrug' => $selectedDrug,
            'days' => $days,
            'predictions' => $predictions,
            'historical' => $historical,
        ]);
    }

    #[Route('/stockout', name: 'app_prediction_stockout', methods: ['GET'])]
    public function stockout(LowStockPredictionService $predictionService): Response
    {
        $allPredictions = $predictionService->predictAllDrugs();

        return $this->render('prediction/stockout.html.twig', [
            'predictions' => $allPredictions,
        ]);
    }

    #[Route('/drug/{id}', name: 'app_prediction_drug', methods: ['GET'])]
    public function drugPrediction(
        Drug $drug,
        ConsumptionPredictionService $consumptionService,
        LowStockPredictionService $stockoutService
    ): Response {
        $consumptionPredictions = $consumptionService->predictConsumption($drug, 14);
        $historical = $consumptionService->getHistoricalData($drug, 30);
        $stockoutPrediction = $stockoutService->predictStockout($drug);

        return $this->render('prediction/drug.html.twig', [
            'drug' => $drug,
            'consumptionPredictions' => $consumptionPredictions,
            'historical' => $historical,
            'stockoutPrediction' => $stockoutPrediction,
        ]);
    }
}

