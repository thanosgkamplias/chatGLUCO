<?php

namespace App\Http\Controllers;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Εμφάνιση της σελίδας προφίλ.
     */

    // Επιστρέφει το view `profile` που εμφανίζει τα στοιχεία προφίλ του χρήστη.
    public function ShowProfile() {
        return view('profile');
    }

    /**
     * Ενημέρωση εικόνας προφίλ.
     */
    public function UpdatePicture(Request $request) {
        $id = Auth::user()->id;

        // Επικύρωση της εικόνας προφίλ που ανεβαίνει.
        $request->validate([
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:10240', // Επιτρέπονται μόνο συγκεκριμένοι τύποι αρχείων, μέγιστο μέγεθος 10MB.
        ]);

        // Έλεγχος αν έχει ανέβει νέα εικόνα.
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName(); // Δημιουργία μοναδικού ονόματος αρχείου.

            // Μετακίνηση της εικόνας στον φάκελο `profile_pics`.
            $image->move(public_path('profile_pics'), $filename);

            // Ενημέρωση της εικόνας προφίλ στη βάση δεδομένων.
            User::where('id', $id)->update([
                'profile_pic' => 'profile_pics/' . $filename
            ]);
        }



        // Ανακατεύθυνση πίσω με μήνυμα επιτυχίας.
        return redirect()->back()->with('message', 'Your profile has been successfully updated!');
    }

    /**
     * Ενημέρωση πληροφοριών προφίλ.
     */
    public function UpdateProfile(Request $request) {
        // Επικύρωση των δεδομένων που εισάγονται από τον χρήστη.
        $request->validate([
            'diagnosis' => 'required', // Η διάγνωση είναι υποχρεωτική.
            'weight' => 'required|numeric|min:1', // Το βάρος είναι υποχρεωτικό και πρέπει να είναι θετικός αριθμός.
        ]);

        // Ενημέρωση των πεδίων προφίλ στη βάση δεδομένων.
        Patient::where('id', Auth::User()->patient->id)->update([
            'diagnosis' => $request->input('diagnosis'),
            'weight' => $request->input('weight'),
        ]);

        // Ανακατεύθυνση πίσω με μήνυμα επιτυχίας.
        return redirect()->back()->with('message', 'Your profile has been successfully updated!');
    }

    /**
     * Ενημέρωση κωδικού πρόσβασης.
     */
    public function UpdatePassword(Request $request)
    {
        $id = Auth::User()->id; // Λήψη του ID του συνδεδεμένου χρήστη.
        $user = User::where('id',$id)->first(); // Ανάκτηση του χρήστη από τη βάση.
        $password = $request->input('password'); // Λήψη του νέου κωδικού.

        // Ορισμός μηνυμάτων σφάλματος για την επικύρωση.
        $messages = [
            'current_password.required' => 'Please enter your current password.',
            'password.required' => 'Please enter a new password.',
            'password.min' => 'Your password must be at least :min characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Your password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ];

        // Επικύρωση του αιτήματος και έλεγχος του τρέχοντος κωδικού.
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        ], $messages)->after(function ($validator) use ($request, $user) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });

        // Αν η επικύρωση αποτύχει, επιστροφή πίσω με σφάλματα.
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Ενημέρωση του κωδικού πρόσβασης στη βάση δεδομένων.
        $user->update([
            'password' => Hash::make($password),
        ]);

        // Αποσύνδεση του χρήστη και διαγραφή token.
        DB::table('personal_access_tokens')
            ->where('tokenable_id', $user->id)
            ->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Ανακατεύθυνση στη σελίδα σύνδεσης με μήνυμα επιτυχίας.
        return redirect('/login')->with('message', 'Your password has been successfully changed. Please log in with your new password.');
    }

    /**
     * Διαγραφή λογαριασμού χρήστη.
     */
    public function destroy(Request $request)
    {
        // Επικύρωση ότι έχει εισαχθεί ο κωδικός διαγραφής.
        $request->validate([
            'delete_password' => 'required',
        ]);

        $user = Auth::user(); // Ανάκτηση του συνδεδεμένου χρήστη.

        // Έλεγχος του κωδικού που εισήχθη.
        if (!Hash::check($request->input('delete_password'), $user->password)) {
            return redirect()->back()->withErrors(['delete_password' => 'The provided password is incorrect.']);
        }

        // Διαγραφή του λογαριασμού από τη βάση δεδομένων.
        $user->delete();

        // Αποσύνδεση του χρήστη και ανακατεύθυνση στη σελίδα login.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('message', 'Your account has been successfully deleted.');
    }
}

