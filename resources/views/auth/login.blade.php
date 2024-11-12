<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ URL('chatgluco_logo/cg_tab_logo.png') }}" type="image/x-icon" />
    <title>@yield('title', config('app.name', 'ChatGLUCO'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Icon Libraries -->
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-regular-rounded/css/uicons-regular-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-bold-straight/css/uicons-bold-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-straight/css/uicons-solid-straight.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.5.1/uicons-regular-straight/css/uicons-regular-straight.css'>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js', 'resources/css/app.css'])
</head>
<body>
<div id="app">

    <!-- Redirect authenticated users to 'Diary' if they try to access the login page -->
    @if (auth()->check())
        <script>window.location = "{{ route('diary') }}";</script>
    @endif

    <main>
            <div class="tab-wrap shadow-lg min-vh-100" >
                <!-- active tab on page load gets checked attribute -->
                <input type="radio" id="tab1" name="tabGroup1" class="tab" checked>
                <label for="tab1">Log In</label>

                <input type="radio" id="tab2" name="tabGroup1" class="tab">
                <label for="tab2">Sign Up</label>

                <div class="tab__content row m-0 " >
                    <div class="col-lg-6 d-flex justify-content-center align-items-center">
                        <div class="text-center">
                            <h2 style="color: white;">Welcome to</h2>
                            <img src="{{ URL('chatgluco_logo/chatgluco_logo.png') }}" style="width: 350px" alt="DiabeticAI Logo">
                        </div>
                    </div>

                    <form class="col-lg-6 bg-light" style="display:flex; justify-content: center; min-height: 87vh !important;" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="col-10">
                        <div class="text-center mb-3 mt-5 pb-5 pt-5">
                            <h2 style="color: rgba(2, 62, 138, 1);">Log In</h2>
                        </div>

                            <div class="container col-md-12">
                                @if(session()->has('message'))
                                    <div class="alert alert-success alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert">x</button>
                                        {{session()->get('message')}}
                                    </div>
                                @endif
                            </div>
                            <div class="container col-md-12">
                                @if(session()->has('warning'))
                                    <div class="alert alert-danger alert-dismissible">
                                        <button type="button" class="close" data-dismiss="alert">x</button>
                                        {{session()->get('warning')}}
                                    </div>
                                @endif
                            </div>

                        <input id="email" type="email" placeholder="Username" class="form-control mt-4 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror


                        <input id="password" placeholder="Password" type="password" class="form-control mt-4 @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror


                        <div class="pt-5 pb-4">
                            <div class="form-check col-lg-12" >
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary form-control" style="background-color: rgba(2, 62, 138, 0.8); paddin-top:20px">
                            {{ __('Log In') }}
                        </button>

                        </div>

                    </form>

                </div>

                <div class="tab__content row m-0">
                    <form class="col-lg-6 bg-light" style="display:flex; justify-content: center; min-height: 87vh !important; " method="POST" action="{{ route('register') }}"> @csrf
                        <div class="col-10">
                            <div class="text-center  mt-5 mb-4 pb-3">
                                <h2 style="color: rgba(2, 62, 138, 0.8);">Sign Up</h2>
                            </div>

                            <input id="firstname" placeholder="Firstname" type="text" class="form-control mt-4 @error('firstname') is-invalid @enderror" name="firstname" value="{{ old('firstname') }}" required autocomplete="firstname" autofocus>

                            @error('firstname')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror

                            <input id="lastname" placeholder="Lastname" type="text" class="form-control mt-4 @error('lastname') is-invalid @enderror" name="lastname" value="{{ old('lastname') }}" required autocomplete="lastname" autofocus>

                            @error('lastname')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror

                            <input id="email" type="email" placeholder="Email" class="form-control mt-4 @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">

                            @error('email')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror


                            <input id="birthdate" type="date" class="form-control  mt-4" name="birthdate" value="{{ old('email') }}" required>

                            <select name="gender" id="gender" class="form-select mt-4">
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                                <option value="Other">Other</option>
                            </select>


                            <input id="password" type="password" placeholder="Password" class="form-control mt-4 @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">

                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                            <input id="password-confirm" placeholder="Confirm Password" type="password" class="form-control mt-4" name="password_confirmation" required autocomplete="new-password">
                            <button type="submit" class="btn btn-primary form-control mt-5" style="background-color: rgba(2, 62, 138, 0.8);">
                                {{ __('Register') }}
                            </button>

                        </div>
                    </form>


                    <div class="col-lg-6 d-flex justify-content-center align-items-center">
                        <div class="text-center">
                            <h2 style="color: white;">Welcome to</h2>
                            <img src="{{ URL('chatgluco_logo/chatgluco_logo.png') }}" style="width: 350px" alt="DiabeticAI Logo">
                        </div>
                    </div>
                </div>
            </div>
    </main>
</div>

</body>
</html>
<script>
    $("document").ready(function() {
        //function that close the popups forms
        $(".popup-close").click(function () {
            $(".popup").fadeOut();
        });
    });
</script>
