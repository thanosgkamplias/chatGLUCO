@extends('layouts.app')
@section('title', 'Diagrams')

@section('sidebar')
    @include('layouts.sidebar')
@endsection

@section('content')
<div class="container" >
   <div class="row justify-content-center">
        <div class="col-md-9 mt-4">

            {{-- Εμφάνιση προειδοποιητικού μηνύματος αν λείπουν οι υποχρεωτικές πληροφορίες "Weight" και "Diagnosis". --}}
                @if(Auth::user()->patient->diagnosis === null || Auth::user()->patient->weight === null)
                <div class="alert alert-danger">
                    <p> <i class="fi fi-rr-triangle-warning"></i>  The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed! </p>
                </div>
                @endif

            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: rgba(244, 244, 244, 0.8); display: flex; justify-content: space-between">
                    <h4 style="color: black">Diagrams</h4>
                </div>

                {{-- Φόρμα για την επιλογή διαγράμματος και χρονικού διαστήματος. --}}
                <form method="POST" action="{{ route('load.diagram') }}" class="form">
                    @csrf {{-- Προσθήκη CSRF token για ασφάλεια της φόρμας. --}}
                    <div class="row mb-3 mt-4">
                        {{-- Επιλογή τύπου διαγράμματος. --}}
                        <div class="col-md-4 pb-4">
                            <label>Select Diagram:</label>
                            <select class="form-select" name="diagram_type">
                                <option value="time_in_range" {{ request('diagram_type') == 'time_in_range' ? 'selected' : '' }}>Time in Range</option>
                                <option value="glucose_insulin_correlation" {{ request('diagram_type') == 'glucose_insulin_correlation' ? 'selected' : '' }}>Glucose-Insulin Correlation</option>
                            </select>
                        </div>
                        {{-- Επιλογή αρχικής ημερομηνίας. --}}
                        <div class="col-md-3 pb-4">
                            <label>From:</label>
                            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}" required>
                        </div>
                        {{-- Επιλογή τελικής ημερομηνίας. --}}
                        <div class="col-md-3 pb-4">
                            <label>To:</label>
                            <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" required>
                        </div>
                        {{-- Κουμπί για τη φόρτωση του διαγράμματος. --}}
                        <div class="col-md-2 d-flex align-items-end pb-4">
                            <button type="submit" class="btn btn-primary">Load Diagram</button>
                        </div>
                    </div>
                </form>

                {{-- Εμφάνιση μηνύματος σφάλματος αν δεν υπάρχουν δεδομένα. --}}
                @if(isset($message))
                    <div class="alert alert-danger">{{ $message }}</div>
               @endif

                {{-- Εμφάνιση αποτελεσμάτων γραμμικής παλινδρόμησης (slope & intercept) αν υπάρχουν. --}}
                @if(isset($regressionData))
                    <div style="text-align:center; font-size:16px;">
                        Slope (A): {{ number_format($regressionData['slope'], 3) }} | Intercept (B): {{ number_format($regressionData['intercept'], 3) }}
                    </div>
                @endif

                <!-- Time in Range Diagram -->
                @if(request('diagram_type') == 'time_in_range')
                    {{-- Εμφάνιση λεζάντας με ποσοστά για κάθε ζώνη γλυκόζης. --}}
                    <div class="legend-box text-center mb-3">
                        <span style="border: 2px solid yellow; padding: 5px 10px; color: black;">Under 80 mg/dL ({{ round($under80, 0) }}%)</span>
                        <span style="border: 2px solid green; padding: 5px 10px; color: black;">80-180 mg/dL ({{ round($between80and180, 0) }}%)</span>
                        <span style="border: 2px solid red; padding: 5px 10px; color: black;">Above 180 mg/dL ({{ round($above180, 0) }}%)</span>
                    </div>

                <!-- minimum height για το chart container -->
                    <div class="text-center" style="padding-right: 30px; min-height: 600px;">
                        <canvas id="glucoseChart"></canvas>
                    </div>

                    <!-- Glucose-Insulin Correlation Chart -->
                @elseif(request('diagram_type') == 'glucose_insulin_correlation')
                    <!-- minimum height για το chart container -->
                    <div class="text-center" style="padding-right: 30px; max-width: 1300px; min-height: 600px;">
                        <canvas id="glucoseInsulinChart"></canvas>
                    </div>
               @endif
            </div>
        </div>
   </div>
