<?php

namespace App\Service;

use App\Entity\Consumption;
use App\Entity\Drug;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service for predicting future drug consumption using time series analysis
 */
class ConsumptionPredictionService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Predict consumption for a specific drug for the next N days
     * 
     * @param Drug $drug The drug to predict for
     * @param int $days Number of days to predict ahead (default: 7)
     * @return array Prediction data with dates and predicted quantities
     */
    public function predictConsumption(Drug $drug, int $days = 7): array
    {
        // Get historical consumption data
        $consumptions = $this->entityManager
            ->getRepository(Consumption::class)
            ->createQueryBuilder('c')
            ->where('c.drug = :drug')
            ->setParameter('drug', $drug)
            ->orderBy('c.consumedAt', 'ASC')
            ->getQuery()
            ->getResult();

        if (count($consumptions) < 3) {
            // Not enough data for prediction, return simple average
            return $this->simpleAveragePrediction($consumptions, $days);
        }

        // Group consumption by date
        $dailyConsumption = $this->groupByDate($consumptions);
        
        // Use moving average for prediction
        return $this->movingAveragePrediction($dailyConsumption, $days);
    }

    /**
     * Simple average prediction when there's not enough data
     */
    private function simpleAveragePrediction(array $consumptions, int $days): array
    {
        $totalQuantity = 0;
        foreach ($consumptions as $consumption) {
            $totalQuantity += $consumption->getQuantity();
        }
        
        $avgDaily = count($consumptions) > 0 
            ? $totalQuantity / max(count($consumptions), 1) 
            : 0;

        $predictions = [];
        $startDate = new \DateTimeImmutable();
        
        for ($i = 1; $i <= $days; $i++) {
            $date = $startDate->modify("+{$i} days");
            $predictions[] = [
                'date' => $date->format('Y-m-d'),
                'predicted' => (int) round($avgDaily),
                'confidence' => 'low',
            ];
        }

        return $predictions;
    }

    /**
     * Group consumptions by date
     */
    private function groupByDate(array $consumptions): array
    {
        $grouped = [];
        
        foreach ($consumptions as $consumption) {
            $date = $consumption->getConsumedAt()->format('Y-m-d');
            if (!isset($grouped[$date])) {
                $grouped[$date] = 0;
            }
            $grouped[$date] += $consumption->getQuantity();
        }

        return $grouped;
    }

    /**
     * Moving average prediction method
     */
    private function movingAveragePrediction(array $dailyConsumption, int $days): array
    {
        $values = array_values($dailyConsumption);
        $count = count($values);
        
        // Calculate moving average (window of last 7 days or all available)
        $window = min(7, $count);
        $recentValues = array_slice($values, -$window);
        $average = array_sum($recentValues) / $window;
        
        // Calculate trend (simple linear trend)
        $trend = 0;
        if ($count >= 2) {
            $firstHalf = array_slice($values, 0, (int)($count / 2));
            $secondHalf = array_slice($values, (int)($count / 2));
            $firstAvg = array_sum($firstHalf) / count($firstHalf);
            $secondAvg = array_sum($secondHalf) / count($secondHalf);
            $trend = ($secondAvg - $firstAvg) / max($count, 1);
        }

        // Calculate standard deviation for confidence
        $variance = 0;
        foreach ($recentValues as $value) {
            $variance += pow($value - $average, 2);
        }
        $stdDev = sqrt($variance / max($window, 1));

        $predictions = [];
        $startDate = new \DateTimeImmutable();
        
        for ($i = 1; $i <= $days; $i++) {
            $date = $startDate->modify("+{$i} days");
            $predicted = $average + ($trend * $i);
            $predicted = max(0, (int) round($predicted)); // Ensure non-negative
            
            // Confidence based on data consistency
            $confidence = 'medium';
            if ($stdDev < $average * 0.2) {
                $confidence = 'high';
            } elseif ($stdDev > $average * 0.5) {
                $confidence = 'low';
            }

            $predictions[] = [
                'date' => $date->format('Y-m-d'),
                'predicted' => $predicted,
                'confidence' => $confidence,
                'upperBound' => (int) round($predicted + $stdDev),
                'lowerBound' => (int) round(max(0, $predicted - $stdDev)),
            ];
        }

        return $predictions;
    }

    /**
     * Get historical consumption data for a drug
     */
    public function getHistoricalData(Drug $drug, int $days = 30): array
    {
        $startDate = new \DateTimeImmutable("-{$days} days");
        
        $consumptions = $this->entityManager
            ->getRepository(Consumption::class)
            ->createQueryBuilder('c')
            ->where('c.drug = :drug')
            ->andWhere('c.consumedAt >= :startDate')
            ->setParameter('drug', $drug)
            ->setParameter('startDate', $startDate)
            ->orderBy('c.consumedAt', 'ASC')
            ->getQuery()
            ->getResult();

        $grouped = $this->groupByDate($consumptions);
        
        $data = [];
        $currentDate = $startDate;
        $endDate = new \DateTimeImmutable();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $data[] = [
                'date' => $dateStr,
                'quantity' => $grouped[$dateStr] ?? 0,
            ];
            $currentDate = $currentDate->modify('+1 day');
        }

        return $data;
    }
}

