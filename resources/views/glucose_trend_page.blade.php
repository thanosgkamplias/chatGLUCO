<!-- Κληρονομεί τη βασική διάταξη από το layouts.app και ορίζει τον τίτλο της σελίδας ως "Future Glucose Trend". -->
@extends('layouts.app')
@section('title', 'Future Glucose Trend')

@section('sidebar')
    @include('layouts.sidebar')
@endsection

@section('content')

    <div class="container ps-0 ps-md-5" >
        <div class="row justify-content-center">
            <div class="col-md-10 mt-4">
                <!-- Ελέγχει αν υπάρχει μήνυμα σφάλματος και το εμφανίζει σε alert box. -->
                @if(isset($message) && $message)
                    <div class="alert alert-danger">{{ $message }}</div>
                @endif

                <div class="card shadow-sm mt-4">
                    <div class="card-header" style="background-color: rgba(244, 244, 244, 0.8); display: flex; justify-content: space-between">
                        <h4 style="color: black">Future Glucose Trend Prediction</h4>
                    </div>

                    <!-- Κεντρική περιοχή που περιέχει το canvas για το διάγραμμα. -->
                    <div class="text-center" style="padding-right: 30px; min-height: 600px;">
                        <!-- Το canvas όπου θα σχεδιαστεί το διάγραμμα. -->
                        <canvas id="glucoseTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Εισαγωγή της βιβλιοθήκης Chart.js από CDN. -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        //Λήψη και μετατροπή των δεδομένων σε JSON από την πλευρά του server (Controller),
        // ώστε να είναι διαθέσιμα ως JavaScript μεταβλητές.

        // Ετικέτες (x-axis) για τα ιστορικά δεδομένα (χρονικές στιγμές).
        var actualLabels = {!! json_encode($timeData ?? []) !!};
        // Επίπεδα γλυκόζης από ιστορικά δεδομένα.
        var actualGlucoseData = {!! json_encode($glucoseData ?? []) !!};
        // Ετικέτες (x-axis) για προβλεπόμενες χρονικές στιγμές.
        var predictedLabels = {!! json_encode($futureTimeData ?? []) !!};
        // Επίπεδα γλυκόζης από προβλέψεις.
        var predictedGlucoseData = {!! json_encode($predictedFuture ?? []) !!};

        // Λήψη του context από το canvas για τη δημιουργία του διαγράμματος.
        var ctx = document.getElementById('glucoseTrendChart').getContext('2d');

        var glucoseTrendChart = new Chart(ctx, {
            type: 'line', // Ο τύπος του διαγράμματος (γραμμικό διάγραμμα).
            data: {
                // Ενώνουμε (concat) τις ετικέτες των ιστορικών δεδομένων με εκείνες των προβλέψεων
                // για να εμφανιστούν σε ενιαίο άξονα Χ.
                labels: actualLabels.concat(predictedLabels),
                datasets: [
                    {
                        label: 'Historical Glucose (mg/dL)',
                        data: actualGlucoseData, // Τα δεδομένα της γλυκόζης.
                        borderColor: 'blue',
                        borderWidth: 2,
                        fill: false,
                        pointBackgroundColor: 'blue',
                        pointBorderColor: 'blue',
                        pointRadius: 4
                    },
                    {
                        label: 'Predicted Glucose (mg/dL)',
                        /*
                      Δημιουργούμε τόσες null τιμές, όσες τα ιστορικά δεδομένα,
                      ώστε η γραμμή των προβλέψεων να ξεκινά από το τέλος των ιστορικών.
                      Μετά ακολουθούν οι τιμές predictedGlucoseData.
                        */
                        data: Array(actualGlucoseData.length).fill(null).concat(predictedGlucoseData),
                        borderColor: 'orange',
                        borderWidth: 2,
                        fill: false,
                        pointBackgroundColor: 'orange',
                        pointBorderColor: 'orange',
                        pointStyle: 'triangle',
                        // Εμφανίζει γραμμή με διακεκομμένες γραμμές για τις προβλέψεις.
                        borderDash: [5,5],
                        pointRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                // Το διάγραμμα είναι προσαρμόσιμο ανάλογα με το μέγεθος της οθόνης.
                maintainAspectRatio: true,
                scales: {
                    // Εύρος στον άξονα Y (επίπεδα γλυκόζης).
                    y: {
                        min: 0,
                        max: 400,
                        title: {
                            display: true,
                            text: 'Glucose Level (mg/dL)'
                        }
                    },
                    // Τίτλος του οριζόντιου άξονα (x)
                    x: {
                        title: {
                            display: true,
                            text: 'Time'
                        },
                        // Περιορίζει τον αριθμό των ετικετών (ticks) που θα εμφανίζονται,
                        // π.χ. για να μην "φορτώνεται" πολύ ο άξονας με πολλές ημερομηνίες.
                        ticks: {
                            autoSkip: true,
                            maxTicksLimit: 20
                        }
                    }
                },
                // Εμφάνιση του legend στο πάνω μέρος του διαγράμματος.
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    </script>
@endsection
