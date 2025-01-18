<nav class="navbar navbar-expand-md navbar-light shadow-lg align-items-start py-2 sidebar d-none d-md-block">
    <div class="pr-2" style="padding-top: 72px; display: flex">
        <ul class="navbar">
            <!-- Patient-specific menu -->
            <!-- Σύνδεσμος για το ημερολόγιο του ασθενούς -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('diary') }}">
                    <i class="fi fi-rr-diary-clasp"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diary
                </a>
            </li>
            <!-- Σύνδεσμος για τα διαγράμματα γλυκόζης -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('show.diagram') }}">
                    <i class="fi fi-rr-chart-histogram"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diagrams
                </a>
            </li>
            <!-- New Glucose Prediction Link -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('glucose.prediction.form') }}">
                    <i class="fi fi-rr-blood-test-tube-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Predict Glucose
                </a>
            </li>

            <!-- Future Glucose Trend -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('future.glucose.trend') }}">
                    <i class="fi fi-rs-arrow-trend-up"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Glucose Trend
                </a>
            </li>

            <!-- Σύνδεσμος για το προφίλ του χρήστη -->
            <li class="nav-item button-57 pt-2 mt-3" style="width: 170px; border-top: 2px solid white">
                <a class="nav-link pl-2" href="{{ route('my.profile') }}">
                    <i class="fi fi-ss-admin-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;My Profile
                </a>
            </li>

            <!-- Κουμπί αποσύνδεσης -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form-2').submit();">
                    <i class="fi fi-ss-exit"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logout
                </a>

                <!-- Χρήση φόρμας για ασφαλή αποσύνδεση μέσω POST -->
                <form id="logout-form-2" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf <!-- Προσθήκη CSRF Token για ασφάλεια -->
                </form>
            </li>
        </ul>
    </div>
</nav>
