@extends('layouts.app')
@section('title', 'Glucose Prediction')

@section('content')
    <div class="container ps-0 ps-md-3">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-4">
                {{-- Εμφάνιση προειδοποιητικού μηνύματος αν λείπουν οι υποχρεωτικές πληροφορίες "Weight" και "Diagnosis". --}}
                @if(Auth::user()->patient->diagnosis === null || Auth::user()->patient->weight === null)
                    <div class="alert alert-danger">
                        <p><i class="fi fi-rr-triangle-warning"></i> The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed!</p>
                    </div>
                @endif

                <!-- Δημιουργία μεταβλητής για να ελέγχεται η απενεργοποίηση του κουμπιού "Predict Glucose" -->
                @php
                    $disablePredictButton = Auth::user()->patient->diagnosis === null || Auth::user()->patient->weight === null;
                @endphp

                <h2>Glucose Prediction</h2>

                <!-- Εμφάνιση μηνύματος σφάλματος αν υπάρχει -->
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <!-- Εμφάνιση της προβλεπόμενης τιμής γλυκόζης, αν υπάρχει -->
                @if(isset($predicted_glucose_new))
                    @php
                        $alertClass = '';
                        $iconClass = '';
                        if($predicted_glucose_new < 80){
                            $alertClass = 'alert-warning';
                            $iconClass = 'fi fi-rr-triangle-warning';
                        } elseif($predicted_glucose_new >= 80 && $predicted_glucose_new <= 180){
                            $alertClass = 'alert-success';
                            $iconClass = 'fi fi-sr-checkbox';
                        } else {
                            $alertClass = 'alert-danger';
                            $iconClass = 'fi fi-rr-triangle-warning';
                        }
                    @endphp
                    <div class="alert {{ $alertClass }}">
                        <i class="{{ $iconClass }}"></i>
                        <strong> Predicted Glucose Level:</strong> {{ $predicted_glucose_new }} mg/dL
                    </div>
                @endif

                <!-- Φόρμα πρόβλεψης γλυκόζης -->
                <form method="POST" action="{{ route('glucose.prediction.result') }}">
                    @csrf

                    <!-- Πεδίο εισαγωγής τρέχουσας τιμής γλυκόζης -->
                    <div class="form-group">
                        <label for="glucose_old">Current Glucose Level (mg/dL):</label>
                        <input type="number" name="glucose_old" id="glucose_old" class="form-control"
                               value="{{ old('glucose_old', $glucose_old ?? '') }}" required>
                        @error('glucose_old')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Πεδίο εισαγωγής υδατανθράκων -->
                    <div class="form-group">
                        <label for="food_carbo">Food Carbohydrates (grams):</label>
                        <input type="number" name="food_carbo" id="food_carbo" step="0.01" class="form-control"
                               value="{{ old('food_carbo', $food_carbo ?? '') }}">
                        @error('food_carbo')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Πεδίο εισαγωγής δόσης ινσουλίνης -->
                    <div class="form-group">
                        <label for="insulin_dose">Insulin Dose (units):</label>
                        <input type="number" name="insulin_dose" id="insulin_dose" step="0.01" class="form-control"
                               value="{{ old('insulin_dose', $insulin_dose ?? '') }}" required>
                        @error('insulin_dose')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Calculation Mode selection -->
                    <div class="form-group">
                        <label for="calculation_mode">Calculation Mode:</label>
                        <select name="calculation_mode" id="calculation_mode" class="form-control" required onchange="toggleAlgorithmSelect()">
                            <option value="">-- Select Mode --</option>
                            <option value="flask_algorithm" {{ old('calculation_mode', $calculation_mode ?? '') == 'flask_algorithm' ? 'selected' : '' }}>Machine Learning-Based Glucose Prediction</option>
                            <option value="calculate_new_glucose" {{ old('calculation_mode', $calculation_mode ?? '') == 'calculate_new_glucose' ? 'selected' : '' }}>Insulin-Carbohydrate Ratio Model</option>
                        </select>
                        @error('calculation_mode')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Algorithm selection (only if "Machine Learning-Based Glucose Prediction" is selected) -->
                    <div class="form-group" id="algorithm_group" style="display: none;">
                        <label for="algorithm">Select Algorithm:</label>
                        <select name="algorithm" id="algorithm" class="form-control">
                            <option value="">-- Select Algorithm --</option>
                            <option value="linear_regression" {{ old('algorithm', $algorithm ?? '') == 'linear_regression' ? 'selected' : '' }}>Linear Regression</option>
                            <option value="random_forest" {{ old('algorithm', $algorithm ?? '') == 'random_forest' ? 'selected' : '' }}>Random Forest</option>
                            <option value="gradient_boosting" {{ old('algorithm', $algorithm ?? '') == 'gradient_boosting' ? 'selected' : '' }}>Gradient Boosting</option>
                        </select>
                        @error('algorithm')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Κουμπιά φόρμας -->
                    <div class="d-flex justify-content-between mt-4">
                        <!-- Κουμπί υποβολής -->
                        <button type="submit" class="btn btn-primary" @if($disablePredictButton) disabled @endif>
                            <i class="fi fi-rs-sparkles"></i> Predict Glucose
                        </button>
                        <!-- Κουμπί εκκαθάρισης πεδίων -->
                        <button type="button" class="btn btn-secondary" onclick="clearFields()">
                            Clear Fields
                        </button>
                    </div>
                </form>

                <!-- Περιγραφή Κατηγοριών Γλυκόζης -->
                <div class="mt-5 p-4 rounded shadow-sm" style="background-color: #f8f9fa; border: 1px solid #ddd;">
                    <p><strong>Glucose Prediction Categories:</strong></p>
                    <ul>
                        <li><span class="text-warning font-weight-bold">Yellow Indicator:</span> Glucose Levels below 80 mg/dL (Low)</li>
                        <li><span class="text-success font-weight-bold">Green Indicator:</span> Glucose Levels between 80 and 180 mg/dL (Normal)</li>
                        <li><span class="text-danger font-weight-bold">Red Indicator:</span> Glucose Levels above 180 mg/dL (High)</li>
                    </ul>
                    <p>These color-coded indicators provide an immediate understanding of glucose levels, allowing appropriate action to be taken as needed.</p>
                </div>
            </div>
        </div>
    </div>

    @section('sidebar')
        @include('layouts.sidebar')
    @endsection

    <script>
        // Συνάρτηση εκκαθάρισης πεδίων
        function clearFields() {
            // Εκκαθάριση της τιμής του πεδίου "glucose_old"
            document.getElementById('glucose_old').value = '';
            // Εκκαθάριση της τιμής του πεδίου "food_carbo"
            document.getElementById('food_carbo').value = '';
            // Εκκαθάριση της τιμής του πεδίου "insulin_dose"
            document.getElementById('insulin_dose').value = '';
            // Κλήση της συνάρτησης toggleAlgorithmSelect για ενημέρωση της ορατότητας του "algorithm_group"
            toggleAlgorithmSelect();
        }

        // Συνάρτηση εναλλαγής ορατότητας του πεδίου επιλογής αλγορίθμου
        function toggleAlgorithmSelect() {
            // Λήψη της τιμής από το πεδίο "calculation_mode"
            var mode = document.getElementById('calculation_mode').value;
            // Λήψη του στοιχείου "algorithm_group" (το group επιλογής αλγορίθμου)
            var algoGroup = document.getElementById('algorithm_group');
            // Αν η τιμή του "calculation_mode" είναι "flask_algorithm", εμφανίζει το πεδίο
            if (mode === 'flask_algorithm') {
                algoGroup.style.display = 'block';
            } else {
                // Διαφορετικά, το κρύβει
                algoGroup.style.display = 'none';
            }
        }

        // Αρχική εκτέλεση της toggleAlgorithmSelect
        // Εξασφαλίζει ότι η σωστή ορατότητα του "algorithm_group" είναι ενεργοποιημένη κατά τη φόρτωση της σελίδας
        toggleAlgorithmSelect();
    </script>
@endsection
