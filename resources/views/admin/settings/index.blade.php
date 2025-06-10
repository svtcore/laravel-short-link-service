@extends('layouts.admin')

@section('title', 'User Management - Admin Panel')

@section('styles')
    @vite(['resources/css/admin/styles.css'])
    @vite(['resources/css/admin/settings.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css"
        integrity="sha256-rKBEfnQsmpf8CmrOAohl7rVNfVUf+8mtA/8AKfXN7YA=" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous">
@endsection


@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="settings-card">
                    <h3><i class="bi bi-person"></i> Basic Information</h3>
                    <form class="settings-form" method="POST" action="{{ route('admin.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ auth()->user()->name }}"
                                required maxlength="255" pattern="^[a-zA-Z0-9_\- ]+$" 
                                title="Only letters, numbers, spaces, hyphens and underscores are allowed">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ auth()->user()->email }}" required maxlength="255">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Information
                        </button>
                    </form>
                </div>

                <div class="settings-card">
                    <h3><i class="bi bi-shield-lock"></i> Change Password</h3>
                    <form class="settings-form" method="POST" action="{{ route('admin.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" 
                                    required maxlength="255">
                                <button class="toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required
                                    minlength="8" pattern="^(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[@$!%*?&]).*$"
                                    title="Password must contain: at least 8 characters, one letter, one number and one special character (@$!%*?&)">
                                <button class="toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted small">
                                Password requirements:
                                <ul class="mb-0 ps-3">
                                    <li>Minimum 8 characters</li>
                                    <li>At least one English letter</li>
                                    <li>At least one number</li>
                                    <li>At least one special character (@$!%*?&)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password_confirmation"
                                    name="new_password_confirmation" required maxlength="255">
                                <button class="toggle-password" type="button">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="settings-card">
                    @csrf
                    <h3><i class="bi bi-globe"></i> Site Settings</h3>
                    <div class="mb-3">
                        <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                        <select class="form-select" id="maintenance_mode" name="maintenance_mode" required
                            data-url="{{ route('admin.settings.maintenance') }}">
                            <option value="0" {{ !app()->isDownForMaintenance() ? 'selected' : '' }}>Disabled</option>
                            <option value="1" {{ app()->isDownForMaintenance() ? 'selected' : '' }}>Enabled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    @vite(['resources/js/admin/settings/index.js'])
    @vite(['resources/js/admin/settings/maintenance.js'])
@endsection
