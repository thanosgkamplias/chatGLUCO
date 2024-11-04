@extends('layouts.app')
@section('title', 'Diagrams')

@section('sidebar')
    @include('layouts.sidebar')
@endsection

@section('content')
<div class="container" >
   <div class="row justify-content-center">
        <div class="col-md-9">

                @if(Auth::user()->patient->diagnosis===null || Auth::user()->patient->weight === null)
                <div class="alert alert-danger">
                    <p> <i class="fi fi-rr-triangle-warning"></i>  The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed! </p>
                </div>
                @endif

            <div class="card shadow-sm mt-4">
                <div class="card-header" style="background-color: rgba(244, 244, 244, 0.8); display: flex; justify-content: space-between">
                    <h4 style="color: black">Diagrams</h4>
                </div>

                <form method="POST" action="{{ route('load.diagram') }}" class="form">
                    @csrf
                    <div class="row mb-3 mt-4">
                        <div class="col-md-4 pb-4">
                            <label>Select Diagram:</label>
                            <select class="form-select" name="diagram_type">
                                <option value="time_in_range" {{ request('diagram_type') == 'time_in_range' ? 'selected' : '' }}>Time in Range</option>
                                <option value="glucose_insulin_correlation" {{ request('diagram_type') == 'glucose_insulin_correlation' ? 'selected' : '' }}>Glucose-Insulin Correlation</option>
                            </select>
                        </div>
                        <div class="col-md-3 pb-4">
                            <label>From:</label>
                            <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}" required>
                        </div>
                        <div class="col-md-3 pb-4">
                            <label>To:</label>
                            <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end pb-4">
                            <button type="submit" class="btn btn-primary">Load Diagram</button>
                        </div>
                    </div>
                </form>

                @if(isset($message))
                    <div class="alert alert-danger">{{ $message }}</div>
               @endif

                <!-- Display slope and intercept for Glucose-Insulin Correlation -->
                @if(isset($regressionData))
                    <div style="text-align:center; font-size:16px;">
                        Slope (A): {{ number_format($regressionData['slope'], 3) }} | Intercept (B): {{ number_format($regressionData['intercept'], 3) }}
                    </div>
                @endif

                <!-- Time in Range Diagram -->
                @if(request('diagram_type') == 'time_in_range')
                <!-- Custom Percentage Legend Box -->
                    <div class="legend-box text-center mb-3">
                        <span style="border: 2px solid yellow; padding: 5px 10px; color: black;">Under 80 mg/dL ({{ round($under80, 0) }}%)</span>
                        <span style="border: 2px solid green; padding: 5px 10px; color: black;">80-180 mg/dL ({{ round($between80and180, 0) }}%)</span>
                        <span style="border: 2px solid red; padding: 5px 10px; color: black;">Above 180 mg/dL ({{ round($above180, 0) }}%)</span>
                    </div>

                <!-- Add a minimum height for the chart container -->
                    <div class="text-center" style="padding-right: 30px; min-height: 600px;">
                        <canvas id="glucoseChart"></canvas>
                    </div>

                @elseif(request('diagram_type') == 'glucose_insulin_correlation')
                    <!-- Glucose-Insulin Correlation Chart -->
                    <!-- Add a minimum height for the chart container -->
                    <div class="text-center" style="padding-right: 30px; max-width: 1300px; min-height: 600px;">
                        <canvas id="glucoseInsulinChart"></canvas>
                    </div>
               @endif
            </div>
        </div>
   </div>
