<?php

namespace App\Service;

use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for predicting when a drug will run out of stock
 */
class LowStockPredictionService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConsumptionPredictionService $consumptionPredictionService
    ) {
    }

    /**
     * Predict when a drug will run out of stock
     * 
     * @param Drug $drug The drug to predict for
     * @return array Prediction data with estimated days until stockout
     */
    public function predictStockout(Drug $drug): array
    {
        $currentStock = $drug->getStockQuantity();
        
        if ($currentStock <= 0) {
            return [
                'willRunOut' => true,
                'daysUntilStockout' => 0,
                'estimatedDate' => (new \DateTimeImmutable())->format('Y-m-d'),
                'confidence' => 'high',
                'isUrgent' => true,
                'isWarning' => true,
                'message' => 'Stock is already depleted',
            ];
        }

        // Get predicted daily consumption
        $predictions = $this->consumptionPredictionService->predictConsumption($drug, 90); // Predict 90 days ahead
        
        if (empty($predictions)) {
            return [
                'willRunOut' => false,
                'daysUntilStockout' => null,
                'estimatedDate' => null,
                'confidence' => 'low',
                'isUrgent' => false,
                'isWarning' => false,
                'message' => 'Insufficient consumption data for prediction',
            ];
        }

        // Calculate cumulative consumption
        $cumulativeStock = $currentStock;
        $daysUntilStockout = null;
        $estimatedDate = null;
        
        foreach ($predictions as $index => $prediction) {
            $cumulativeStock -= $prediction['predicted'];
            
            if ($cumulativeStock <= 0 && $daysUntilStockout === null) {
                $daysUntilStockout = $index + 1;
                $estimatedDate = $prediction['date'];
                break;
            }
        }

        // Determine confidence based on prediction confidence
        $avgConfidence = array_reduce($predictions, function($carry, $item) {
            $score = $item['confidence'] === 'high' ? 3 : ($item['confidence'] === 'medium' ? 2 : 1);
            return $carry + $score;
        }, 0) / count($predictions);
        
        $confidence = $avgConfidence >= 2.5 ? 'high' : ($avgConfidence >= 1.5 ? 'medium' : 'low');

        if ($daysUntilStockout === null) {
            return [
                'willRunOut' => false,
                'daysUntilStockout' => null,
                'estimatedDate' => null,
                'confidence' => $confidence,
                'isUrgent' => false,
                'isWarning' => false,
                'message' => 'Stock is predicted to last more than 90 days',
                'remainingAfter90Days' => (int) $cumulativeStock,
            ];
        }

        // Check if it's urgent (within threshold)
        $isUrgent = $daysUntilStockout <= 7;
        $isWarning = $daysUntilStockout <= 14;

        return [
            'willRunOut' => true,
            'daysUntilStockout' => $daysUntilStockout,
            'estimatedDate' => $estimatedDate,
            'confidence' => $confidence,
            'isUrgent' => $isUrgent,
            'isWarning' => $isWarning,
            'message' => $isUrgent 
                ? "Stock predicted to run out in {$daysUntilStockout} days (URGENT)"
                : ($isWarning 
                    ? "Stock predicted to run out in {$daysUntilStockout} days (WARNING)"
                    : "Stock predicted to run out in {$daysUntilStockout} days"),
        ];
    }

    /**
     * Get predictions for all drugs
     */
    public function predictAllDrugs(): array
    {
        $drugs = $this->entityManager->getRepository(Drug::class)->findAll();
        $predictions = [];

        foreach ($drugs as $drug) {
            $prediction = $this->predictStockout($drug);
            $predictions[] = [
                'drug' => $drug,
                'prediction' => $prediction,
            ];
        }

        // Sort by urgency (will run out soonest first)
        usort($predictions, function($a, $b) {
            $aDays = $a['prediction']['daysUntilStockout'] ?? 999;
            $bDays = $b['prediction']['daysUntilStockout'] ?? 999;
            return $aDays <=> $bDays;
        });

        return $predictions;
    }
}







