<?php

namespace App\Controller;

use App\Entity\Consumption;
use App\Entity\Drug;
use App\Service\ConsumptionPredictionService;
use App\Service\LowStockPredictionService;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reports')]
#[IsGranted('ROLE_ADMIN')]
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

    #[Route('/download-summary', name: 'app_reports_download_summary', methods: ['GET'])]
    public function downloadSummary(
        EntityManagerInterface $entityManager,
        LowStockPredictionService $stockoutService,
        ConsumptionPredictionService $consumptionService
    ): Response {
        // Collect all analytics data
        $drugs = $entityManager->getRepository(Drug::class)->findAll();
        $lowStockDrugs = array_filter($drugs, fn(Drug $drug) => $drug->isLowStock());
        $totalStockItems = array_sum(array_map(fn(Drug $drug) => $drug->getStockQuantity(), $drugs));
        
        // Get consumption data
        $allConsumptions = $entityManager->getRepository(Consumption::class)->findAll();
        $recentConsumptions = $entityManager
            ->getRepository(Consumption::class)
            ->findBy([], ['consumedAt' => 'DESC'], 10);
        
        // Calculate consumption statistics
        $totalConsumption = array_sum(array_map(fn(Consumption $c) => $c->getQuantity(), $allConsumptions));
        $consumptionByDrug = [];
        foreach ($allConsumptions as $consumption) {
            $drugName = $consumption->getDrug()->getName();
            if (!isset($consumptionByDrug[$drugName])) {
                $consumptionByDrug[$drugName] = [
                    'drug' => $consumption->getDrug(),
                    'totalQuantity' => 0,
                    'count' => 0,
                ];
            }
            $consumptionByDrug[$drugName]['totalQuantity'] += $consumption->getQuantity();
            $consumptionByDrug[$drugName]['count']++;
        }
        
        // Sort by total quantity consumed
        uasort($consumptionByDrug, fn($a, $b) => $b['totalQuantity'] <=> $a['totalQuantity']);
        $topConsumedDrugs = array_slice($consumptionByDrug, 0, 10, true);
        
        // Get stockout predictions
        $stockoutPredictions = $stockoutService->predictAllDrugs();
        $urgentPredictions = array_filter($stockoutPredictions, fn($p) => $p['prediction']['isUrgent'] ?? false);
        $warningPredictions = array_filter($stockoutPredictions, fn($p) => ($p['prediction']['isWarning'] ?? false) && !($p['prediction']['isUrgent'] ?? false));
        
        // Generate PDF
        $html = $this->generateReportHtml([
            'totalDrugs' => count($drugs),
            'totalStockItems' => $totalStockItems,
            'lowStockCount' => count($lowStockDrugs),
            'lowStockDrugs' => array_slice($lowStockDrugs, 0, 10),
            'totalConsumption' => $totalConsumption,
            'totalConsumptionLogs' => count($allConsumptions),
            'topConsumedDrugs' => $topConsumedDrugs,
            'recentConsumptions' => $recentConsumptions,
            'urgentPredictions' => array_slice($urgentPredictions, 0, 10),
            'warningPredictions' => array_slice($warningPredictions, 0, 10),
            'allStockoutPredictions' => array_slice($stockoutPredictions, 0, 20),
            'generatedAt' => new \DateTimeImmutable(),
        ]);
        
        // Configure Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        // Generate filename
        $filename = 'MediStock_Summary_Report_' . date('Y-m-d_His') . '.pdf';
        
        // Return PDF response
        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
    
    private function generateReportHtml(array $data): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28pt;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 12pt;
            opacity: 0.9;
        }
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .section-title {
            background: #f7fafc;
            padding: 15px;
            border-left: 5px solid #667eea;
            margin-bottom: 15px;
            font-size: 18pt;
            font-weight: bold;
            color: #2d3748;
        }
        .subsection-title {
            font-size: 14pt;
            font-weight: bold;
            color: #4a5568;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
        }
        .kpi-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .kpi-box {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 25%;
        }
        .kpi-value {
            font-size: 24pt;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .kpi-label {
            font-size: 10pt;
            color: #718096;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: white;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        tr:nth-child(even) {
            background: #f7fafc;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
        }
        .badge-danger {
            background: #fee;
            color: #c33;
        }
        .badge-warning {
            background: #ffeaa7;
            color: #d63031;
        }
        .badge-success {
            background: #d5f4e6;
            color: #00b894;
        }
        .explanation-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .explanation-box h4 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 12pt;
        }
        .explanation-box p {
            margin: 5px 0;
            font-size: 10pt;
            color: #4a5568;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 9pt;
        }
        .no-data {
            text-align: center;
            padding: 30px;
            color: #a0aec0;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MediStock Analytics Report</h1>
        <p>Comprehensive Inventory & Consumption Analysis</p>
        <p style="font-size: 10pt; margin-top: 10px;">Generated on: ' . $data['generatedAt']->format('F j, Y \a\t g:i A') . '</p>
    </div>
    
    <!-- Executive Summary -->
    <div class="section">
        <div class="section-title">Executive Summary</div>
        <table class="kpi-grid">
            <tr>
                <td class="kpi-box">
                    <div class="kpi-label">Total Drugs</div>
                    <div class="kpi-value">' . $data['totalDrugs'] . '</div>
                </td>
                <td class="kpi-box">
                    <div class="kpi-label">Total Stock Items</div>
                    <div class="kpi-value">' . number_format($data['totalStockItems']) . '</div>
                </td>
                <td class="kpi-box">
                    <div class="kpi-label">Low Stock Alerts</div>
                    <div class="kpi-value">' . $data['lowStockCount'] . '</div>
                </td>
                <td class="kpi-box">
                    <div class="kpi-label">Total Consumption</div>
                    <div class="kpi-value">' . number_format($data['totalConsumption']) . '</div>
                </td>
            </tr>
        </table>
        
        <div class="explanation-box">
            <h4>What This Summary Means</h4>
            <p><strong>Total Drugs:</strong> The number of unique drug entries in your inventory system. This represents the variety of medications being tracked.</p>
            <p><strong>Total Stock Items:</strong> The sum of all available quantities across all drugs. This gives you the overall inventory volume.</p>
            <p><strong>Low Stock Alerts:</strong> Drugs that have fallen below their reorder threshold. These require immediate attention to prevent stockouts.</p>
            <p><strong>Total Consumption:</strong> The cumulative quantity of all drugs consumed since tracking began. This helps understand overall usage patterns.</p>
        </div>
    </div>
    
    <!-- Low Stock Analysis -->
    <div class="section">
        <div class="section-title">Low Stock Analysis</div>';
        
        if (count($data['lowStockDrugs']) > 0) {
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Drug Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Threshold</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['lowStockDrugs'] as $drug) {
                $html .= '<tr>
                    <td><strong>' . htmlspecialchars($drug->getName()) . '</strong></td>
                    <td>' . htmlspecialchars($drug->getCategory()) . '</td>
                    <td><span class="badge badge-danger">' . $drug->getStockQuantity() . '</span></td>
                    <td>' . $drug->getThreshold() . '</td>
                    <td><span class="badge badge-warning">Low Stock</span></td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="no-data">No low stock items at this time. All drugs are above their reorder thresholds.</div>';
        }
        
        $html .= '<div class="explanation-box">
            <h4>Understanding Low Stock Alerts</h4>
            <p><strong>Low Stock Threshold:</strong> Each drug has a predefined minimum quantity level. When current stock falls below this threshold, the system generates an alert.</p>
            <p><strong>Why It Matters:</strong> Timely reordering prevents stockouts that could disrupt patient care. Monitoring these alerts helps maintain optimal inventory levels.</p>
            <p><strong>Action Required:</strong> Review low stock items and initiate procurement processes to replenish inventory before critical levels are reached.</p>
        </div>';
        
        $html .= '</div>
    
    <!-- Consumption Analytics -->
    <div class="section">
        <div class="section-title">Consumption Analytics</div>
        <div class="subsection-title">Top 10 Most Consumed Drugs</div>';
        
        if (count($data['topConsumedDrugs']) > 0) {
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Drug Name</th>
                        <th>Category</th>
                        <th>Total Consumed</th>
                        <th>Log Entries</th>
                        <th>Avg per Log</th>
                    </tr>
                </thead>
                <tbody>';
            
            $rank = 1;
            foreach ($data['topConsumedDrugs'] as $drugName => $stats) {
                $avgPerLog = $stats['count'] > 0 ? round($stats['totalQuantity'] / $stats['count'], 2) : 0;
                $html .= '<tr>
                    <td><strong>#' . $rank . '</strong></td>
                    <td>' . htmlspecialchars($drugName) . '</td>
                    <td>' . htmlspecialchars($stats['drug']->getCategory()) . '</td>
                    <td><strong>' . number_format($stats['totalQuantity']) . '</strong></td>
                    <td>' . $stats['count'] . '</td>
                    <td>' . $avgPerLog . '</td>
                </tr>';
                $rank++;
            }
            
            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="no-data">No consumption data available yet.</div>';
        }
        
        $html .= '<div class="explanation-box">
            <h4>Consumption Analytics Explained</h4>
            <p><strong>Total Consumed:</strong> The cumulative quantity of a drug used over time. Higher values indicate high-demand medications.</p>
            <p><strong>Log Entries:</strong> The number of times consumption was recorded. More entries suggest frequent usage patterns.</p>
            <p><strong>Average per Log:</strong> The mean quantity consumed per log entry. This helps identify typical usage patterns and batch sizes.</p>
            <p><strong>Business Insight:</strong> Drugs with high consumption rates should be prioritized for stock management and may require larger safety stock levels.</p>
        </div>';
        
        $html .= '</div>
    
    <!-- Stockout Predictions -->
    <div class="section">
        <div class="section-title">AI/ML Stockout Predictions</div>';
        
        if (count($data['urgentPredictions']) > 0) {
            $html .= '<div class="subsection-title">URGENT: Predictions (Stockout within 7 days)</div>
            <table>
                <thead>
                    <tr>
                        <th>Drug Name</th>
                        <th>Current Stock</th>
                        <th>Days Until Stockout</th>
                        <th>Estimated Date</th>
                        <th>Confidence</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['urgentPredictions'] as $prediction) {
                $drug = $prediction['drug'];
                $pred = $prediction['prediction'];
                $days = $pred['daysUntilStockout'] ?? 'N/A';
                $date = $pred['estimatedDate'] ?? 'N/A';
                $confidence = ucfirst($pred['confidence'] ?? 'Unknown');
                
                $html .= '<tr>
                    <td><strong>' . htmlspecialchars($drug->getName()) . '</strong></td>
                    <td>' . $drug->getStockQuantity() . '</td>
                    <td><span class="badge badge-danger">' . $days . '</span></td>
                    <td>' . $date . '</td>
                    <td>' . $confidence . '</td>
                    <td><span class="badge badge-danger">URGENT</span></td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        if (count($data['warningPredictions']) > 0) {
            $html .= '<div class="subsection-title">WARNING: Predictions (Stockout within 14 days)</div>
            <table>
                <thead>
                    <tr>
                        <th>Drug Name</th>
                        <th>Current Stock</th>
                        <th>Days Until Stockout</th>
                        <th>Estimated Date</th>
                        <th>Confidence</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['warningPredictions'] as $prediction) {
                $drug = $prediction['drug'];
                $pred = $prediction['prediction'];
                $days = $pred['daysUntilStockout'] ?? 'N/A';
                $date = $pred['estimatedDate'] ?? 'N/A';
                $confidence = ucfirst($pred['confidence'] ?? 'Unknown');
                
                $html .= '<tr>
                    <td><strong>' . htmlspecialchars($drug->getName()) . '</strong></td>
                    <td>' . $drug->getStockQuantity() . '</td>
                    <td><span class="badge badge-warning">' . $days . '</span></td>
                    <td>' . $date . '</td>
                    <td>' . $confidence . '</td>
                    <td><span class="badge badge-warning">WARNING</span></td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        }
        
        if (count($data['urgentPredictions']) == 0 && count($data['warningPredictions']) == 0) {
            $html .= '<div class="no-data">No urgent stockout predictions at this time. All drugs are predicted to have sufficient stock for the next 14 days.</div>';
        }
        
        $html .= '<div class="explanation-box">
            <h4>How Stockout Predictions Work</h4>
            <p><strong>Prediction Method:</strong> Our AI/ML system uses time series analysis, moving averages, and trend analysis to forecast future consumption patterns based on historical data.</p>
            <p><strong>Days Until Stockout:</strong> Calculated by dividing current stock by predicted daily consumption rate. This gives an estimated timeline for when stock will be depleted.</p>
            <p><strong>Confidence Levels:</strong>
                <br>• <strong>High:</strong> Consistent historical data with low variance (≥80% accuracy expected)
                <br>• <strong>Medium:</strong> Moderate data consistency (60-80% accuracy expected)
                <br>• <strong>Low:</strong> Limited or inconsistent data (<60% accuracy expected)
            </p>
            <p><strong>Urgent vs Warning:</strong> Urgent predictions (≤7 days) require immediate action, while warnings (8-14 days) allow for proactive planning.</p>
            <p><strong>Action Items:</strong> Use these predictions to optimize procurement schedules, reduce stockout risks, and maintain optimal inventory levels.</p>
        </div>';
        
        $html .= '</div>
    
    <!-- Recent Activity -->
    <div class="section">
        <div class="section-title">Recent Consumption Activity</div>';
        
        if (count($data['recentConsumptions']) > 0) {
            $html .= '<table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Drug Name</th>
                        <th>Quantity</th>
                        <th>Logged By</th>
                    </tr>
                </thead>
                <tbody>';
            
            foreach ($data['recentConsumptions'] as $consumption) {
                $html .= '<tr>
                    <td>' . $consumption->getConsumedAt()->format('Y-m-d H:i') . '</td>
                    <td>' . htmlspecialchars($consumption->getDrug()->getName()) . '</td>
                    <td><strong>' . $consumption->getQuantity() . '</strong></td>
                    <td>' . htmlspecialchars($consumption->getLoggedBy()) . '</td>
                </tr>';
            }
            
            $html .= '</tbody></table>';
        } else {
            $html .= '<div class="no-data">No recent consumption logs available.</div>';
        }
        
        $html .= '</div>
    
    <!-- Report Summary -->
    <div class="section">
        <div class="section-title">Report Summary & Recommendations</div>
        <div class="explanation-box">
            <h4>Key Insights</h4>
            <p>• <strong>Inventory Health:</strong> ' . ($data['lowStockCount'] > 0 ? $data['lowStockCount'] . ' drug(s) require immediate attention due to low stock levels.' : 'All drugs are currently above their reorder thresholds.') . '</p>
            <p>• <strong>Consumption Patterns:</strong> ' . count($data['topConsumedDrugs']) . ' drug(s) have been tracked for consumption analysis, providing valuable insights into usage patterns.</p>
            <p>• <strong>Prediction Coverage:</strong> ' . count($data['allStockoutPredictions']) . ' drug(s) have been analyzed for stockout risk using AI/ML predictions.</p>
        </div>
        
        <div class="explanation-box">
            <h4>Recommended Actions</h4>
            <p>1. <strong>Immediate:</strong> Review and address all urgent stockout predictions (≤7 days) to prevent service disruptions.</p>
            <p>2. <strong>Short-term:</strong> Monitor warning predictions (8-14 days) and plan procurement accordingly.</p>
            <p>3. <strong>Ongoing:</strong> Regularly review consumption patterns to optimize reorder points and safety stock levels.</p>
            <p>4. <strong>Strategic:</strong> Use consumption analytics to identify trends and adjust inventory management strategies.</p>
        </div>
    </div>
    
    <div class="footer">
        <p>This report was automatically generated by MediStock Inventory Management System</p>
        <p>For questions or support, please contact your system administrator</p>
    </div>
</body>
</html>';
        
        return $html;
    }
}









