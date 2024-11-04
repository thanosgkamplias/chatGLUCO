<nav class="navbar navbar-expand-md navbar-light shadow-lg align-items-start py-2 sidebar">
    <div class="pr-2" style="padding-top: 72px; display: flex">
        <ul class="navbar">

            <!-- Patient-specific menu -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('diary') }}"><i class="fi fi-rr-diary-clasp"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diary</a>
            </li>
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('show.diagram') }}"><i class="fi fi-rr-chart-histogram"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Diagrams</a>
            </li>

            <!-- Common Profile Link -->
            <li class="nav-item button-57 pt-2 mt-3" style="width: 170px; border-top: 2px solid white">
                <a class="nav-link pl-2" href="{{ route('my.profile') }}"><i class="fi fi-ss-admin-alt"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;My Profile</a>
            </li>

            <!-- Logout Button -->
            <li class="nav-item button-57 pt-2" style="width: 170px;">
                <a class="nav-link pl-2" href="{{ route('logout') }}"  onclick="event.preventDefault(); document.getElementById('logout-form-2').submit();">
                    <i class="fi fi-ss-exit"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Logout</a>

                <form id="logout-form-2" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

            </li>
        </ul>
    </div>
</nav>



<script>
    $(document).ready(function () {
        // Initially hide the drop-down div
        $(".drop_down_div_user").hide();
        $(".drop_down_div_conference").hide();

        // Add click event to the drop-down trigger
        $(".choice_drop_down_user").click(function(){
            // Toggle visibility of the drop-down div
            $(".drop_down_div_user").toggle();

            // Toggle the 'active' class based on the visibility of the drop-down div
            if ($(".drop_down_div_user").is(':visible')) {
                $(".choice_drop_down_user").addClass('active');
            } else {
                $(".choice_drop_down_user").removeClass('active');
            }
        });

    });
</script>
