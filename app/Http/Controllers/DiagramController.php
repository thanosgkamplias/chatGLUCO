<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PatientStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DiagramController extends Controller
{
    public function showDiagramForm()
    {
        return view('diagrams');
    }

    public function loadDiagram(Request $request)
    {
        // Validate input dates
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // Get current logged-in patient's ID
        $userId = Auth::user()->id;

        // Determine which diagram type is requested
        $diagramType = $request->input('diagram_type');

        // Time in Range Diagram
        if ($diagramType == 'time_in_range') {
            // Query the patient statistics for glucose data
            $patientStatistics = PatientStatistic::whereHas('patient', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at', 'ASC')
                ->get(['glucose_new', 'created_at']);

            // If no data, return the view with a message
            if ($patientStatistics->isEmpty()) {
                return view('diagrams', [
                    'message' => 'No data available for the selected date range.',
                    'glucoseData' => [],
                    'timeData' => [],
                    'under80' => 0,
                    'between80and180' => 0,
                    'above180' => 0
                ]);
            }

            // Prepare data for the plot
            $glucoseData = $patientStatistics->pluck('glucose_new')->toArray();
            $timeData = $patientStatistics->pluck('created_at')->map(function ($item) {
                return $item->format('Y-m-d H:i');
            })->toArray();

            // Calculate the percentages of time spent in each range
            $totalPoints = count($glucoseData);
            $under80 = (count(array_filter($glucoseData, function ($g) { return $g < 80; })) / $totalPoints) * 100;
            $between80and180 = (count(array_filter($glucoseData, function ($g) { return $g >= 80 && $g <= 180; })) / $totalPoints) * 100;
            $above180 = (count(array_filter($glucoseData, function ($g) { return $g > 180; })) / $totalPoints) * 100;

            // Return view with data for Time in Range
            return view('diagrams', [
                'glucoseData' => $glucoseData,
                'timeData' => $timeData,
                'under80' => $under80,
                'between80and180' => $between80and180,
                'above180' => $above180
            ]);

        } elseif ($diagramType === 'glucose_insulin_correlation') {
            // Query the patient statistics for glucose and insulin data
            $patientStatistics = PatientStatistic::whereHas('patient', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at', 'ASC')
                ->get(['glucose_new', 'insulin_dose']);

            // If no data, return the view with empty data and a message
            if ($patientStatistics->isEmpty()) {
                return view('diagrams', [
                    'message' => 'No data available for the selected date range.',
                    'glucoseData' => [],
                    'insulinData' => [],
                    'regressionData' => [
                        'slope' => 0,
                        'intercept' => 0,
                        'predictedY' => []
                    ]
                ]);
            }

            // Prepare data for the plot
            $glucoseData = $patientStatistics->pluck('glucose_new')->toArray();
            $insulinData = $patientStatistics->pluck('insulin_dose')->toArray();

            // Perform Linear Regression Calculation (you need to ensure this method is defined)
            $regressionData = $this->performLinearRegression($glucoseData, $insulinData);


            // Return view with regression data
            return view('diagrams', [
                'glucoseData' => $glucoseData,
                'insulinData' => $insulinData,
                'regressionData' => $regressionData
            ]);

        }
        return 0 ;
    }

// Perform linear regression function

    private function performLinearRegression($x, $y)
    {

        $n = count($x);
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        $sum_x_squared = array_sum(array_map(function($val) { return $val * $val; }, $x));
        $sum_xy = array_sum(array_map(function($xi, $yi) { return $xi * $yi; }, $x, $y));

        // Calculate slope (A) and intercept (B)
        $denominator = ($n * $sum_x_squared - $sum_x * $sum_x);
        if ($denominator == 0) {
            // Avoid division by zero, return default values if regression can't be calculated
            return [
                'slope' => 0,
                'intercept' => 0,
                'predictedY' => []
            ];
        }

        $slope = ($n * $sum_xy - $sum_x * $sum_y) / $denominator;
        $intercept = ($sum_y - $slope * $sum_x) / $n;

        // Predict Y values
        $predictedY = array_map(function($xi) use ($slope, $intercept) {
            return $slope * $xi + $intercept;
        }, $x);

        // Return an array with slope, intercept, and predictedY
        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'predictedY' => $predictedY
        ];
    }








//    private function createGlucosePlot($glucoseData, $timeData)
//    {
//        include(app_path('Charts/jpgraph-4.4.2/src/jpgraph.php'));
//        include(app_path('Charts/jpgraph-4.4.2/src/jpgraph_line.php'));
//        include(app_path('Charts/jpgraph-4.4.2/src/jpgraph_plotmark.inc.php'));  // For markers
//
//// Define thresholds
//        $lowThreshold = 80;
//        $highThreshold = 180;
//
//        // Create the graph
//        $graph = new \Graph(800, 600);
//        $graph->SetScale('textlin', 0, 400);
//        $graph->img->SetMargin(60, 30, 30, 60);
//
//        // Set titles
//        $graph->title->Set('Glucose Levels Throughout the Day');
//        $graph->xaxis->title->Set('Time');
//        $graph->yaxis->title->Set('Glucose Level (mg/dL)');
//
//        // Set background gradient
//        $graph->SetBackgroundGradient('white', 'lightgray', 2);
//
//        // Customize X-axis labels
//        $graph->xaxis->SetTickLabels($timeData);
//        $graph->xaxis->SetLabelAngle(50);
//
//        // Create a line plot for glucose levels
//        $linePlot = new \LinePlot($glucoseData);
//        $graph->Add($linePlot);
//        $linePlot->SetColor('black');
//        $linePlot->SetWeight(2);
//        $linePlot->SetLegend('Glucose Level');
//
//        // Add markers for points on the graph
//        $linePlot->mark->SetType(MARK_FILLEDCIRCLE);
//        $linePlot->mark->SetFillColor('black');
//        $linePlot->mark->SetWidth(4);
//
//        // Highlight different ranges with colors
//        $graph->AddBand(new \PlotBand(HORIZONTAL, BAND_RDIAG, 0, $lowThreshold, 'yellow', 0.1)); // Below 80 mg/dL
//        $graph->AddBand(new \PlotBand(HORIZONTAL, BAND_RDIAG, $lowThreshold, $highThreshold, 'green', 0.1)); // Between 80-180 mg/dL
//        $graph->AddBand(new \PlotBand(HORIZONTAL, BAND_RDIAG, $highThreshold, 400, 'red', 0.1)); // Above 180 mg/dL
//
//        // Add horizontal lines for low and high thresholds
//        $graph->yaxis->SetWeight(2);
//        $graph->AddLine(new \PlotLine(HORIZONTAL, $lowThreshold, 'purple', 2, 'dashed'));
//        $graph->AddLine(new \PlotLine(HORIZONTAL, $highThreshold, 'red', 2, 'dashed'));
//
//        // Annotate the points where glucose is below 80 or above 180
//        foreach ($glucoseData as $index => $glucose) {
//            if ($glucose < $lowThreshold || $glucose > $highThreshold) {
//                $graph->AddText(new \Text($glucose, $index, $glucose < $lowThreshold ? $glucose - 10 : $glucose + 10, 'red'));
//            }
//        }
//
//        // Set legend position
//        $graph->legend->SetPos(0.5, 0.97, 'center', 'bottom');
//        $graph->legend->SetFrameWeight(1);
//
//        // Save the image
//        if (!Storage::exists('public')) {
//            Storage::makeDirectory('public');
//        }
//
//        $filename = 'glucose_levels_' . time() . '.png';
//        $filePath = storage_path('app/public/' . $filename);
//        $graph->Stroke($filePath);
//
//        return 'storage/' . $filename;
//    }

}

