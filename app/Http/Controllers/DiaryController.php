<?php

namespace App\Http\Controllers;

use App\Exports\DiaryExport;
use App\Models\Patient;
use App\Models\PatientStatistic;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache; // Add this line
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

// Διαχειριση Χρηστών του συστήματος
class DiaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('checkIfPatient');
    }

    public function ShowDiary(){
        $data = PatientStatistic::whereHas('patient', function ($query) {
            $query->where('user_id', Auth::user()->id);
        })->orderBy('created_at','desc')->paginate(15);

        return view('Diary',['data'=>$data]);
    }

    public function AddNewRow(Request $request){

        // Define validation rules
        $validator = Validator::make($request->all(), [
            'glucose_old' => 'required|numeric|min:0',  // Ensure glucose_old is a numeric value greater than or equal to 0
            'insulin' => 'required|numeric|min:0', // Ensure insulin is a numeric value greater than or equal to 0
            'glucose_new' => 'required|numeric|min:0',  // Ensure glucose_new is a numeric value greater than or equal to 0
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
        // Validation passed, create the new PatientStatistic entry
        PatientStatistic::create([
            'patient_id' => Auth::user()->patient->id,
            'glucose_old' => $request->input('glucose_old'),
            'insulin_dose' => $request->input('insulin'), // Fixed from 'insulin' to 'insulin_dose'
            'food_carbo' => $food_carbo,
            'glucose_new' => $request->input('glucose_new'),
            'weight' => Auth::user()->patient->weight,
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('message', "The new line has been successfully added!");
    }

    public function DeleteRow(Request $request){

        $id=$request->input('id');

        PatientStatistic::where('id', $id)->delete();
        return redirect()->back()->with('message',"The new line has been successfully Deleted!");
    }

    public function UpdateRow(Request $request){
        $validator = Validator::make($request->all(), [
            'id'=>'required',
            'glucose_old' => 'required|numeric|min:0',  // Ensure glucose_old is a numeric value greater than or equal to 0
            'insulin' => 'required|numeric|min:0', // Ensure insulin is a numeric value greater than or equal to 0
            'glucose_new' => 'required|numeric|min:0',  // Ensure glucose_new is a numeric value greater than or equal to 0
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
        // Validation passed, create the new PatientStatistic entry
        PatientStatistic::where('id',$request->input('id'))->update([
            'patient_id' => Auth::user()->patient->id,
            'glucose_old' => $request->input('glucose_old'),
            'insulin_dose' => $request->input('insulin'), // Fixed from 'insulin' to 'insulin_dose'
            'food_carbo' => $food_carbo,
            'glucose_new' => $request->input('glucose_new'),
            'weight' => Auth::user()->patient->weight,
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('message', "The new line has been successfully updated!");
    }

    public function getFoodList(Request $request)
    {
        $food = $request->input('food');

        if (empty($food)) {
            return response()->json(['error' => 'Food query is required'], 400);
        }

        $apiUrl = 'https://trackapi.nutritionix.com/v2/natural/nutrients';
        $appId = env('NUTRITIONIX_APP_ID');
        $apiKey = env('NUTRITIONIX_API_KEY');

        try {
            $response = Http::withHeaders([
                'x-app-id' => $appId,
                'x-app-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false, // Disable SSL verification
            ])->post($apiUrl, [
                'query' => $food,
                'timezone' => 'US/Eastern'
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                \Log::error('API Error: ' . $response->status() . ' - ' . $response->body());
                return response()->json(['error' => 'Unable to fetch data from Nutritionix API'], $response->status());
            }
        } catch (\Exception $e) {
            \Log::error('Exception in getFoodList: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }
    }

    public function getAutocomplete(Request $request)
    {
        $food = $request->input('food'); // Fetch the 'food' input from the request

        if (empty($food)) {
            return response()->json(['error' => 'Food query is required'], 400);
        }

        $cacheKey = 'autocomplete_' . strtolower($food);

        // Check if the results are cached
        $foodNames = Cache::remember($cacheKey, 60, function() use ($food) {
            $apiUrl = 'https://trackapi.nutritionix.com/v2/search/instant'; // Instant Search API URL
            $appId = env('NUTRITIONIX_APP_ID');
            $apiKey = env('NUTRITIONIX_API_KEY');

            // Make a GET request to the Nutritionix Instant Search API
            $response = Http::withHeaders([
                'x-app-id' => $appId,
                'x-app-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])->withOptions([
                'verify' => false, // Disable SSL verification
            ])->get($apiUrl, [
                'query' => $food,  // Pass the food query as a parameter
                'self' => true,    // Option to include branded and common foods
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Combine both common and branded foods (if needed)
                $foods = array_merge(
                    $data['common'] ?? [],  // Fetch common foods (if present)
                    $data['branded'] ?? []  // Fetch branded foods (if present)
                );

                // Extract only the 'food_name' and limit to 10 results
                $foodNames = array_slice(array_map(function ($item) {
                    return $item['food_name'] ?? '';  // Handle both types
                }, $foods), 0, 10);

                return $foodNames; // Return the limited food names
            } else {
                \Log::error('API Error: ' . $response->body()); // Log any errors
                return [];
            }
        });

        return response()->json(['food_names' => $foodNames]);
    }

    public function ExportData($patientId){

        $userName=Auth::user()->lastname." ". Auth::user()->firstname;
        $userBirth=Auth::user()->patient->birth_at;
        $userGender=Auth::user()->patient->gender;
        $userWeight=Auth::user()->patient->weight;
        $userDiagnosis= Auth::user()->patient->diagnosis;

        $filename=Auth::user()->lastname."_". Auth::user()->firstname.".xlsx";

        return Excel::download(new DiaryExport($patientId,$userName,$userBirth,$userGender,$userWeight,$userDiagnosis),$filename);
        //        return redirect()->back();
    }
}
