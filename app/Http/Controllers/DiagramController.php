<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\PatientStatistic;

class DiagramController extends Controller
{
    /**
     * Εμφάνιση της φόρμας για την επιλογή διαγραμμάτων.
     * Επιστρέφει το view 'diagrams' όπου ο χρήστης μπορεί να επιλέξει τύπο διαγράμματος και χρονικό διάστημα.
     */
    public function showDiagramForm()
    {
        return view('diagrams');
    }

    /**
     * Φορτώνει δεδομένα για το επιλεγμένο διάγραμμα με βάση τον τύπο διαγράμματος και το χρονικό διάστημα.
     */
    public function loadDiagram(Request $request)
    {
        // Επικύρωση (Validate) των εισαγόμενων ημερομηνιών και διαμόρφωσή τους σε ημερομηνίες έναρξης και λήξης.
        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        // Ανάκτηση του ID του συνδεδεμένου χρήστη.
        $userId = Auth::user()->id;

        // Ανάκτηση του επιλεγμένου τύπου διαγράμματος.
        $diagramType = $request->input('diagram_type');

        // Διαχείριση του διαγράμματος "Time in Range"
        if ($diagramType == 'time_in_range') {
            // Ανάκτηση δεδομένων γλυκόζης (glucose_old) και χρονικού διαστήματος από τον πίνακα patients_statistics για τον συγκεκριμένο ασθενή.
            $patientStatistics = PatientStatistic::whereHas('patient', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at', 'ASC')
                ->get(['glucose_old', 'created_at']);

            // Επιστροφή μηνύματος αν δεν υπάρχουν δεδομένα.
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

            // Επεξεργασία δεδομένων για το διάγραμμα.
            $glucoseData = $patientStatistics->pluck('glucose_old')->toArray();
            $timeData = $patientStatistics->pluck('created_at')->map(function ($item) {
                return $item->format('Y-m-d H:i');
            })->toArray();

            // Υπολογισμός ποσοστoύ των τιμών γλυκόζης που βρισκόντουσαν εντός μίας συγκεκριμένης κάθε φορά γλυκαιμικής ζώνης.
            $totalPoints = count($glucoseData);
            $under80 = (count(array_filter($glucoseData, function ($g) { return $g < 80; })) / $totalPoints) * 100;
            $between80and180 = (count(array_filter($glucoseData, function ($g) { return $g >= 80 && $g <= 180; })) / $totalPoints) * 100;
            $above180 = (count(array_filter($glucoseData, function ($g) { return $g > 180; })) / $totalPoints) * 100;

            // Επιστροφή του view με τα δεδομένα του διαγράμματος "Time In Range".
            return view('diagrams', [
                'glucoseData' => $glucoseData,
                'timeData' => $timeData,
                'under80' => $under80,
                'between80and180' => $between80and180,
                'above180' => $above180
            ]);

            // Διαχείριση του διαγράμματος "Glucose-Insulin Correlation"
        } elseif ($diagramType === 'glucose_insulin_correlation') {
            // Ανάκτηση δεδομένων γλυκόζης και δόσης ινσουλίνης.
            $patientStatistics = PatientStatistic::whereHas('patient', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->orderBy('created_at', 'ASC')
                ->get(['glucose_old', 'insulin_dose']);

            // Επιστροφή μηνύματος αν δεν υπάρχουν δεδομένα.
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

            // Επεξεργασία δεδομένων για το διάγραμμα.
            $glucoseData = $patientStatistics->pluck('glucose_old')->toArray();
            $insulinData = $patientStatistics->pluck('insulin_dose')->toArray();

            // Υπολογισμός γραμμικής παλινδρόμησης (Linear Regression)
            $regressionData = $this->performLinearRegression($glucoseData, $insulinData);

            // Επιστροφή του view με τα δεδομένα του διαγράμματος "Glucose-Insulin Correlation".
            return view('diagrams', [
                'glucoseData' => $glucoseData,
                'insulinData' => $insulinData,
                'regressionData' => $regressionData
            ]);
        }

        // Επιστροφή μηδενικού αν δεν αναγνωρίζεται ο τύπος διαγράμματος.
        return 0;
    }

    /**
     * Υπολογισμός γραμμικής παλινδρόμησης (Linear Regression).
     */
    private function performLinearRegression($x, $y)
    {
        // Υπολογίζει τον αριθμό των τιμών στον πίνακα x (μέγεθος του δείγματος δεδομένων).
        $n = count($x);

        // Αν δεν υπάρχουν δεδομένα, επιστρέφουμε προεπιλεγμένες τιμές (0).
        if ($n == 0) {
            return [
                'slope' => 0,
                'intercept' => 0,
                'predictedY' => [] // Κενός πίνακας για τις προβλεπόμενες τιμές Y.
            ];
        }

        // Υπολογισμός του αθροίσματος όλων των τιμών του x.
        $sum_x = array_sum($x);
        // Υπολογισμός του αθροίσματος όλων των τιμών του y.
        $sum_y = array_sum($y);

        // Υπολογισμός του αθροίσματος των τετραγώνων των τιμών του x.
        $sum_x_squared = array_sum(array_map(function($val)
        { return $val * $val; // Κάθε τιμή x πολλαπλασιάζεται με τον εαυτό της.
            }, $x));

        // Υπολογισμός του αθροίσματος των γινομένων των τιμών x και y.
        $sum_xy = array_sum(array_map(function($xi, $yi)
        { return $xi * $yi; // Κάθε x πολλαπλασιάζεται με το αντίστοιχο y.
            }, $x, $y));

        // Υπολογισμός του παρονομαστή για τον υπολογισμό του slope.
        $denominator = ($n * $sum_x_squared - $sum_x * $sum_x);

        // Αν ο παρονομαστής είναι 0, αποφεύγουμε τη διαίρεση με το μηδέν.
        if ($denominator == 0) {
            return [
                'slope' => 0,
                'intercept' => 0,
                'predictedY' => []
            ];
        }

        // Υπολογισμός της κλίσης (slope) της γραμμής.
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / $denominator;

        // Υπολογισμός της εκτομής (intercept) της γραμμής.
        $intercept = ($sum_y - $slope * $sum_x) / $n;

        // Υπολογισμός των προβλεπόμενων τιμών Y (predictedY).
        $predictedY = array_map(function($xi) use ($slope, $intercept) {
            return $slope * $xi + $intercept; // Εφαρμόζεται η εξίσωση Y = slope * X + intercept.
        }, $x);

        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'predictedY' => $predictedY
        ];
    }

    /**
     * Εμφάνιση διαγράμματος "Glucose Future Trend Prediction".
     */
    public function showFutureGlucoseTrend()
    {
        $patientId = Auth::user()->patient->id;

        // Κλήση API Python, με το patiend_id, για πρόβλεψη
        $response = Http::get('http://127.0.0.1:5000/predict_future_glucose', [
            'patient_id' => $patientId
        ]);

        // Διαχείριση αποτυχίας κλήσης API.
        if ($response->failed()) {
            return view('glucose_trend_page', [
                'message' => 'Error retrieving data from API',
                'glucoseData' => [],
                'timeData' => [],
                'futureTimeData' => [],
                'predictedFuture' => []
            ]);
        }

        // Ανάκτηση δεδομένων από την απόκριση API.
        $data = $response->json();

        // Επιστροφή του view με τα δεδομένα του διαγράμματος "Glucose Future Trend Prediction".
        return view('glucose_trend_page', [
            'glucoseData' => $data['glucoseData'] ?? [],
            'timeData' => $data['timeData'] ?? [],
            'futureTimeData' => $data['futureTimeData'] ?? [],
            'predictedFuture' => $data['predictedFuture'] ?? [],
            'message' => $data['error'] ?? null
        ]);
    }
}
