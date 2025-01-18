<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Κλάση για τον έλεγχο ταυτότητας χρηστών.
use Illuminate\Notifications\Notifiable; // Χρήση για αποστολή ειδοποιήσεων.
use Laravel\Sanctum\HasApiTokens; // Ενεργοποίηση token-based authentication για APIs.

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Χρήση traits για λειτουργικότητα API, δημιουργία δεδομένων και ειδοποιήσεις.

    /**
     * @var array<int, string> $fillable
     * Καθορίζει τα πεδία που επιτρέπεται να γεμιστούν μαζικά (mass assignable).
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role',
        'profile_pic'
    ];

    /**
     * @var array<int, string> $hidden
     * Καθορίζει τα πεδία που θα αποκρύπτονται κατά τη μετατροπή του αντικειμένου σε array ή JSON.
     */
    protected $hidden = [
        'password', // Ο κωδικός πρόσβασης δεν πρέπει να εμφανίζεται δημόσια.
        'remember_token', // Το token "θυμήσου με" αποκρύπτεται για λόγους ασφαλείας.
    ];

    /**
     * @var array<string, string> $casts
     * Καθορίζει τα πεδία που πρέπει να μετατραπούν σε συγκεκριμένους τύπους δεδομένων.
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Ημερομηνία επαλήθευσης email μετατρέπεται σε τύπο datetime.
        'password' => 'hashed', // Ο κωδικός πρόσβασης μετατρέπεται σε hashed μορφή.
    ];


    /**
     * Σχέση με το μοντέλο Patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * Δηλώνει ότι ένας χρήστης έχει μία μόνο εγγραφή ασθενούς.
     */
    public function patient(){
        // Ένας χρήστης συσχετίζεται με μία εγγραφή στον πίνακα patients μέσω του user_id.
        return $this->hasOne(Patient::class, 'user_id');
    }
}
