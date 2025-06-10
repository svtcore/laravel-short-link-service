@extends('layouts.admin')

@section('title', 'Search Links Results')

@section('styles')
    @vite(['resources/css/admin/styles.css'])
    @vite(['resources/css/admin/users/index.css'])
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css"
        integrity="sha256-rKBEfnQsmpf8CmrOAohl7rVNfVUf+8mtA/8AKfXN7YA=" crossorigin="anonymous">
    <!-- DataTables Responsive extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css"
        integrity="sha256-LliUrn7vT0PjsdPWfsGcQdDPMI0faaUPWzVGQahZkCQ=" crossorigin="anonymous">
    <!-- DataTables Buttons extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.bootstrap5.min.css"
        integrity="sha256-inPB99Z0Y2Ijp7YHBoE4K2W7CoUcM6YZ+aK4CgjrPY8=" crossorigin="anonymous">
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
            <ul style="list-style-type: none; padding-left: 0; margin-top: 10px;">
                @foreach ($errors->all() as $error)
                    <li
                        style="background-color: #fecaca; border-radius: 8px; margin-bottom: 8px; padding: 10px; font-weight: 500; color: #7f1d1d;">
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="users-container">
        <div class="user-header d-flex align-items-center mb-3">
            <h2 class="block-title mb-0 me-3">Search Results for query</h2>
        </div>
        <div class="search-query-badge">
            <span class="badge bg-light text-dark fs-6 p-2 border">
                <i class="bi bi-search me-2"></i>"{{ $query }}"
            </span>
        </div>
        <hr />
        <table id="manageUsersTable" class="table table-hover table-striped">
            <thead>
                <tr>
                    <th class="text-center">Email</th>
                    <th class="text-center">Links</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $user)
                    <tr>
                        <td class="align-middle text-center">
                            @isset($user->id)
                                {{ $user->email }}
                            @else
                                {{ $user->ip_address }}
                            @endisset
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge bg-primary rounded-pill fs-7 px-3 py-2">
                                {{ $user->links_count ?? 0 }}
                            </span>
                        </td>
                        <td class="d-none d-sm-table-cell text-center">
                            @isset($user->id)
                                @if ($user->status == "active")
                                    <span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm text-center"
                                        style="font-size: 0.875rem; transition: all 0.3s ease;">
                                        Active
                                    </span>
                                @elseif ($user->status == "freezed")
                                    <span class="badge rounded-pill bg-secondary text-white px-3 py-2 shadow-sm text-center"
                                        style="font-size: 0.875rem; transition: all 0.3s ease;">
                                        Freezed
                                    </span>
                                @elseif ($user->status == "banned")
                                    <span class="badge rounded-pill bg-danger text-white px-3 py-2 shadow-sm text-center"
                                        style="font-size: 0.875rem; transition: all 0.3s ease;">
                                        Banned
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary text-white px-3 py-2 shadow-sm text-center"
                                        style="font-size: 0.875rem; transition: all 0.3s ease;">
                                        Unknown
                                    </span>
                                @endif
                            @endisset
                        </td>
                        <td class="align-middle text-center">
                            @isset($user->ip_address)
                                <form action="{{  route('admin.search.links.byUserIP') }}" method="GET">
                                    @csrf
                                    <input type="hidden" name="ip" value="{{ $user->ip_address }}">
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-primary me-1 w-100"
                                        title="View user links">
                                        <i class="bi bi-link"></i>
                                        Show Links
                                    </button>
                                </form>
                            @endisset
                            @isset($user->id)
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-icon btn-sm btn-outline-dark"
                                    title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <button class="btn btn-icon btn-sm btn-outline-secondary edit-user" data-bs-toggle="modal"
                                    data-bs-target="#editUserModal" data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}"
                                    data-user-role="{{ $user->role }}" data-user-status="{{ $user->status }}"
                                    data-user-roles="{{ $user->roles->pluck('name')->implode(',') }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-outline-danger delete-user" data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endisset
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endsection

@section('scripts')
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"
        integrity="sha256-aTBve1VKWozmjo9Nb2E73RvP4t8xOWLn/IPPX2vl4IU=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"
        integrity="sha256-U3zt3BGRnSBU8GxdNWp9CGmrMKBUthlz2ia7LERbiNc=" crossorigin="anonymous"></script>

    <!-- Bootstrap JS (required for DataTables with Bootstrap) -->
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js"
        integrity="sha256-2SmX/IhbmVdeg5paUsa6SExqBi0iF2LF+Dh7tG/Uv7s=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.bootstrap5.min.js"
        integrity="sha256-dU1f0caF6eBMRglcDQ2xQ0oLhXNConpBNc2JSFeINL8=" crossorigin="anonymous"></script>

    <!-- Responsive extension for DataTables -->
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"
        integrity="sha256-fgKMuTMhdhI9JaA6XdUSrgkOY41nWaeN5eH1ctK4yog=" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js"
        integrity="sha256-Ml0aZ+BzTKuF3cSJIyCElyJjEVFJjqoFucYGL6/hsmI=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/js/admin/users/index.js'])
@endsection
