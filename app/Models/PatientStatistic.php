<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientStatistic extends Model
{
    use HasFactory;
    protected $table = 'patients_statistics';


    protected $fillable = [
        'patient_id',
        'glucose_old',
        'insulin_dose',
        'food_carbo',
        'glucose_new',
        'created_at',
        'updated_at',
        'weight',

    ];


    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }


}
