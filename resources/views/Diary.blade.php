@extends('layouts.app')
@section('title', 'Diary')  <!-- This will set the browser tab title -->
@section('content')

    <div class="container px-0 ps-md-5">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-4">

                {{-- Εμφάνιση προειδοποιητικού μηνύματος αν λείπουν οι υποχρεωτικές πληροφορίες "Weight" και "Diagnosis". --}}
                @if(Auth::user()->patient->diagnosis===null || Auth::user()->patient->weight === null)
                    <div class="alert alert-danger">
                      <p> <i class="fi fi-rr-triangle-warning"></i>  The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed! </p>
                    </div>
                @endif
                <div class="card shadow-sm ">
                    <div class="card-header" style="background-color: rgba(244, 244, 244, 0.8); display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="color: black; margin: 0; min-width: 60px;">
                            Diary
                        </h4>

                        <!-- Κουμπί εξαγωγής δεδομένων σε αρχείο XLS -->
                        <div style="display: flex; gap: 10px;">
                            <a href="{{ route('export',['patientId'=>Auth::user()->patient->id]) }}" class="btn btn-success d-flex align-items-center px-3">
                                <i class="fi fi-rr-file-download me-2"></i> Export XLS
                            </a>

                            <!-- Κουμπί για την προσθήκη νέας εγγραφής -->
                            <button type="submit" class="btn btn-primary d-flex align-items-center create px-3">
                                <i class="fi fi-rr-octagon-plus me-2"></i> New Record
                            </button>
                        </div>

                    </div>


                    <div class="card-body table-responsive">
                        <table>
                            <!-- Έλεγχος αν υπάρχουν δεδομένα για τον χρήστη -->
                            @if(count($data)!=0)
                                <tr style="background-color: rgba(176, 190, 227, 0.8);">
                                    <th></th>
                                    <th style="text-align: center;">Glucose<br>Before Meal</th>
                                    <th style="text-align: center;">Food Carbo<br>(Grams)</th>
                                    <th style="text-align: center;">Insulin Dose<br>(Units)</th>
                                    <th style="text-align: center;">Glucose<br>After Meal</th>
                                    <th style="text-align: center;width:5px;"></th>
                                </tr>

                                <!-- Βρόχος για εμφάνιση των δεδομένων σε γραμμές -->
                                @foreach($data as $d)
                                    <tr class="row_tr" data-id="{{$d->id}}" data-timestamp="{{$d->created_at}}" data-glucose_old="{{ $d->glucose_old }}"
                                        data-glucose_new="{{ $d->glucose_new}}" data-food="{{$d->food_carbo}}" data-insulin="{{$d->insulin_dose}}">

                                        <!-- Ημερομηνία και ώρα της εγγραφής -->
                                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.8); color: #1b4965;font-weight: bold;">
                                            <i>{{ date('d/m/Y', strtotime($d->created_at)) }}</i><br>
                                            <i>{{ date('H:i', strtotime($d->created_at)) }}</i>
                                        </td>
                                        <!-- Τιμές γλυκόζης πριν το γεύμα -->
                                        <td style="text-align: center;"><i>{{ $d->glucose_old }}</i></td>
                                        <!-- Τιμή υδατανθράκων του φαγητού -->
                                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);"><i>{{$d->food_carbo}}</i></td>
                                        <!-- Δόση ινσουλίνης -->
                                        <td style="text-align: center;"><i>{{$d->insulin_dose}}</i></td>
                                        <!-- Τιμές γλυκόζης μετά το γεύμα -->
                                        <td style="text-align: center; background-color: rgba(176, 190, 227, 0.3);"><i>{{ $d->glucose_new }}</i></td>
                                        <!-- Κουμπί διαγραφής εγγραφής -->
                                        <td style="text-align: center;" class="td_delete">
                                            <button type="button" class="close delete" data-dismiss="modal" aria-label="Close"
                                                    data-id="{{$d->id}}" data-timestamp="{{$d->created_at}}" data-glucose_old="{{ $d->glucose_old }}"
                                                    data-glucose_new="{{ $d->glucose_new }}" data-food="{{$d->food_carbo}}"
                                                    data-insulin="{{$d->insulin_dose}}">
                                                <i class="fi fi-sr-circle-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif

                            <!-- Μήνυμα αν δεν υπάρχουν εγγραφές -->
                            @if(count($data)==0)
                                <tr class="shadow-lg">
                                    <td>
                                        <p>There are not any records yet.</p>
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>

                </div>
                <!-- Σύνδεσμοι για την πλοήγηση ανάμεσα στις σελίδες των αποτελεσμάτων -->
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    <!-- Ενσωμάτωση της φόρμας προσθήκης εγγραφών μόνο αν υπάρχουν τα πεδία "Weight" και "Diagnosis" -->
    @if(Auth::user()->patient->diagnosis!==null && Auth::user()->patient->weight !== null)
        @include('PopUpForms.Diary')
    @endif


    @section('sidebar')
        @include('layouts.sidebar') <!-- Ενσωμάτωση του sidebar -->
    @endsection

@endsection
