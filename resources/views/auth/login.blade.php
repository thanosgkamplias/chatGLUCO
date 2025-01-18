<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <!-- Επιτρέπει την προσαρμογή της διάταξης σε διαφορετικά μεγέθη οθονών. -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ URL('chatgluco_logo/cg_tab_logo.png') }}" type="image/x-icon" />
    <title>@yield('title', config('app.name', 'ChatGLUCO'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <!-- Εισαγωγή γραμματοσειράς Nunito από Bunny Fonts. -->
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Icon Libraries -->
    <!-- Εισαγωγή βιβλιοθηκών εικονιδίων από Flaticon. -->
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-bold-straight/css/uicons-bold-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-regular-straight/css/uicons-regular-straight.css'>
    <!-- Εισαγωγή Bootstrap 4.5.2 για τη διάταξη και το στυλ της σελίδας. -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Εισαγωγή της βιβλιοθήκης jQuery (έκδοση 3.6.0). Απαραίτητη για διάφορες διεργασίες. -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Popper.js και Bootstrap JS για τα δυναμικά στοιχεία του Bootstrap (π.χ. tooltips, modals). -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Χρήση του Vite για φόρτωση αρχείων CSS και JavaScript. -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/css/app.css'])
</head>
<body>

<!-- Κύριο container της εφαρμογής. -->
<div id="app">

    {{-- Αν ο χρήστης είναι ήδη συνδεδεμένος (auth()->check()), ανακατευθύνουμε στη σελίδα "ημερολόγιο" (diary).
         Αυτό προστατεύει το login page από ήδη συνδεδεμένους χρήστες. --}}
    @if (auth()->check())
        <script>window.location = "{{ route('diary') }}";</script>
    @endif

    {{-- Αποθηκεύουμε τον τύπο της φόρμας (login ή register) σε μια μεταβλητή, ώστε να γνωρίζουμε σε ποια καρτέλα να πάμε.
     Το old('form_type') επιστρέφει την προηγούμενη τιμή του πεδίου form_type αν υπήρξε αποτυχία υποβολής φόρμας. --}}
    @php
        $formType = old('form_type');
    @endphp

    <main>
        <!-- Διαχείριση καρτελών για το login και το register. -->
        <div class="tab-wrap shadow-lg min-vh-100" >

            {{-- Η πρώτη καρτέλα είναι η login.
     Αν το $formType είναι 'login' ή δεν υπάρχει (δεν έχει οριστεί), το ραδιοκουμπί για την login καρτέλα είναι εξ αρχής επιλεγμένο. --}}
            <input type="radio" id="tab1" name="tabGroup1" class="tab" @if($formType == 'login' || !$formType) checked @endif>
            <label for="tab1">Log In</label>

            {{-- Η δεύτερη καρτέλα είναι η register.
     Αν το $formType είναι 'register', τότε αυτή επιλέγεται αυτόματα. --}}
            <input type="radio" id="tab2" name="tabGroup1" class="tab" @if($formType == 'register') checked @endif>
            <label for="tab2">Sign Up</label>

            <!-- Περιεχόμενο της καρτέλας "Log In". -->
            <div class="tab__content row m-0 " >
                {{-- Αριστερό τμήμα: Εμφάνιση λογοτύπου ChatGLUCO και μήνυμα καλωσορίσματος. --}}
                <div class="col-lg-6 d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <h2 style="color: white;">Welcome to</h2>
                        <img src="{{ URL('chatgluco_logo/chatgluco_logo.png') }}" style="width: 320px" alt="ChatGLUCO Logo">
                    </div>
                </div>

                {{-- Δεξί τμήμα: Η φόρμα σύνδεσης. --}}
                <form class="col-lg-6 bg-light" style="display:flex; justify-content: center; min-height: 87vh !important;" method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Κρυφό πεδίο για να ξέρουμε ότι αυτή είναι η φόρμα "login". --}}
                    <input type="hidden" name="form_type" value="login">

                    <div class="col-10">
                        <div class="text-center mb-3 mt-5 pb-5 pt-5">
                            <h2 style="color: rgba(2, 62, 138, 1);">Log In</h2>
                        </div>

                        {{-- Μήνυμα επιτυχίας (π.χ. "Ο λογαριασμός δημιουργήθηκε επιτυχώς") που έρχεται από το session.
     Προβάλλεται μόνο αν υπάρχει session('message'). --}}
                        @if (session('message'))
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center position-relative" role="alert">
                                <i class="fi fi-rr-check-circle mr-2" style="font-size: 1.5em;"></i>
                                <span>{{ session('message') }}</span>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <!-- Display Login Errors with Warning Icon -->
                        @if ($errors->login->has('login_error'))
                            <!-- Include the CSS in a style tag -->
                            <style>
                                .alert-dismissible .close {
                                    position: absolute;
                                    top: 50%;
                                    right: 15px; /* Adjust as needed */
                                    transform: translateY(-50%);
                                    line-height: 1;
                                    font-size: 1.2rem; /* Adjust font size for better alignment */
                                }
                            </style>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center position-relative" role="alert">
                                <i class="fi fi-rr-exclamation mr-2" style="font-size: 1.5em;"></i>
                                <span>{{ $errors->login->first('login_error') }}</span>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        {{-- Πεδίο για το Email του χρήστη. --}}
                        <input id="email" type="email" placeholder="Email" class="form-control mt-4 @error('email', 'login') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                        @error('email', 'login')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <!-- Password Field with Eye Icon -->
                        <div class="input-group mt-4">
                            <input id="login-password" placeholder="Password" type="password" class="form-control @error('password', 'login') is-invalid @enderror" name="password" required autocomplete="current-password">
                            <div class="input-group-append">
                                <span class="input-group-text" style="cursor: pointer; display: flex; align-items: center; line-height: 1.5;">
                                    <i class="fi-rr-eye" data-placement="top" id="toggleLoginPassword"></i>
                                </span>
                            </div>
                        </div>

                        {{-- Αν υπάρχουν λάθη στο password, τα εμφανίζουμε εδώ. --}}
                        @error('password', 'login')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        {{-- Κουτάκι "Remember Me" για να παραμένει ο χρήστης συνδεδεμένος. --}}
                        <div class="pt-5 pb-4">
                            <div class="form-check col-lg-12" >
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                            </div>
                        </div>

                        {{-- Κουμπί "Log In" που υποβάλλει τη φόρμα στο route('login'). --}}
                        <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(2, 62, 138, 0.8);">
                            {{ __('Log In') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Περιεχόμενο της καρτέλας "Sign Up". --}}
            <div class="tab__content row m-0">
                {{-- Φόρμα εγγραφής. Στο αριστερό κομμάτι (col-lg-6) θα μπουν τα πεδία. --}}
                <form class="col-lg-6 bg-light" style="display:flex; justify-content: center; min-height: 87vh !important;" method="POST" action="{{ route('register') }}">
                    @csrf

                    {{-- Κρυφό πεδίο για να ξέρουμε ότι αυτή είναι η φόρμα "register". --}}
                    <input type="hidden" name="form_type" value="register">

                    <div class="col-10">
                        <div class="text-center mt-5 mb-4 pb-3">
                            <h2 style="color: rgba(2, 62, 138, 0.8);">Sign Up</h2>
                        </div>

                        {{-- Πεδίο για Όνομα (Firstname). --}}
                        <input id="firstname" placeholder="Firstname" type="text" class="form-control mt-4 @error('firstname', 'register') is-invalid @enderror" name="firstname" value="{{ old('firstname') }}" required autocomplete="firstname" autofocus>

                        @error('firstname', 'register')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        {{-- Πεδίο για Επώνυμο (Lastname). --}}
                        <input id="lastname" placeholder="Lastname" type="text" class="form-control mt-4 @error('lastname', 'register') is-invalid @enderror" name="lastname" value="{{ old('lastname') }}" required autocomplete="lastname" autofocus>

                        @error('lastname', 'register')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        {{-- Πεδίο Email για εγγραφή. --}}
                        <input id="email" type="email" placeholder="Email" class="form-control mt-4 @error('email', 'register') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                        @error('email', 'register')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        {{-- Πεδίο Ημερομηνίας Γέννησης (birthdate). --}}
                        <input id="birthdate" type="date" class="form-control mt-4" name="birthdate" value="{{ old('birthdate') }}" required>

                        {{-- Επιλογή φύλου (gender). --}}
                        <select name="gender" id="gender" class="form-select mt-4">
                            <option value="">-- Select Gender --</option>
                            <option value="Male" @if(old('gender') == 'Male') selected @endif>Male</option>
                            <option value="Female" @if(old('gender') == 'Female') selected @endif>Female</option>
                        </select>

                        @error('gender', 'register')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror

                        {{-- Πεδίο Password με εικονίδιο πληροφοριών για τις απαιτήσεις (tooltip). --}}
                        <div class="input-group mt-4">
                            <input id="password" type="password" placeholder="Password" class="form-control @error('password', 'register') is-invalid @enderror" name="password" required autocomplete="new-password">
                            <div class="input-group-append">
                                <span class="input-group-text" style="line-height: 1.5;">
                                    {{-- Το data-toggle="tooltip" ενεργοποιεί το tooltip στο mouse over. --}}
                                    <i class="fi-rr-info" data-toggle="tooltip" data-placement="top" title="Your password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, one number, and one special character."></i>
                                </span>
                            </div>
                        </div>
                        @error('password', 'register')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        {{-- Επαλήθευση κωδικού (Confirm Password). --}}
                        <input id="password-confirm" placeholder="Confirm Password" type="password" class="form-control mt-4" name="password_confirmation" required autocomplete="new-password">

                        {{-- Κουμπί "Register" που υποβάλλει τη φόρμα στο route('register'). --}}
                        <button type="submit" class="btn btn-primary form-control mt-5" style="background-color: rgba(2, 62, 138, 0.8);">
                            {{ __('Register') }}
                        </button>

                    </div>
                </form>

                {{-- Δεξιά στήλη της φόρμας Register: λογότυπο και μήνυμα καλωσορίσματος. --}}
                <div class="col-lg-6 d-flex justify-content-center align-items-center">
                    <div class="text-center">
                        <h2 style="color: white;">Welcome to</h2>
                        <img src="{{ URL('chatgluco_logo/chatgluco_logo.png') }}" style="width: 320px" alt="ChatGLUCO Logo">
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

{{-- Εδώ ορίζονται κάποια jQuery scripts για τη σελίδα. --}}
<script>
    // Μόλις η σελίδα φορτώσει, συσχετίζουμε το κουμπί κλεισίματος της popup (εάν υπάρχει).
    $(document).ready(function() {
        $(".popup-close").click(function () {
            $(".popup").fadeOut();
        });
    });

    // Ενεργοποίηση των tooltips του Bootstrap.
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });

    $(document).ready(function() {
        // Λειτουργία εμφάνισης/απόκρυψης κωδικού (eye icon) στη φόρμα Login.
        $("#toggleLoginPassword").click(function() {
            var passwordInput = $("#login-password");
            var type = passwordInput.attr('type') === 'password' ? 'text' : 'password';
            passwordInput.attr('type', type);
            // Εναλλαγή του εικονιδίου από eye σε eye-crossed icon
            $(this).toggleClass('fi-rr-eye fi-rr-eye-crossed');
        });

        // Επαναλαμβάνουμε το κλείσιμο popup.
        $(".popup-close").click(function () {
            $(".popup").fadeOut();
        });

        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

</body>
</html>
