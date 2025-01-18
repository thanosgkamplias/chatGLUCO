<?php

namespace App\Http\Controllers;

use App\Models\PatientStatistic;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PredictionController extends Controller
{
    /**
     * Μέθοδος για την πρόβλεψη (υπολογισμό) δόσης ινσουλίνης.
     * Υποστηρίζει τόσο απλές μεθόδους (Sliding Scale + Carbo)
     * όσο και επικοινωνία με το Flask API.
     */
    public function PredictDose(Request $request)
    {
        // Έλεγχος εγκυρότητας (validation) για τα πεδία που έρχονται από το Request.
        $validatedData = Validator::make($request->all(), [
            'glucose_old' => 'required|numeric',  // Η παλιά τιμή γλυκόζης είναι υποχρεωτική και αριθμητική
            'algorithm' => 'required|string',     // Το επιλεγμένο αλγοριθμικό μοντέλο είναι υποχρεωτικό
        ]);

        // Εάν η επικύρωση αποτύχει, επιστρέφει ειδοποίηση (warning) και ανακατευθύνει πίσω
        if ($validatedData->fails()) {
            return redirect()->back()->with(
                'warning',
                "In order to calculate the insulin dose prediction, the fields 'glucose_old' and 'select algorithm' must be filled out."
            );
        }

        // Λαμβάνουμε το ID του ασθενούς και το βάρος του από τον authenticated χρήστη
        $patient_id = Auth::user()->patient->id;
        $patient_weight = Auth::user()->patient->weight;

        // Μετράμε πόσες εγγραφές υπάρχουν ήδη καταχωρημένες για τον ασθενή
        $countstats = PatientStatistic::where('patient_id', $patient_id)->count();

        // Εάν υπάρχουν λιγότερες από 4 εγγραφές για τον ασθενή,
        // επιστρέφουμε έναν απλό υπολογισμό: (βάρος / 2) / 3
        // ως μία αρχική/προσεγγιστική δόση ινσουλίνης.
        if ($countstats < 4) {
            $insulin_dose = ($patient_weight / 2) / 3;
            return response()->json(['message' => $insulin_dose]);
        }

        // Υπολογισμός ηλικίας του ασθενούς με βάση το έτος γέννησης
        $currentYear = Carbon::now()->format('Y');
        $birthYear = Carbon::parse(Auth::user()->patient->birth_at)->format('Y');
        $patient_age = $currentYear - $birthYear;

        // Λαμβάνονται οι τιμές του request, όπως η νέα γλυκόζη (προαιρετική),
        // το φύλο, η διάγνωση κ.λπ.
        $glucose_new = (float)$request->input('glucose_new');
        $patient_gender = Auth::user()->patient->gender;
        $patient_diagnosis = Auth::user()->patient->diagnosis;

        // Βασικές παράμετροι εισόδου για τον υπολογισμό ινσουλίνης
        $glucose_old = (float)$request->input('glucose_old');  // Παλιά τιμή γλυκόζης
        $food_carbo = (float)$request->input('food_carbo');    // Γραμμάρια υδατανθράκων στο γεύμα
        $algorithm = $request->input('algorithm');             // Επιλεγμένος αλγόριθμος υπολογισμού

        // Δημιουργούμε ένα array με τα δεδομένα που θα χρειαστούν
        // είτε για τοπικό υπολογισμό είτε για αποστολή στο Flask API
        $data = [
            'glucose_old' => $glucose_old,
            'glucose_new' => $glucose_new,
            'food_carbo' => $food_carbo,
            'algorithm' => $algorithm,
            'patient_id' => $patient_id,
            'weight' => $patient_weight,
            'age' => $patient_age,
            'gender' => $patient_gender,
            'diagnosis' => $patient_diagnosis,
        ];

        // Υλοποίηση του Sliding Scale + Carbo Calculation απευθείας στο Laravel
        // χωρίς να καλείται το Flask API.
        if ($algorithm === 'sliding_scale_carbo') {
            // Sliding Scale Calculation
            // Εάν το "glucose_old" > 100, υπολογίζουμε πόσα mg/dL πάνω από το 100 είναι
            $extra_units = 0;
            if ($glucose_old > 100) {
                $over_100 = $glucose_old - 100;
                // Για κάθε 50 mg/dL πάνω από το 100, προσθέτουμε 2 μονάδες ινσουλίνης.
                $extra_units = ($over_100 * 2) / 50;
            }

            // Carbo Calculation:
            // Υποθέτουμε λόγο ινσουλίνης προς υδατάνθρακες (ICR) 1:10
            // Δηλαδή 1 μονάδα ινσουλίνης ανά 10g υδατανθράκων.
            $insulin_for_carbs = $food_carbo / 10;

            // Το άθροισμα των δύο μεθόδων μας δίνει τη συνολική προτεινόμενη δόση
            $total_dose = $extra_units + $insulin_for_carbs;

            // Επιστρέφουμε τη δόση ως JSON απάντηση (σε AJAX κλήση)
            return response()->json(['message' => $total_dose]);
        }

        // Εάν δεν χρησιμοποιείται η τοπική λογική (sliding_scale_carbo),
        // δημιουργούμε έναν Guzzle HTTP client για να καλέσουμε το Flask API
        $client = new Client();

        try {
            // Στέλνουμε αίτημα GET στο Flask API, περνώντας τα δεδομένα μας (query params)
            $response = $client->get('http://localhost:5000/predict_insulin', [
                'query' => $data,
            ]);

            // Αποκωδικοποιούμε την απόκριση (JSON) που λαμβάνουμε από το Flask
            $responseData = json_decode($response->getBody(), true);

            // Εάν η απόκριση περιέχει πεδίο 'error', το επιστρέφουμε στον χρήστη
            if (isset($responseData['error'])) {
                return response()->json(['error' => $responseData['error']], 500);
            }

            // Διαβάζουμε τη δόση ινσουλίνης που προβλέπεται από το Flask (predicted_insulin_dose)
            $final_dose = $responseData['predicted_insulin_dose'];

            // Επιστρέφουμε την τιμή στην κλήση μας με μορφή JSON
            return response()->json(['message' => $final_dose]);
        } catch (\Exception $e) {
            // Σε περίπτωση που κάτι αποτύχει (π.χ. σύνδεση με Flask)
            // επιστρέφουμε το μήνυμα σφάλματος
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Προβολή φόρμας για πρόβλεψη γλυκόζης.
     */
    public function showGlucoseForm()
    {
        // Εμφανίζουμε ένα view που περιέχει τη φόρμα PredictGlucoseNew.
        return view('PredictGlucoseNew');
    }

    /**
     * Μέθοδος για την πρόβλεψη της γλυκόζης μετά το γεύμα, είτε τοπικά είτε μέσω Flask API.
     */
    public function predictGlucose(Request $request)
    {
        // Επικύρωση δεδομένων για την πρόβλεψη γλυκόζης
        $validatedData = Validator::make($request->all(), [
            'glucose_old' => 'required|numeric',  // Η παλιά τιμή γλυκόζης είναι υποχρεωτική και αριθμητική
            'food_carbo' => 'nullable|numeric',   // Οι υδατάνθρακες μπορεί να είναι μηδέν ή αριθμός
            'insulin_dose' => 'required|numeric', // Η χορηγούμενη δόση ινσουλίνης είναι υποχρεωτική
            'calculation_mode' => 'required|string', // Ο τρόπος υπολογισμού (π.χ. τοπικός ή flask_algorithm)
        ]);

        // Αν αποτύχει η επικύρωση, επιστρέφουμε σφάλματα και τα αρχικά inputs
        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }

        // Από τον authenticated χρήστη, παίρνουμε τα στοιχεία του ασθενούς
        $patient = Auth::user()->patient;
        $patient_id = $patient->id;
        $patient_weight = $patient->weight;

        // Υπολογίζουμε την ηλικία και λαμβάνουμε το φύλο & διάγνωση
        $currentYear = Carbon::now()->format('Y');
        $birthYear = Carbon::parse($patient->birth_at)->format('Y');
        $patient_age = $currentYear - $birthYear;
        $patient_gender = $patient->gender;
        $patient_diagnosis = $patient->diagnosis; // Για παράδειγμα "Type I", "Type II"

        // Παίρνουμε τις τιμές εισόδου από τη φόρμα
        $glucose_old = (float)$request->input('glucose_old');
        $food_carbo = (float)$request->input('food_carbo', 0);  // Προεπιλογή 0 αν δεν δόθηκε
        $insulin_dose = (float)$request->input('insulin_dose');
        $calculation_mode = $request->input('calculation_mode');
        $algorithm = $request->input('algorithm');

        // Ορισμός ISF (Insulin Sensitivity Factor):
        // Πόσα mg/dL μειώνει 1 μονάδα ινσουλίνης.
        $ISF = 25;

        // 1ο σενάριο: Τοπικός υπολογισμός της νέας τιμής γλυκόζης
        if ($calculation_mode === 'calculate_new_glucose') {
            // Υποθέτουμε ότι 10g υδατάνθρακες αυξάνουν τη γλυκόζη κατά 25 mg/dL
            // και 1 μονάδα ινσουλίνης μειώνει τη γλυκόζη κατά ISF (25) mg/dL.
            $carb_increase = ($food_carbo / 10) * 25;

            // Υπολογίζουμε τη νέα γλυκόζη:
            // γλυκόζη πριν το γεύμα + αύξηση από υδατάνθρακες - μείωση από ινσουλίνη
            $predicted_glucose_new = $glucose_old + $carb_increase - ($insulin_dose * $ISF);

            // Σε περίπτωση που το αποτέλεσμα είναι < 0, το θέτουμε στο 0
            // για να αποφύγουμε αρνητικές τιμές.
            if ($predicted_glucose_new < 0) {
                $predicted_glucose_new = 0;
            }

            // Επιστρέφουμε ένα view με την προβλεπόμενη τιμή γλυκόζης
            return view('PredictGlucoseNew', [
                'predicted_glucose_new' => $predicted_glucose_new,
                'glucose_old' => $glucose_old,
                'food_carbo' => $food_carbo,
                'insulin_dose' => $insulin_dose,
                'algorithm' => $algorithm,
                'calculation_mode' => $calculation_mode,
            ]);
        }

        // 2ο σενάριο: Υπολογισμός της νέας γλυκόζης μέσω Flask API
        if ($calculation_mode === 'flask_algorithm') {
            // Προετοιμάζουμε τα δεδομένα που θα σταλούν στο Flask API
            $data = [
                'glucose_old' => $glucose_old,
                'food_carbo' => $food_carbo,
                'insulin_dose' => $insulin_dose,
                'algorithm' => $algorithm,
                'patient_id' => $patient_id,
                'weight' => $patient_weight,
                'age' => $patient_age,
                'gender' => $patient_gender,
                'diagnosis' => $patient_diagnosis,
            ];

            // Δημιουργούμε Guzzle client για την HTTP επικοινωνία
            $client = new \GuzzleHttp\Client();

            try {
                // Κάνουμε GET αίτημα στο Flask, περνώντας τα δεδομένα ως query params
                $response = $client->get('http://localhost:5000/predict_glucose', [
                    'query' => $data,
                ]);

                // Αποκωδικοποιούμε την απάντηση από το Flask
                $responseData = json_decode($response->getBody(), true);

                // Εάν υπάρχει σφάλμα, επιστρέφουμε πίσω με μήνυμα
                if (isset($responseData['error'])) {
                    return redirect()->back()->with('error', $responseData['error'])->withInput();
                }

                // Παίρνουμε τη νέα προβλεπόμενη γλυκόζη
                $predicted_glucose_new = round($responseData['predicted_glucose_new']);

                // Επιστρέφουμε το view με την καινούρια τιμή
                return view('PredictGlucoseNew', [
                    'predicted_glucose_new' => $predicted_glucose_new,
                    'glucose_old' => $glucose_old,
                    'food_carbo' => $food_carbo,
                    'insulin_dose' => $insulin_dose,
                    'algorithm' => $algorithm,
                    'calculation_mode' => $calculation_mode,
                ]);
            } catch (\Exception $e) {
                // Σε περίπτωση σφάλματος, ανακατευθύνουμε πίσω με error message
                return redirect()->back()->with('error', 'An error occurred during glucose prediction: ' . $e->getMessage())->withInput();
            }
        }

        // Εάν δεν βρεθεί έγκυρη τιμή για το calculation_mode,
        // επιστρέφουμε σφάλμα στον χρήστη
        return redirect()->back()->with('error', 'Invalid calculation mode selected.')->withInput();
    }
}
