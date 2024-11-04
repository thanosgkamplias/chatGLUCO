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
    public function ShowProfile() {
        return view('profile');
    }

    public function UpdatePicture(Request $request) {
        $id = Auth::user()->id;

        // Validate the profile update request
        $request->validate([
            'image' => 'nullable|mimes:jpeg,png,jpg,gif|max:10240', // Optional, image validation
//            'age' => 'nullable|integer|min:1', // Example for updating other fields like age
//            'weight' => 'nullable|numeric|min:1', // Example for updating other fields like weight
        ]);

        // Check if a new profile picture was uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();

            // Move the uploaded file to the profile_pics directory
            $image->move(public_path('profile_pics'), $filename);

            // Update the user's profile picture in the database
            User::where('id', $id)->update([
                'profile_pic' => 'profile_pics/' . $filename
            ]);
        }



        // Redirect back with a success message
        return redirect()->back()->with('message', 'Your profile has been successfully updated!');
    }

    public function UpdateProfile(Request $request) {
        // Validate the profile update request
        $request->validate([
            'diagnosis' => 'required', // Example for updating other fields like age
            'weight' => 'required|numeric|min:1', // Example for updating other fields like weight
        ]);
        // Update other fields like age or weight if provided
        Patient::where('id', Auth::User()->patient->id)->update([
            'diagnosis' => $request->input('diagnosis'),
            'weight' => $request->input('weight'),
            // Add any other fields you want to allow users to update
        ]);

        return redirect()->back()->with('message', 'Your profile has been successfully updated!');
    }

    public function UpdatePassword(Request $request)
    {
        $id = Auth::User()->id;
        $user = User::where('id',$id)->first(); // Correctly retrieve the user instance
        $password = $request->input('password');

//         Track failed attempts using session or cache
        $failedAttempts = Cache::get('password_change_failed_attempts_'.$user->id, 0);


        // Validate the data from the form
        //  The new password must be the same with the conformation password
        //  The  current password must be correct
        //   A password must have a size of at least 8 characters and comprise both capital and
        //  lowercase letters, digits and special characters (e.g., !, ~, $, etc.). The username must be also
        //  validated: it must always begin with a letter and should comprise at least 5 characters that
        //  must be either alphanumeric or the ‘_’ one
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        ])->after(function ($validator) use ($request, $user) {
            if (!Hash::check($request->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });

        // If the user tries unsuccessfully to change his/her password
        if ($validator->fails()) {
            $failedAttempts++;
            Cache::put('password_change_failed_attempts_'.$user->id, $failedAttempts, now()->addMinutes(15)); // 15 minutes lockout period

            // Check if the user has failed 3 times
            if ($failedAttempts >= 3) {

                // delete the token
                DB::table('personal_access_tokens')
                    ->where("tokenable_id", Auth::user()->id)
                    ->delete();

                Auth::logout();// lock the user out
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->wantsJson()
                    ? new JsonResponse([], 204)
                    : redirect('/')->with('warning','You have been locked out because you had too many failed attempts to change your password.');
            }
//
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
//
//        // Reset failed attempts on successful password change
//        Cache::forget('password_change_failed_attempts_'.$user->id);


        // if the validation is successfull then update the user's password
        $user->update([
            'password' => Hash::make($password), // Hash the new password
        ]);


        // Log the user out because and delete the user's token
        DB::table('personal_access_tokens')
            ->where("tokenable_id", Auth::user()->id)
            ->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/login')->with('message', "You must log in again because you changed your password.");
    }
}
