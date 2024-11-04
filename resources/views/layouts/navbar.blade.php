<nav class="navbar navbar-expand-md navbar-light shadow-lg fixed-top" style="background-color: rgba(2, 62, 138, 1); z-index: 10;">
    <div class="container">
        <!-- Left Side: Logo -->
        <a class="navbar-brand" href="{{ url('/diary') }}">
            <img src="{{ URL('chatgluco_logo/chatgluco_logo.png') }}" style="width: 110px" alt="Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav me-auto">
                <!-- Add additional left-side items if necessary -->
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                <!-- Authentication Links -->
                @guest
                    {{-- @if (Route::has('login')) --}}
                    {{--     <li class="nav-item button-nav"> --}}
                    {{--         <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a> --}}
                    {{--     </li> --}}
                    {{-- @endif --}}

                    {{-- @if (Route::has('register')) --}}
                    {{--     <li class="nav-item button-nav"> --}}
                    {{--         <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a> --}}
                    {{--     </li> --}}
                    {{-- @endif --}}
                @else

                    <!-- Profile Picture and Name Dropdown in Navbar -->
                    <li class="nav-item dropdown d-flex align-items-center" style="padding-top: 2px;">
                        <!-- Profile Picture -->
                        <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <div class="sidebar_image_div">
                                <div class="sidebar_image_frame neon_border">
                                    <img src="{{ asset(Auth::User()->profile_pic) }}" class="sidebar_image" alt=""/>
                                </div>
                                <div class="sidebar_status active_account neon_border"></div>
                            </div>
                            <span class="profile-name" style="color: white; font-weight: bold; margin-left: 10px;">
                                {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}
                            </span>
                        </a>

                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ route('my.profile') }}">
                                My Profile
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                {{ __('Log Out') }}
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
