@extends('layouts.app')
@section('title', 'My Profile')   <!-- This will set the browser tab title -->

@section('content')
    <div class="container">
        <!-- Profile Picture Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-light shadow-lg mb-4">
                    <div class="card shadow-lg">
                        <div class="card-body">
                            <div class="d-flex justify-content-start mt-5 px-4">
                                <!-- Profile Image Container -->
                                <div class="profile_div">
                                    <div class="profile_frame">
                                        <img class="shadow-lg profile" src="{{ asset(Auth::User()->profile_pic) }}"  alt="Profile Picture"/>
                                    </div>
                                </div>

                                <!-- User Information -->
                                <div class="pt-5 ml-3">
                                    <h3 class="title-bold">{{ Auth::User()->firstname }} {{ Auth::User()->lastname }}</h3>
                                    <p class="info-light">Patient</p>
                                </div>
                            </div>


                            <!-- Buttons -->

                                <form action="{{route('my.profile.picture')}}" method="post" enctype="multipart/form-data" class="d-flex justify-content-end form">
                                    @csrf
                                    <label for="imageInput" class="btn btn-primary mr-2 mb-0">Add Photo</label>
                                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">
                                    <button type="submit" class="btn btn-primary mb-0">Save</button>
                                </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Information Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-light shadow-lg mb-4">
                    <div class="card-header bg-white">
                        <h5 class="title-bold mb-0">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><span class="info-light">First Name:</span> <span class="info-normal">{{ Auth::User()->firstname }}</span></p>
                                <p><span class="info-light">Last Name:</span> <span class="info-normal">{{ Auth::User()->lastname }}</span></p>
                                <p><span class="info-light">Email Address:</span> <span class="info-normal">{{ Auth::User()->email }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><span class="info-light">Date of Birth:</span> <span class="info-normal">{{ Auth::User()->patient->birth_at }}</span></p>
                                <p><span class="info-light">Gender:</span> <span class="info-normal">{{ Auth::User()->patient->gender }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Health Information Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-light shadow-lg mb-4">
                    <div class="card-header bg-white">
                        <h5 class="title-bold mb-0">Health Information</h5>
                    </div>
                    <form action="{{route('my.profile.data')}}" method="post" class="form">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="diagnosis"><strong>Diagnosis:</strong></label>
                                        <select name="diagnosis" id="diagnosis" class="form-select" required>
                                            @if(Auth::User()->patient->diagnosis===null || Auth::User()->patient->diagnosis==='')
                                                <option selected hidden>--Select Diagnosis--</option>
                                            @else
                                                <option value="{{ Auth::User()->patient->diagnosis }}" selected hidden>{{ Auth::User()->patient->diagnosis }}</option>
                                            @endif

                                            <option value="Type I">Type I</option>
                                            <option value="Type II">Type II</option>
                                        </select>
                                    </div>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="weight"><strong>Weight:</strong></label>
                                        <input type="number" name="weight" value="{{ Auth::User()->patient->weight }}" class="form-control" placeholder="Enter Weight" required step="0.01">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Save Changes Button -->
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password Section -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg bg-light">
                    <div class="card-header bg-white">
                        <h5 class="title-bold mb-0">Change Password</h5>
                    </div>
                    <form action="{{ route('my.profile.password') }}" method="POST" class="form">
                        @csrf

                        <div class="card-body">
                            <div class="form-group">
                                <label for="current_password" class="col-form-label">Current Password</label>
                                <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" required autocomplete="current-password">
                            </div>
                            @error('current_password')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <div class="form-group">
                                <label for="password" class="col-form-label">New Password</label>
                                <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                            </div>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <div class="form-group">
                                <label for="password-confirm" class="col-form-label">Confirm New Password</label>
                                <input id="password-confirm" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <!-- Change Password Button -->
                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JS for Previewing Profile Picture before upload -->
    <script>
        $('#imageInput').on('change', function(e) {
            var file = e.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                $('.profile_frame img').attr('src', e.target.result);
            }

            reader.readAsDataURL(file);
        });
    </script>

    @section('sidebar')
        @include('layouts.sidebar')
    @endsection
@endsection


