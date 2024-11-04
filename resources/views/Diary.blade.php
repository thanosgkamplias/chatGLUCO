@extends('layouts.app')
@section('title', 'Diary')  <!-- This will set the browser tab title -->
@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 mt-4 ">

                @if(Auth::user()->patient->diagnosis===null || Auth::user()->patient->weight === null)
                    <div class="alert alert-danger">
                      <p> <i class="fi fi-rr-triangle-warning"></i>  The following fields <b>"Weight"</b> and <b>"Diagnosis"</b> in your profile are required for making predictions. Please fill them in to proceed! </p>
                    </div>
                @endif
                <div class="card shadow-sm ">
                    <div class="card-header" style="background-color: rgba(244, 244, 244, 0.8); display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="color: black; margin: 0;">
                            Diary
                        </h4>
                        <div style="display: flex; gap: 10px;">
                            <a href="{{ route('export',['patientId'=>Auth::user()->patient->id]) }}" class="btn btn-success d-flex align-items-center px-3">
                                <i class="fi fi-rr-file-download me-2"></i> Export XLS
                            </a>

                            <button type="submit" class="btn btn-primary d-flex align-items-center create px-3">
                                <i class="fi fi-rr-octagon-plus me-2"></i> Entry Log
                            </button>
                        </div>

                    </div>


                    <div class="card-body table-responsive">
                        <table>
                            {{--If the are results for the users' table then show the results--}}
                            @if(count($data)!=0)
                                <tr style="background-color: rgba(176, 190, 227, 0.8);">
                                    <th></th>
                                    <th style="text-align: center;">Glucose Before</th>
                                    <th style="text-align: center;">Food Carbo<br>(Grams)</th>
                                    <th style="text-align: center;">Insulin Dose<br>(Units)</th>
                                    <th style="text-align: center;">Glucose After</th>
                                    <th style="text-align: center;width:5px;"></th>
                                </tr>
                                @foreach($data as $d)

                                    <tr class="row_tr" data-id="{{$d->id}}" data-timestamp="{{$d->created_at}}" data-glucose_old="{{ $d->glucose_old }}"
                                        data-glucose_new="{{ $d->glucose_new}}" data-food="{{$d->food_carbo}}" data-insulin="{{$d->insulin_dose}}">
                                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.8); color: white;font-weight: bold;">
                                            <i>{{ date('d/m/Y', strtotime($d->created_at)) }}</i><br>
                                            <i>{{ date('H:i', strtotime($d->created_at)) }}</i>
                                        </td>
                                        <td style="text-align: center;"><i>{{ $d->glucose_old }}</i></td>
                                        <td style="text-align: center;background-color: rgba(176, 190, 227, 0.3);"><i>{{$d->food_carbo}}</i></td>
                                        <td style="text-align: center;"><i>{{$d->insulin_dose}}</i></td>
                                        <td style="text-align: center; background-color: rgba(176, 190, 227, 0.3);"><i>{{ $d->glucose_new }}</i></td>
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

                            {{--If there is not any results for this user's table then show the following message--}}
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
                {{--            the links for the next page of results--}}
                {{ $data->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    @if(Auth::user()->patient->diagnosis!==null || Auth::user()->patient->weight !== null)
        @include('PopUpForms.Diary')
    @endif


    @section('sidebar')
        @include('layouts.sidebar')
    @endsection

@endsection
