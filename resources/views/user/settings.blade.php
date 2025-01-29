@extends('layouts.app')

@section('title', 'URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/settings.css'])
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
        <strong>Success!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; color:rgb(3, 2, 2);">
        <strong>Error!</strong>
        <ul style="list-style-type: none; padding-left: 0; margin-top: 10px;">
            @foreach($errors->all() as $error)
            <li style="background-color:rgb(252, 230, 232); border-radius: 8px; margin-bottom: 8px; padding: 10px; font-weight: 500;">
                {{ $error }}
            </li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Profile Information -->
        <div class="col-lg-6">
            <div class="settings-card shadow-lg bg-white rounded-3">
                <h5 class="mb-4"><i class="bi bi-person-gear me-2"></i>Profile Information</h5>
                <form action="{{ route('user.settings.profile') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" class="form-control" name="name" id="name" 
                                   placeholder="Enter your full name" 
                                   value="{{ $user_data->name }}" 
                                   maxlength="255">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email address" 
                                   value="{{ $user_data->email }}" 
                                   required 
                                   maxlength="255">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">
                        <i class="bi bi-save2 me-2"></i>Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6">
            <div class="settings-card shadow-lg bg-white rounded-3">
                <h5 class="mb-4"><i class="bi bi-shield-lock me-2"></i>Change Password</h5>
                <form action="{{ route('user.settings.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" name="password" 
                                   id="currentPassword" 
                                   placeholder="Enter current password" 
                                   required 
                                   minlength="8">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="newPassword" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" class="form-control" name="new_password" 
                                   id="newPassword" 
                                   placeholder="Enter new password" 
                                   required 
                                   minlength="8">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                            <input type="password" class="form-control" 
                                   name="new_password_confirmation" 
                                   id="confirmPassword" 
                                   placeholder="Confirm new password" 
                                   required 
                                   minlength="8">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-custom w-100">
                        <i class="bi bi-arrow-repeat me-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Data Request -->
        <div class="col-12">
            <div class="settings-card shadow-lg bg-white rounded-3">
                <h5 class="mb-4"><i class="bi bi-database me-2"></i>Data Management</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="border-start border-3 border-info ps-3">
                            <h6>Request Account Data</h6>
                            <form action="{{ route('user.settings.data') }}" method="POST">
                                @csrf
                                <p class="text-muted small mb-3">
                                    You will receive your data archive within 30 days.
                                </p>
                                <button type="submit" class="btn btn-custom">
                                    <i class="bi bi-download me-2"></i>Request Data
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border-start border-3 border-danger ps-3">
                            <h6>Delete Account</h6>
                            <form action="{{ route('user.settings.deletion') }}" method="POST">
                                @csrf
                                <p class="text-muted small mb-3">
                                    This action cannot be undone. All data will be permanently deleted.
                                </p>
                                <button type="submit" class="btn delete-btn">
                                    <i class="bi bi-trash3 me-2"></i>Delete Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@endsection