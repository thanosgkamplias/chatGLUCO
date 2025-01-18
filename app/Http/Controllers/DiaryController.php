<?php

namespace App\Http\Controllers;

use App\Exports\DiaryExport;
use App\Models\PatientStatistic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;

// Κλάση για τη διαχείριση του ημερολογίου των ασθενών
class DiaryController extends Controller
{
    // Constructor: Εξασφαλίζει ότι ο χρήστης είναι αυθεντικοποιημένος και είναι ασθενής
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkIfPatient');
    }

    // Εμφάνιση του ημερολογίου του ασθενούς
    public function ShowDiary(){
        $data = PatientStatistic::whereHas('patient', function ($query) {
            $query->where('user_id', Auth::user()->id); // Φιλτράρισμα με βάση τον συνδεδεμένο χρήστη
        })->orderBy('created_at','desc')->paginate(15); // Ταξινόμηση κατά φθίνουσα ημερομηνία και χρήση pagination

        return view('Diary',['data'=>$data]); // Επιστροφή δεδομένων στο αντίστοιχο view
    }

    // Προσθήκη νέας γραμμής στο ημερολόγιο
    public function AddNewRow(Request $request){

        // Κανόνες επικύρωσης των εισερχόμενων δεδομένων
        $validator = Validator::make($request->all(), [
            'glucose_old' => 'required|numeric|min:0',  // Το glucose_old πρέπει να είναι αριθμός ≥ 0
            'timestamp'=> 'required|date', // Το timestamp πρέπει να είναι έγκυρη ημερομηνία
        ]);

        // Έλεγχος αν αποτυγχάνει η επικύρωση
        if ($validator->fails()) {
            return redirect()->back()->with('warning', "There are missing some information.");
        }

        // Ανάκτηση και αποθήκευση της τιμής food_carbo, αν έχει εισαχθεί
        $food_carbo=0;
        if(($request->input('food_carbo') !== null))
        {
            $food_carbo=$request->input('food_carbo');
        }

        // Ανάκτηση των τιμών glucose_new και insulin_dose ή ορισμός προεπιλεγμένης τιμής 0
        $glucose_new = $request->input('glucose_new') ?? 0;
        $insulin_dose = $request->input('insulin_dose') ?? 0;

        $datetime = Carbon::parse($request->input('timestamp')); // Μετατροπή timestamp σε αντικείμενο Carbon

        // Έλεγχος για ήδη υπάρχουσα εγγραφή με ίδιο timestamp
        $exists = PatientStatistic::where('patient_id', Auth::user()->patient->id)
            ->where('created_at', $datetime->toDateTimeString())
            ->exists();

        if ($exists) {
            return redirect()->back()->with('warning', "There is already a record with the same date and time.");
        }

        // Validation passed, δημιουργία νέας εγγραφής στη βάση δεδομένων
        PatientStatistic::create([
            'patient_id' => Auth::user()->patient->id,
            'glucose_old' => $request->input('glucose_old'),
            'insulin_dose' => $insulin_dose,
            'food_carbo' => $food_carbo,
            'glucose_new' => $glucose_new,
            'weight' => Auth::user()->patient->weight,
            'created_at' => $datetime->toDateTimeString(),
        ]);

        // Επιτυχής προσθήκη εγγραφής
        return redirect()->back()->with('message', "The new line has been successfully added!");
    }

    // Διαγραφή γραμμής από το ημερολόγιο
    public function DeleteRow(Request $request){

        $id=$request->input('id'); // Ανάκτηση του id της εγγραφής που θα διαγραφεί

        PatientStatistic::where('id', $id)->delete(); // Διαγραφή εγγραφής από τη βάση
        return redirect()->back()->with('message',"The new line has been successfully Deleted!");
    }

    // Ενημέρωση γραμμής στο ημερολόγιο
    public function UpdateRow(Request $request){
        // Κανόνες επικύρωσης
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'glucose_old' => 'required|numeric|min:0',
            'timestamp'=> 'required|date',
        ]);

        // Check if the validation fails
        if ($validator->fails()) {
            return redirect()->back()->with('warning', "There are missing some information.");
        }

        $food_carbo=0;
        if(($request->input('food_carbo') !== null))
        {
            $food_carbo=$request->input('food_carbo');
        }

        $glucose_new = $request->input('glucose_new') ?? 0;
        $insulin_dose = $request->input('insulin_dose') ?? 0;

        $datetime = Carbon::parse($request->input('timestamp'));
        $rowId = $request->input('id'); // Ανάκτηση ID εγγραφής

        // Έλεγχος για ήδη υπάρχουσα εγγραφή με ίδιο timestamp (εκτός από αυτήν που ενημερώνουμε)
        $exists = PatientStatistic::where('patient_id', Auth::user()->patient->id)
            ->where('created_at', $datetime->toDateTimeString())
            ->where('id', '!=', $rowId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('warning', "There is already a record with the same date and time.");
        }

        // Validation passed, ενημέρωση της εγγραφής
        PatientStatistic::where('id', $rowId)->update([
            'patient_id' => Auth::user()->patient->id,
            'glucose_old' => $request->input('glucose_old'),
            'insulin_dose' => $insulin_dose,
            'food_carbo' => $food_carbo,
            'glucose_new' => $glucose_new,
            'weight' => Auth::user()->patient->weight,
            'created_at' => $datetime->toDateTimeString(),
        ]);
        // Redirect back with a success message
        return redirect()->back()->with('message', "The new line has been successfully updated!");
    }

    // Λήψη δεδομένων τροφίμων από το Nutritionix API
    public function getFoodList(Request $request)
    {
        // Λήψη ονόματος τροφής από το request
        $food = $request->input('food');

        if (empty($food)) {
            // Επιστροφή σφάλματος αν λείπει η τροφή
            return response()->json(['error' => 'Food query is required'], 400);
        }

        $apiUrl = 'https://trackapi.nutritionix.com/v2/natural/nutrients'; // API URL
        $appId = env('NUTRITIONIX_APP_ID'); // Ανάκτηση ID εφαρμογής από το .env
        $apiKey = env('NUTRITIONIX_API_KEY'); // Ανάκτηση κλειδιού API από το .env

        try {
            $response = Http::withHeaders([
                'x-app-id' => $appId,
                'x-app-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false, // Disable SSL verification
            ])->post($apiUrl, [
                'query' => $food, // Εισαγωγή του ονόματος τροφής ως παράμετρος στο αίτημα
                'timezone' => 'US/Eastern'
            ]);

            // Έλεγχος αν το αίτημα στο API ήταν επιτυχές
            if ($response->successful()) {
                return response()->json($response->json()); // Επιστροφή των δεδομένων από το API
            } else {
                \Log::error('API Error: ' . $response->status() . ' - ' . $response->body()); // Καταγραφή σφάλματος
                return response()->json(['error' => 'Unable to fetch data from Nutritionix API'], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Exception in getFoodList: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }

    // Αυτή η συνάρτηση υλοποιεί τη λειτουργία αυτόματης συμπλήρωσης (autocomplete) για τα ονόματα τροφίμων,
    // χρησιμοποιώντας το Nutritionix API.
    public function getAutocomplete(Request $request)
    {
        // Λήψη της εισόδου 'food' από το αίτημα (request)
        $food = $request->input('food');

        // Έλεγχος αν η είσοδος είναι κενή
        if (empty($food)) {
            // Επιστροφή σφάλματος αν δεν έχει δοθεί όνομα τροφής
            return response()->json(['error' => 'Food query is required'], 400);
        }

        // Δημιουργία ενός μοναδικού κλειδιού για το cache βασισμένο στο όνομα της τροφής
        $cacheKey = 'autocomplete_' . strtolower($food);

        // Έλεγχος αν τα αποτελέσματα βρίσκονται ήδη στην προσωρινή μνήμη (cache)
        $foodNames = Cache::remember($cacheKey, 60, function() use ($food) {
            $apiUrl = 'https://trackapi.nutritionix.com/v2/search/instant'; // Instant Search API URL

            // Ανάκτηση των credentials από το αρχείο .env
            $appId = env('NUTRITIONIX_APP_ID');
            $apiKey = env('NUTRITIONIX_API_KEY');

            // Επικοινωνία με το API μέσω GET request
            $response = Http::withHeaders([
                'x-app-id' => $appId, // Προσθήκη του App ID στην επικεφαλίδα του αιτήματος
                'x-app-key' => $apiKey, // Προσθήκη του API Key στην επικεφαλίδα του αιτήματος
                'Content-Type' => 'application/json', // Ορισμός του τύπου περιεχομένου
            ])->withOptions([
                'verify' => false, // Disable SSL verification
            ])->get($apiUrl, [
                'query' => $food,  // Προσθήκη της τροφής ως παράμετρο στο αίτημα
                'self' => true,    // Εμφάνιση τόσο κοινών όσο και επώνυμων τροφίμων
            ]);

            // Έλεγχος αν το αίτημα ήταν επιτυχές
            if ($response->successful()) {
                $data = $response->json(); // Ανάκτηση δεδομένων από το API

                // Συνδυασμός των κοινών και επώνυμων τροφίμων, αν υπάρχουν
                $foods = array_merge(
                    $data['common'] ?? [],
                    $data['branded'] ?? []
                );

                // Εξαγωγή μόνο των ονομάτων των τροφίμων ('food_name') και περιορισμός στα 10 αποτελέσματα
                $foodNames = array_slice(array_map(function ($item) {
                    return $item['food_name'] ?? '';  // Ανάκτηση του 'food_name' ή κενής τιμής αν δεν υπάρχει
                }, $foods), 0, 10);

                return $foodNames; // Επιστροφή των ονομάτων των τροφίμων
            } else {
                // Καταγραφή σφάλματος σε περίπτωση αποτυχίας του αιτήματος
                \Log::error('API Error: ' . $response->body());
                return []; // Επιστροφή κενής λίστας
            }
        });

        // Επιστροφή των ονομάτων τροφίμων σε μορφή JSON
        return response()->json(['food_names' => $foodNames]);
    }

    // Εξαγωγή δεδομένων ημερολογίου σε Excel
    public function ExportData($patientId){

        $userName=Auth::user()->lastname." ". Auth::user()->firstname;
        $userBirth=Auth::user()->patient->birth_at;
        $userGender=Auth::user()->patient->gender;
        $userWeight=Auth::user()->patient->weight;
        $userDiagnosis= Auth::user()->patient->diagnosis;

        // Δημιουργία ονόματος αρχείου
        $filename=Auth::user()->lastname."_". Auth::user()->firstname.".xlsx";

        // Εξαγωγή δεδομένων μέσω του DiaryExport
        return Excel::download(new DiaryExport($patientId,$userName,$userBirth,$userGender,$userWeight,$userDiagnosis),$filename);
        //        return redirect()->back();
    }
}
