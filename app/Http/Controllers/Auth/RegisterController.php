<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient; // Εισαγωγή του μοντέλου Patient για να αποθηκεύσουμε δεδομένα ασθενούς
use App\Models\User; // Εισαγωγή του μοντέλου User για να αποθηκεύσουμε τα δεδομένα χρήστη
use Illuminate\Foundation\Auth\RegistersUsers; // Εισαγωγή του trait RegistersUsers για λειτουργίες εγγραφής
use Illuminate\Support\Facades\Hash; // Εισαγωγή για την κρυπτογράφηση των κωδικών
use Illuminate\Support\Facades\Validator; // Εισαγωγή για την επικύρωση δεδομένων
use Illuminate\Http\Request; // Εισαγωγή για τη διαχείριση αιτημάτων HTTP
use Illuminate\Auth\Events\Registered; // Εισαγωγή του event για την εγγραφή χρηστών

/**
 * RegisterController
 *
 * Υπεύθυνος για τη λειτουργία εγγραφής νέων χρηστών.
 */
class RegisterController extends Controller
{
    // Χρήση του trait RegistersUsers που παρέχει έτοιμες λειτουργίες για εγγραφή χρηστών.
    use RegistersUsers;

    // Η διαδρομή στην οποία ανακατευθύνονται οι χρήστες μετά την εγγραφή.
    protected $redirectTo = '/home';

    /**
     * Κατασκευαστής της κλάσης.
     * Ορίζει το middleware 'guest' για να διασφαλίσει ότι μόνο μη συνδεδεμένοι χρήστες
     * μπορούν να έχουν πρόσβαση στη σελίδα εγγραφής.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * validator
     * Επικυρώνει τα δεδομένα που εισάγει ο χρήστης κατά την εγγραφή.
     *
     * @param array $data Τα δεδομένα από τη φόρμα εγγραφής.
     */
    protected function validator(array $data)
    {
        // Προσαρμοσμένα μηνύματα σφαλμάτων για το πεδίο του κωδικού πρόσβασης
        $messages = [
            'password.required' => 'Please enter a password.',
            'password.min' => 'Your password must be at least :min characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Your password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];

        // Επιστροφή validator με κανόνες επικύρωσης
        return Validator::make($data, [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Το email πρέπει να είναι μοναδικό.
            'password' => [
                'required', 'string', 'min:8', 'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ], // Ο κωδικός πρέπει να ακολουθεί συγκεκριμένα κριτήρια.
            'gender' => ['required', 'string'],
            'birthdate' => ['required'],
        ], $messages);
    }

    /**
     * register
     * Διαχειρίζεται τη διαδικασία εγγραφής χρήστη.
     *
     * @param Request $request Το αίτημα εγγραφής που υποβάλλεται από τον χρήστη.
     * @return \Illuminate\Http\RedirectResponse Ανακατεύθυνση μετά την εγγραφή.
     */
    public function register(Request $request)
    {
        // Επικύρωση δεδομένων από τον validator
        $validator = $this->validator($request->all());

        // Αν η επικύρωση αποτύχει, επιστροφή στην προηγούμενη σελίδα με τα σφάλματα.
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->except('password', 'password_confirmation')) // Διατήρηση των εισαγωγών εκτός του κωδικού.
                ->withErrors($validator, 'register'); // Καθορισμός του error bag ως 'register'.
        }

        // Δημιουργία event για την εγγραφή νέου χρήστη
        event(new Registered($user = $this->create($request->all())));

        // Αυτόματη σύνδεση του χρήστη μετά την εγγραφή
        $this->guard()->login($user);

        // Ανακατεύθυνση μετά την επιτυχημένη εγγραφή.
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * create
     * Δημιουργεί έναν νέο χρήστη και τον καταχωρεί στη βάση δεδομένων.
     *
     * @param array $data Τα δεδομένα από τη φόρμα εγγραφής.
     * @return User Το αντικείμενο του χρήστη που δημιουργήθηκε.
     */
    protected function create(array $data)
    {
        // Δημιουργία χρήστη στη βάση δεδομένων.
        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'role' => 'Patient',
            'profile_pic' => 'profile_pics/default_pic.png',
            'password' => Hash::make($data['password']), // Κρυπτογράφηση του κωδικού πρόσβασης.
        ]);

        // Δημιουργία αντίστοιχης εγγραφής στον πίνακα 'Patient'.
        $patient = Patient::create([
            'user_id' => $user->id,
            'birth_at' => $data['birthdate'],
            'gender' => $data['gender'],
        ]);
        return $user;
    }
}
