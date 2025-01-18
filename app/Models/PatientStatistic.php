<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PatientStatistic extends Model
{
    use HasFactory; // Χρήση του trait HasFactory για δημιουργία δεδομένων μέσω factories.

    /**
     * @var string $table
     * Καθορίζει το όνομα του πίνακα στη βάση δεδομένων που σχετίζεται με αυτό το μοντέλο.
     */
    protected $table = 'patients_statistics';

    /**
     * @var array $fillable
     * Πεδία που επιτρέπεται να γεμιστούν μαζικά (mass assignable).
     */
    protected $fillable = [
        'patient_id', // Ξένο κλειδί που συνδέει το στατιστικό με έναν ασθενή.
        'glucose_old',
        'insulin_dose',
        'food_carbo',
        'glucose_new',
        'created_at',
        'updated_at',
        'weight',

    ];


    // Σχέση με το μοντέλο Patient.
    public function patient()
    {
        // Το 'patient_id' είναι το ξένο κλειδί που συνδέει το στατιστικό με τον ασθενή.
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
