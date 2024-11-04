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
    public function PredictDose(Request $request)
    {
        // Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'glucose_old' => 'required|numeric',
            'algorithm' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->with(
                'warning',
                "In order to calculate the insulin dose prediction, the fields 'glucose_old' and 'select algorithm' must be filled out."
            );
        }

        $patient_id = Auth::user()->patient->id;
        $patient_weight = Auth::user()->patient->weight;

        // Adjusted the query based on assumed relationships
        $countstats = PatientStatistic::where('patient_id', $patient_id)->count();

        if ($countstats < 5) {
            $insulin_dose = ($patient_weight / 2)/3;
            return response()->json(['message' => $insulin_dose]);
        }

        $currentYear = Carbon::now()->format('Y');
        $birthYear = Carbon::parse(Auth::user()->patient->birth_at)->format('Y');
        $patient_age = $currentYear - $birthYear;
        $glucose_new = (float)$request->input('glucose_new') ;
        $patient_gender = Auth::user()->patient->gender;
        $patient_diagnosis = Auth::user()->patient->diagnosis;

        $glucose_old = (float)$request->input('glucose_old');
        $food_carbo = (float)$request->input('food_carbo');
        $algorithm = $request->input('algorithm');



        $data = [
            'glucose_old' =>$glucose_old,
            'glucose_new' => $glucose_new,
            'food_carbo' => $food_carbo,
            'algorithm' => $algorithm,
            'patient_id' => $patient_id,
            'weight' => $patient_weight,
            'age' => $patient_age,
            'gender' => $patient_gender,
            'diagnosis' => $patient_diagnosis,
        ];


        // Implement Sliding Scale + Carbo Calculation as a separate method
        if ($algorithm === 'sliding_scale_carbo') {
            // Sliding Scale Calculation
            $extra_units = 0;
            if ($glucose_old > 100) {
                $over_100 = $glucose_old - 100;
                $extra_units = (int)floor($over_100 / 50) * 2; // Add 2 units for every 50 mg/dL over 100
            }

            // Carbo Calculation
            $insulin_for_carbs = $food_carbo / 10; // Assuming Insulin-to-Carb Ratio (ICR) of 1:10

            // Total insulin dose is the sum of Sliding Scale and Carbo Calculation
            $total_dose = $extra_units + $insulin_for_carbs;

            return response()->json(['message' => $total_dose]);
        }

        // Create a Guzzle HTTP client
        $client = new Client();

        try {
            // Send a GET request to the Flask API with query parameters
            $response = $client->get('http://localhost:5000/predict_insulin', [
                'query' => $data,
            ]);

            // Decode the response
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['error'])) {
                // Handle any errors returned by the Flask API
                return response()->json(['error' => $responseData['error']], 500);
            }

            $final_dose = $responseData['predicted_insulin_dose']; // Flask API returns predicted insulin dose

            // Return the predicted insulin dose from the API response
            return response()->json(['message' => $final_dose]);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}


