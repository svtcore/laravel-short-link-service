@extends('layouts.admin')

@section('title', 'User Management - Admin Panel')

@section('styles')
    @vite(['resources/css/admin/styles.css'])
    @vite(['resources/css/admin/users/index.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.bootstrap5.min.css">
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
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="users-container">
        <div class="user-header">
            <h2 class="block-title">Top users</h2>
        </div>
        <table id="manageUsersTable" class="table table-hover table-striped">
            <thead class="bg-light">
                <tr>
                    <th data-priority="1">Email</th>
                    <th data-priority="2">Role</th>
                    <th class="text-center" data-priority="3">Links</th>
                    <th data-priority="4">Status</th>
                    <th data-priority="5">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="bg-white rounded shadow-sm">
                        <td class="align-middle">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-decoration-none">
                                {{ $user->email }}
                            </a>
                        </td>
                        <td class="align-middle">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary rounded-pill fs-7 px-3 py-2">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td class="align-middle d-none d-md-table-cell">
                            <span class="badge bg-primary rounded-pill fs-7 px-3 py-2">{{ $user->links_count ?? 0 }}</span>
                        </td>
                        <td class="align-middle">
                            @if ($user->status == "active")
                                <span class="badge rounded-pill fs-7 px-3 py-2 bg-success">Active</span>
                            @elseif ($user->status == "freezed")
                                <span class="badge rounded-pill fs-7 px-3 py-2 bg-secondary">Freezed</span>
                            @else
                                <span class="badge rounded-pill fs-7 px-3 py-2 bg-danger">Banned</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.users.show', $user->id) }}"
                                    class="btn btn-icon btn-sm btn-outline-dark" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="btn btn-icon btn-sm btn-outline-secondary edit-user" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal" 
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}"
                                    data-user-url="{{ route('admin.users.update', $user->id) }}"
                                    data-user-roles="{{ implode(',', $user->roles->pluck('name')->toArray()) }}"
                                    data-user-status="{{ $user->status }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-outline-danger delete-user"
                                    data-user-id="{{ $user->id }}"
                                    data-user-url="{{ route('admin.users.destroy', $user->id) }}"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.users.modals.edit')
    @include('admin.users.modals.delete')
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"></script>
    @vite(['resources/js/admin/users/index.js'])
@endsection
