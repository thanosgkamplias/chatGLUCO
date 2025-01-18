<nav class="navbar navbar-expand-md navbar-dark shadow-lg fixed-top" style="background-color: rgba(2, 62, 138, 1); z-index: 10;">
    <div class="container">
        <!-- Το logo κτλ παραμένουν τα ίδια -->
        <a class="navbar-brand" href="{{ url('/diary') }}">
            <img src="{{ URL('chatgluco_logo/chatgluco_logo_navbar.png') }}" style="width: 120px" alt="Logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar: Sidebar links σε μικρές οθόνες -->
            <ul class="navbar-nav me-auto d-md-none ps-2">
                <li class="nav-item pt-1">
                    <a class="nav-link" href="{{ route('diary') }}">
                        <i class="fi fi-rr-diary-clasp"></i> Diary
                    </a>
                </li>
                <li class="nav-item pt-1">
                    <a class="nav-link" href="{{ route('show.diagram') }}">
                        <i class="fi fi-rr-chart-histogram"></i> Diagrams
                    </a>
                </li>
                <li class="nav-item pt-1">
                    <a class="nav-link" href="{{ route('glucose.prediction.form') }}">
                        <i class="fi fi-rr-blood-test-tube-alt"></i> Predict Glucose
                    </a>
                </li>
                <li class="nav-item pt-2" style="border-top: 1px solid white;">
                    <a class="nav-link" href="{{ route('my.profile') }}">
                        <i class="fi fi-ss-admin-alt"></i> My Profile
                    </a>
                </li>
                <li class="nav-item pt-1">
                    <a class="nav-link" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">
                        <i class="fi fi-ss-exit"></i> Logout
                    </a>
                    <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ms-auto">
                @guest
                    <!-- Εάν ο χρήστης δεν είναι συνδεδεμένος -->
                @else
                    <!-- Profile Picture and Name Dropdown -->
                    <li class="nav-item dropdown d-flex align-items-center" style="padding-top: 2px;">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle d-flex align-items-center" href="#"
                           role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
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
