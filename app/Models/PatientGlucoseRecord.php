<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientGlucoseRecord extends Model
{
    use HasFactory; // Χρήση του trait HasFactory για δημιουργία δεδομένων μέσω factories.

    protected $table = 'patient_glucose_records';

    /**
     * Πεδία που επιτρέπεται να γεμιστούν μαζικά (mass assignable).
     */
    protected $fillable = [
        'patient_id', // Ξένο κλειδί που συνδέει την εγγραφή με έναν ασθενή.
        'timestamp',
        'glucose_before',
        'food_carbo',
        'insulin_dosage',
        'glucose_after',
    ];

    /**
     * Σχέση με το μοντέλο User.
     *
     * Δηλώνει ότι κάθε εγγραφή γλυκόζης ανήκει σε έναν συγκεκριμένο χρήστη (ασθενή).
     */
    public function patient()
    {
        // Το 'patient_id' είναι το ξένο κλειδί που συνδέει την εγγραφή με τον ασθενή.
        return $this->belongsTo(User::class, 'patient_id');
    }
}