</div>

    <!-- Include Chart.js and Chart.js Plugin for Annotations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>

    <script>
        @if(request('diagram_type') == 'time_in_range')
        var ctx = document.getElementById('glucoseChart').getContext('2d');

        // Create a custom plugin to display glucose values over the red limit (above 180 mg/dL)
        Chart.register({
            id: 'highValueAnnotations',
            afterDatasetsDraw: function(chart) {
                var ctx = chart.ctx;
                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    if (!meta.hidden) {
                        meta.data.forEach(function(element, index) {
                            // Check if the glucose value is above 180 mg/dL
                            if (dataset.data[index] > 180) {
                                // Draw the text
                                ctx.fillStyle = 'red';
                                var fontSize = 17;
                                var fontStyle = 'normal';
                                var fontFamily = 'sans-serif';
                                ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                                var dataString = dataset.data[index].toString();
                                // Position the text slightly above the point
                                ctx.textAlign = 'center';
                                ctx.textBaseline = 'middle';
                                var padding = 12;
                                var position = element.tooltipPosition();
                                ctx.fillText(dataString, position.x, position.y - padding);
                            }
                        });
                    }
                });
            }
        });

        // Create the glucose chart with responsive settings
        var glucoseChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($timeData ?? []) !!},  // Time data from PHP
                datasets: [{
                    label: 'Glucose Level (mg/dL)',
                    data: {!! json_encode($glucoseData ?? []) !!},  // Glucose levels from PHP
                    borderColor: 'black',
                    borderWidth: 2,
                    fill: false,
                    pointBackgroundColor: 'black',
                    pointBorderColor: 'black',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
                                    return 'rgba(255, 255, 0, 0.2)';  // Yellow for below 80
                                } else if (context.tick.value <= 180) {
                                    return 'rgba(0, 255, 0, 0.2)';  // Green for 80-180
                                } else {
                                    return 'rgba(255, 0, 0, 0.2)';  // Red for above 180
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
                            box1: {  // Yellow region for under 80 mg/dL
                                type: 'box',
                                yMin: 0,
                                yMax: 80,
                                backgroundColor: 'rgba(255, 255, 0, 0.2)',
                                borderWidth: 0
                            },
                            box2: {  // Green region for 80-180 mg/dL
                                type: 'box',
                                yMin: 80,
                                yMax: 180,
                                backgroundColor: 'rgba(0, 255, 0, 0.2)',
                                borderWidth: 0
                            },
                            box3: {  // Red region for above 180 mg/dL
                                type: 'box',
                                yMin: 180,
                                yMax: 400,
                                backgroundColor: 'rgba(255, 0, 0, 0.2)',
                                borderWidth: 0
                            },
                            line1: {  // Dashed line at 80 mg/dL
                                type: 'line',
                                yMin: 80,
                                yMax: 80,
                                borderColor: 'purple',
                                borderWidth: 2,
                                borderDash: [5, 5]
                            },
                            line2: {  // Dashed line at 180 mg/dL
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

        @elseif(request('diagram_type') == 'glucose_insulin_correlation')

        var ctx = document.getElementById('glucoseInsulinChart').getContext('2d');

        // Data for the scatter plot
        var scatterData = {!! json_encode(array_map(function($glucose, $insulin) {
                return ['x' => $glucose, 'y' => $insulin];
            }, $glucoseData, $insulinData)) !!};

        // Data for the linear regression line
        var regressionLine = {!! json_encode(array_map(function($glucose, $predicted) {
                return ['x' => $glucose, 'y' => $predicted];
            }, $glucoseData, $regressionData['predictedY'])) !!};

        // Intercept point (0, intercept)
        var interceptPoint = {
            x: 0,
            y: {{ $regressionData['intercept'] }}
        };

        // Slope point (mean of glucose, corresponding insulin value on line)
        var meanGlucose = {!! json_encode(count($glucoseData) > 0 ? array_sum($glucoseData) / count($glucoseData) : 1) !!};
        var slopePoint = {
            x: meanGlucose,
            y: {{ $regressionData['slope'] }} * meanGlucose + {{ $regressionData['intercept'] }}
        };

        // Glucose-Insulin Correlation Chart
        var correlationChart = new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: [
                    {
                        label: 'Data Points',
                        data: scatterData,
                        borderColor: 'blue',
                        backgroundColor: 'blue',
                        pointRadius: 5
                    },
                    {
                        label: 'Linear Fit',
                        data: regressionLine,
                        borderColor: 'red',
                        showLine: true,
                        fill: false,
                        borderWidth: 2,
                        pointRadius: 0,  // No points for the regression line
                        borderDash: [5, 5]  // Dashed line
                    },
                    {
                        label: 'Intercept',
                        data: [interceptPoint],
                        borderColor: 'green',
                        backgroundColor: 'green',
                        pointRadius: 8,
                        pointStyle: 'circle'
                    },
                    {
                        label: 'Slope Point',
                        data: [slopePoint],
                        borderColor: 'orange',
                        backgroundColor: 'orange',
                        pointRadius: 8,
                        pointStyle: 'circle'
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Glucose Level (mg/dL)'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Insulin Dose (Units)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '(' + tooltipItem.raw.x + ', ' + tooltipItem.raw.y + ')';
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>
@endsection