</div>

    {{-- Εισαγωγή των βιβλιοθηκών Chart.js και Chart.js Plugin για annotations. --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>

    <script>
        // Σενάριο JavaScript για το διάγραμμα Time in Range.
        @if(request('diagram_type') == 'time_in_range')
        // Εντοπισμός του καμβά (canvas) με ID 'glucoseChart' και απόκτηση του 2D context για σχεδίαση.
        // Χρησιμοποιείται το 2D context για να δημιουργηθεί ένα διάγραμμα μέσω της βιβλιοθήκης Chart.js
        var ctx = document.getElementById('glucoseChart').getContext('2d');

        // Δημιουργία custom plugin για εμφάνιση τιμών γλυκόζης άνω των 180 mg/dL.
        Chart.register({
            id: 'highValueAnnotations', // Ορισμός μοναδικού ID για το plugin.
            afterDatasetsDraw: function(chart) {
                // Απόκτηση του context για σχεδίαση κειμένου.
                var ctx = chart.ctx;


                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);

                    // Έλεγχος αν το dataset δεν είναι κρυφό.
                    if (!meta.hidden) {
                        meta.data.forEach(function(element, index) {
                            // Έλεγχος αν η τιμή γλυκόζης είναι πάνω από 180 mg/dL.
                            if (dataset.data[index] > 180) {
                                ctx.fillStyle = 'red'; // Ορισμός κόκκινου χρώματος για το κείμενο.

                                // Ρυθμίσεις γραμματοσειράς.
                                var fontSize = 17;
                                var fontStyle = 'normal';
                                var fontFamily = 'sans-serif';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                                // Μετατροπή της τιμής σε κείμενο.
                                var dataString = dataset.data[index].toString();
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';

                                // Θέση σχεδίασης του κειμένου πάνω από το σημείο δεδομένων.
                                var padding = 12;
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - padding);
                            }
                        });
                    }
                });
            }
        });

        // Δημιουργία γραφήματος γλυκόζης.
        var glucoseChart = new Chart(ctx, {
            type: 'line', // Καθορισμός τύπου γραφήματος ως γραμμικό (line chart).
            data: {
                labels: {!! json_encode($timeData ?? []) !!}, // Ετικέτες άξονα Χ (χρόνος).
                datasets: [{
                    label: 'Glucose Level (mg/dL)', // Ετικέτα δεδομένων.
                    data: {!! json_encode($glucoseData ?? []) !!},  // Τιμές γλυκόζης.
                    borderColor: 'black', // Χρώμα γραμμής.
                    borderWidth: 2, // Πάχος γραμμής.
                    fill: false, // Χωρίς γέμισμα κάτω από τη γραμμή.
                    pointBackgroundColor: 'black', // Χρώμα σημείων δεδομένων.
                    pointBorderColor: 'black', // Χρώμα περιγράμματος σημείων.
                    pointRadius: 4 // Μέγεθος σημείων δεδομένων.
                }]
            },
            options: {
                responsive: true, // Ενεργοποίηση προσαρμογής σε διάφορα μεγέθη οθόνης.
                maintainAspectRatio: true,  // Διατήρηση αναλογίας διαστάσεων.
                scales: {
                    y: {
                        min: 0,
                        max: 400,
                        title: {
                            display: true,
                            text: 'Glucose Level (mg/dL)'
                        },
                        grid: {
                            color: function(context) {
                                if (context.tick.value < 80) {
                                    return 'rgba(255, 255, 0, 0.2)';  // Κίτρινο για κάτω από 80
                                } else if (context.tick.value <= 180) {
                                    return 'rgba(0, 255, 0, 0.2)';  // Πράσινο για 80-180
                                } else {
                                    return 'rgba(255, 0, 0, 0.2)';  // Κόκκινο για πάνω από 180
                                }
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Time'
                        }
                    }
                },
                plugins: {
                    annotation: {
                        annotations: {
                            box1: {  // Κίτρινη περιοχή για κάτω από 80 mg/dL
                                type: 'box',
                                yMin: 0,
                                yMax: 80,
                                backgroundColor: 'rgba(255, 255, 0, 0.2)',
                                borderWidth: 0
                            },
                            box2: {  // Πράσινη περιοχή για 80-180 mg/dL
                                type: 'box',
                                yMin: 80,
                                yMax: 180,
                                backgroundColor: 'rgba(0, 255, 0, 0.2)',
                                borderWidth: 0
                            },
                            box3: {  // Κόκκινη Περιοχή για πάνω από 180 mg/dL
                                type: 'box',
                                yMin: 180,
                                yMax: 400,
                                backgroundColor: 'rgba(255, 0, 0, 0.2)',
                                borderWidth: 0
                            },
                            line1: {  // Διακεκομμένη γραμμή στα 80 mg/dL
                                type: 'line',
                                yMin: 80,
                                yMax: 80,
                                borderColor: 'purple',
                                borderWidth: 2,
                                borderDash: [5, 5]
                            },
                            line2: {  // Διακεκομμένη γραμμή στα 180 mg/dL
                                type: 'line',
                                yMin: 180,
                                yMax: 180,
                                borderColor: 'red',
                                borderWidth: 2,
                                borderDash: [5, 5]
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            },
            plugins: ['highValueAnnotations']
        });

        {{-- Σενάριο JavaScript για το διάγραμμα Glucose-Insulin Correlation. --}}
        @elseif(request('diagram_type') == 'glucose_insulin_correlation')

        var ctx = document.getElementById('glucoseInsulinChart').getContext('2d');

        // Δεδομένα για το scatter plot (διασπορά σημείων)
        // Δημιουργεί έναν πίνακα αντικειμένων όπου κάθε σημείο περιέχει τις τιμές γλυκόζης (x) και ινσουλίνης (y)
        var scatterData = {!! json_encode(array_map(function($glucose, $insulin) {
                return ['x' => $glucose, 'y' => $insulin];
            }, $glucoseData, $insulinData)) !!};

        // Δεδομένα για τη γραμμή της γραμμικής παλινδρόμησης
        // Δημιουργεί έναν πίνακα αντικειμένων που περιέχουν τις τιμές γλυκόζης (x) και τις αντίστοιχες προβλεπόμενες τιμές ινσουλίνης (y)
        var regressionLine = {!! json_encode(array_map(function($glucose, $predicted) {
                return ['x' => $glucose, 'y' => $predicted];
            }, $glucoseData, $regressionData['predictedY'])) !!};

        // Σημείο εκκίνησης της γραμμής παλινδρόμησης (intercept), (τεταγμένη όταν x = 0)
        var interceptPoint = {
            x: 0, // Το x είναι πάντα 0 για το intercept
            y: {{ $regressionData['intercept'] }} // Το y προέρχεται από τα δεδομένα της παλινδρόμησης
        };

        // Υπολογισμός του μέσου όρου της γλυκόζης (meanGlucose)
        var meanGlucose = {!! json_encode(count($glucoseData) > 0 ? array_sum($glucoseData) / count($glucoseData) : 1) !!};

        // Σημείο κλίσης (slope point)
        var slopePoint = {
            x: meanGlucose, // Η τιμή x είναι ο μέσος όρος της γλυκόζης
            y: {{ $regressionData['slope'] }} * meanGlucose + {{ $regressionData['intercept'] }} // Υπολογίζει το y χρησιμοποιώντας τη φόρμουλα της γραμμής παλινδρόμησης
        };

        // Glucose-Insulin Correlation Chart
        var correlationChart = new Chart(ctx, {
            type: 'scatter', // Τύπος διαγράμματος: scatter (διασπορά σημείων)
            data: {
                datasets: [
                    {
                        label: 'Data Points',
                        data: scatterData, // Τα δεδομένα για το scatter plot
                        borderColor: 'blue',
                        backgroundColor: 'blue',
                        pointRadius: 5 // Μέγεθος σημείου
                    },
                    {
                        label: 'Linear Fit',
                        data: regressionLine, // Τα δεδομένα για τη γραμμή παλινδρόμησης
                        borderColor: 'red',
                        showLine: true,
                        fill: false,
                        borderWidth: 2,
                        pointRadius: 0,  // Μη εμφάνιση σημείων στη γραμμή
                        borderDash: [5, 5]  // Διακεκομμένη γραμμή
                    },
                    {
                        label: 'Intercept',
                        data: [interceptPoint], // Τα δεδομένα περιέχουν μόνο το intercept point
                        borderColor: 'green',
                        backgroundColor: 'green',
                        pointRadius: 8, // Μέγεθος σημείου
                        pointStyle: 'circle' // Στυλ σημείου: κύκλος
                    },
                    {
                        label: 'Slope Point',
                        data: [slopePoint], // Τα δεδομένα περιέχουν μόνο το slope point
                        borderColor: 'orange',
                        backgroundColor: 'orange',
                        pointRadius: 8, // Μέγεθος σημείου
                        pointStyle: 'circle'
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true, // Εμφάνιση τίτλου για τον άξονα x
                            text: 'Glucose Level (mg/dL)' // Τίτλος άξονα x
                        }
                    },
                    y: {
                        title: {
                            display: true, // Εμφάνιση τίτλου για τον άξονα y
                            text: 'Insulin Dose (Units)' // Τίτλος άξονα y
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true, // Ενεργοποίηση εμφάνισης υπομνήματος
                        position: 'top' // Θέση υπομνήματος στην κορυφή
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '(' + tooltipItem.raw.x + ', ' + tooltipItem.raw.y + ')'; // Προβολή συντεταγμένων σημείου
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endsection
