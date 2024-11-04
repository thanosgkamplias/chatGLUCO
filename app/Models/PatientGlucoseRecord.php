<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientGlucoseRecord extends Model
{
    use HasFactory;

    protected $table = 'patient_glucose_records';

    protected $fillable = [
        'patient_id',
        'timestamp',
        'glucose_before',
        'food_carbo',
        'insulin_dosage',
        'glucose_after',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
