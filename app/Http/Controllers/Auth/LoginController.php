<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

/**
 * LoginController
 *
 * Ελέγχει τη διαδικασία σύνδεσης χρηστών και καθορίζει τη λογική δρομολόγησης
 * μετά την επιτυχημένη ή αποτυχημένη σύνδεση.
 */
class LoginController extends Controller
{
    // Χρησιμοποιεί το trait AuthenticatesUsers, το οποίο παρέχει βασική λειτουργικότητα για τη σύνδεση χρηστών.
    use AuthenticatesUsers;

    // Ορίζει τη διαδρομή στην οποία θα ανακατευθύνεται ο χρήστης μετά τη σύνδεση.
    protected $redirectTo = '/home';

    /**
     * Κατασκευαστής της κλάσης.
     * Ορίζει τα middleware για τη σύνδεση και αποσύνδεση.
     */
    public function __construct()
    {
        // Το middleware 'guest' εφαρμόζεται σε όλες τις μεθόδους εκτός από την 'logout'.
        $this->middleware('guest')->except('logout');
        // Το middleware 'auth' εφαρμόζεται μόνο στην 'logout', για να διασφαλίσει ότι μόνο συνδεδεμένοι χρήστες μπορούν να αποσυνδεθούν.
        $this->middleware('auth')->only('logout');
    }

    /**
     * Λειτουργία που καλείται μετά την επιτυχημένη σύνδεση.
     *
     * @param Request $request Το αίτημα σύνδεσης
     * @param $user o συνδεδεμένος χρήστης
     * @return \Illuminate\Http\RedirectResponse Ανακατεύθυνση ανάλογα με τον ρόλο του χρήστη.
     */
    protected function authenticated(Request $request, $user)
    {
        // Αν ο ρόλος του χρήστη είναι 'Patient', ανακατεύθυνση στο ημερολόγιο του ασθενούς.
        if ($user->role == 'Patient') {
            return redirect()->route('diary');
        }
        // Εάν δεν πληρούνται οι παραπάνω συνθήκες, ανακατεύθυνση στην προεπιλεγμένη διαδρομή.
        return redirect($this->redirectTo); // Fallback to the default home
    }

    // Override της μεθόδου sendFailedLoginResponse για την εμφάνιση προσαρμοσμένου μηνύματος σφάλματος.
    protected function sendFailedLoginResponse(Request $request)
    {
        // Ανακατεύθυνση πίσω με το μήνυμα σφάλματος στον καθορισμένο error bag 'login'.
        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember', 'form_type'))
            ->withErrors([
                'login_error' => 'Invalid email or password.', // Μήνυμα σφάλματος
            ], 'login'); // Καθορισμός του error bag ως 'login'
    }
}
