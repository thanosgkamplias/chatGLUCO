<?php

use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
});

Auth::routes();

use App\Http\Controllers\DiagramController;

Route::get('/home', [App\Http\Controllers\DiaryController::class, 'showDiary'])->name('home');

Route::get('/profile',[App\Http\Controllers\ProfileController::class,'ShowProfile'])->name('my.profile');
Route::post('/profile/update/picture', [App\Http\Controllers\ProfileController::class, 'UpdatePicture'])->name('my.profile.picture');
Route::post('/profile/update/data', [App\Http\Controllers\ProfileController::class, 'UpdateProfile'])->name('my.profile.data');
Route::post('/profile/update/password', [App\Http\Controllers\ProfileController::class, 'UpdatePassword'])->name('my.profile.password');


Route::get('/insulin_prediction', [App\Http\Controllers\PredictionController::class, 'showInsulinForm'])->name('insulin.prediction.form');
Route::post('/insulin_prediction', [App\Http\Controllers\PredictionController::class, 'predictInsulin'])->name('insulin.prediction.result');

// Glucose Prediction Routes
Route::get('/glucose_prediction', [App\Http\Controllers\PredictionController::class, 'showGlucoseForm'])->name('glucose.prediction.form');
Route::post('/glucose_prediction', [App\Http\Controllers\PredictionController::class, 'predictGlucose'])->name('glucose.prediction.result');

Route::get('/diary',[App\Http\Controllers\DiaryController::class, 'ShowDiary'])->name('diary');
Route::post('/food-list', [App\Http\Controllers\DiaryController::class, 'getFoodList']);
Route::get('/diary/add' , [App\Http\Controllers\DiaryController::class, 'AddNewRow'])->name('diary.add');
Route::get('/diary/delete' , [App\Http\Controllers\DiaryController::class, 'DeleteRow'])->name('diary.delete');
Route::get('/diary/update' , [App\Http\Controllers\DiaryController::class, 'UpdateRow'])->name('diary.update');


Route::get('/glucose-input', [App\Http\Controllers\PatientController::class, 'showGlucoseInputForm'])->name('glucose.input.form');
Route::post('/glucose-input/save', [App\Http\Controllers\PatientController::class, 'saveGlucoseRecord'])->name('save.glucose.record');
Route::get('/glucose-input/delete/{id}', [App\Http\Controllers\PatientController::class, 'deleteGlucoseRecord'])->name('delete.glucose.record');



//Route::get('/diagrams', [DiagramController::class, 'showDiagramForm'])->name('diagrams.form');
//Route::post('/diagrams/load', [DiagramController::class, 'loadDiagram'])->name('load.diagram');

Route::get('/diagrams', [App\Http\Controllers\DiagramController::class, 'showDiagramForm'])->name('show.diagram');
Route::post('/load-diagram', [App\Http\Controllers\DiagramController::class, 'loadDiagram'])->name('load.diagram');


//Route::post('/predict_insulin', [App\Http\Controllers\PredictionController::class, 'predictInsulin'])->name('predict.insulin');

Route::get('/predict',[App\Http\Controllers\PredictionController::class, 'PredictDose']);
Route::get('/autocomplete',[App\Http\Controllers\DiaryController::class,'getAutocomplete']);

Route::get('/diary/export/{patientId}', [App\Http\Controllers\DiaryController::class, 'ExportData'])->name('export');

// Glucose Prediction Routes
Route::get('/glucose_prediction', [App\Http\Controllers\PredictionController::class, 'showGlucoseForm'])->name('glucose.prediction.form');
Route::post('/glucose_prediction', [App\Http\Controllers\PredictionController::class, 'predictGlucose'])->name('glucose.prediction.result');








