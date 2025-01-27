@extends('layouts.app')

@section('title', 'URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/settings.css'])
@endsection

@section('content')
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-3">Account Settings</h1>
        <p class="lead mb-4">Manage your account details and preferences.</p>
    </div>
</section>

<section class="container settings-section">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" title="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong>
        <ul class="list-unstyled mt-2">
            @foreach($errors->all() as $error)
            <li class="bg-light rounded-3 mb-2 p-2 font-weight-bold">
                {{ $error }}
            </li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" title="Close"></button>
    </div>
    @endif

    <div class="d-flex justify-content-between flex-wrap">
        <div class="settings-card flex-grow-1 me-3">
            <h5>Profile Information</h5>
            <form action="{{ route('user.settings.profile') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name" value="{{ $user_data->name }}" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" value="{{ $user_data->email }}" required maxlength="255">
                </div>
                <button type="submit" class="btn save-btn">Save Changes</button>
            </form>
        </div>

        <div class="settings-card flex-grow-1 ms-3">
            <h5>Change Password</h5>
            <form action="{{ route('user.settings.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="password" id="currentPassword" placeholder="Enter your current password" required minlength="8" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="Enter a new password" required minlength="8" maxlength="255">
                </div>

                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="new_password_confirmation" id="confirmPassword" placeholder="Confirm your new password" required minlength="8" maxlength="255">
                </div>
                <button type="submit" class="btn save-btn">Change Password</button>
            </form>
        </div>
    </div>

    <div class="settings-card flex-grow-1">
        <h5>Request Account Data</h5>
        <form action="{{ route('user.settings.data') }}" method="POST">
            @csrf
            <div class="form-check">
                <label class="form-check-label" for="confirmRequest">
                    By submitting, you understand that account data will be provided within 30 days.
                </label>
            </div>
            <button type="submit" class="btn btn-custom mt-3">Request Account Data</button>
        </form>
    </div>

    <div class="settings-card flex-grow-1">
        <h5>Request Account Deletion</h5>
        <form action="{{ route('user.settings.deletion') }}" method="POST">
            @csrf
            <div class="form-check">
                <label class="form-check-label" for="confirmDeletion">
                    By submitting, you understand that account deletion is permanent and cannot be undone.
                </label>
            </div>
            <button type="submit" class="btn delete-btn mt-3">Request Account Deletion</button>
        </form>
    </div>
</section>
@endsection

@section('scripts')
@endsection