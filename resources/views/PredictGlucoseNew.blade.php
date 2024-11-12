@extends('layouts.app')
@section('title', 'Glucose Prediction')  <!-- This will set the browser tab title -->

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-4">
                @if(Auth::user()->patient->diagnosis === null || Auth::user()->patient->weight === null)
                    <div class="alert alert-danger">
                        <p><i class="fi fi-rr-triangle-warning"></i> The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed!</p>
                    </div>
                @endif

                @php
                    $disablePredictButton = Auth::user()->patient->diagnosis === null || Auth::user()->patient->weight === null;
                @endphp

                <h2>Glucose Prediction</h2>

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(isset($predicted_glucose_new))
                    @php
                        $alertClass = '';
                        $iconClass = '';
                        if($predicted_glucose_new < 80){
                            $alertClass = 'alert-warning'; // Yellow box for glucose below 80
                            $iconClass = 'fi fi-rr-triangle-warning';
                        } elseif($predicted_glucose_new >= 80 && $predicted_glucose_new <= 180){
                            $alertClass = 'alert-success'; // Green box for glucose between 80-180
                            $iconClass = 'fi fi-sr-checkbox';
                        } else {
                            $alertClass = 'alert-danger'; // Red box for glucose above 180
                            $iconClass = 'fi fi-rr-triangle-warning';
                        }
                    @endphp
                    <div class="alert {{ $alertClass }}">
                        <i class="{{ $iconClass }}"></i>
                        <strong> Predicted Glucose Level:</strong> {{ $predicted_glucose_new }} mg/dL
                    </div>
                @endif

                <form method="POST" action="{{ route('glucose.prediction.result') }}">
                    @csrf

                    <div class="form-group">
                        <label for="glucose_old">Current Glucose Level (mg/dL):</label>
                        <input type="number" name="glucose_old" id="glucose_old" class="form-control"
                               value="{{ old('glucose_old', $glucose_old ?? '') }}" required>
                        @error('glucose_old')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="food_carbo">Food Carbohydrates (grams):</label>
                        <input type="number" name="food_carbo" id="food_carbo" step="0.01" class="form-control"
                               value="{{ old('food_carbo', $food_carbo ?? '') }}" required>
                        @error('food_carbo')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="insulin_dose">Insulin Dose (units):</label>
                        <input type="number" name="insulin_dose" id="insulin_dose" step="0.01" class="form-control"
                               value="{{ old('insulin_dose', $insulin_dose ?? '') }}" required>
                        @error('insulin_dose')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Algorithm selection -->
                    <div class="form-group">
                        <label for="algorithm">Select Algorithm:</label>
                        <select name="algorithm" id="algorithm" class="form-control" required>
                            <option value="">-- Select Algorithm --</option>
                            <option value="linear_regression" {{ old('algorithm', $algorithm ?? '') == 'linear_regression' ? 'selected' : '' }}>Linear Regression</option>
                            <option value="random_forest" {{ old('algorithm', $algorithm ?? '') == 'random_forest' ? 'selected' : '' }}>Random Forest</option>
                            <option value="gradient_boosting" {{ old('algorithm', $algorithm ?? '') == 'gradient_boosting' ? 'selected' : '' }}>Gradient Boosting</option>
                        </select>
                        @error('algorithm')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary" @if($disablePredictButton) disabled @endif>
                            <i class="fi fi-rs-sparkles"></i> Predict Glucose
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFields()">
                            Clear Fields
                        </button>
                    </div>
                </form>

                <!-- Prediction category explanation in a styled box -->
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
        function clearFields() {
            document.getElementById('glucose_old').value = '';
            document.getElementById('food_carbo').value = '';
            document.getElementById('insulin_dose').value = '';
            document.getElementById('algorithm').selectedIndex = 0;
        }
    </script>
@endsection
