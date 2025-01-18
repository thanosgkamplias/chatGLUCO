<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Κλάση Patient
 *
 * Αντιπροσωπεύει το μοντέλο του ασθενούς στη βάση δεδομένων.
 */
class Patient extends Model
{
    use HasFactory; // Χρήση του trait HasFactory, το οποίο επιτρέπει τη δημιουργία δεδομένων με factories.
    // Το όνομα του πίνακα στη βάση δεδομένων που αντιστοιχεί σε αυτό το μοντέλο.
    protected $table = 'patients';

    /**
     * @var array $fillable
     * Πεδία που επιτρέπεται να γεμιστούν μαζικά (mass assignable).
     */
    protected $fillable = [
        'user_id', // Το ID του συνδεδεμένου χρήστη (ξένο κλειδί).
        'gender',
        'birth_at',
        'weight',
        'diagnosis',
    ];

    /**
     * Σχέση με τον πίνακα users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * Επιστρέφει ότι ο ασθενής ανήκει σε έναν χρήστη.
     *  Η μέθοδος belongsTo() υποδηλώνει ότι το τρέχον μοντέλο
     *  συνδέεται με το μοντέλο User, βασισμένο στο πεδίο user_id ως ξένο κλειδί.
     */
    public function user(){
        // Το 'user_id' είναι το ξένο κλειδί που συνδέει τον ασθενή με τον χρήστη.
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Σχέση με τον πίνακα patient_statistics.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * Επιστρέφει τα στατιστικά δεδομένα του ασθενούς.
     * Η μέθοδος hasMany() δηλώνει ότι ένας ασθενής μπορεί να έχει πολλά στατιστικά.
     */
    public function stats(){
        // Ένας ασθενής μπορεί να έχει πολλά στατιστικά δεδομένα.
        return $this->hasMany(PatientStatistic::class);
    }
}
