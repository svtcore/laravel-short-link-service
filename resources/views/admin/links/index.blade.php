@extends('layouts.admin')

@section('title', 'Link Management - URL Shortening Service')

@section('styles')
    @vite(['resources/css/admin/styles.css'])
    @vite(['resources/css/admin/links/index.css'])
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
        <div class="alert alert-success alert-dismissible fade show" role="alert"
            style="border-radius: 12px; padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert"
            style="border-radius: 12px; padding: 20px; background-color: #fee2e2; border: 1px solid #fca5a5; color: #7f1d1d;">
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

    <div class="links-container">
        <div class="link-header">
            <h2 class="block-title">Manage Links</h2>
            <button class="add-link-btn" data-bs-toggle="modal" data-bs-target="#linkModal">
                Add Link
            </button>
        </div>
        <table id="manageLinksTable" class="table table-hover table-striped" style="border-collapse: separate; border-spacing: 0 1rem;">
            <thead class="bg-light">
                <tr>
                    <th data-priority="1">User</th>
                    <th data-priority="2">Short URL</th>
                    <th data-priority="3">Total Clicks</th>
                    <th data-priority="4" class="d-none d-sm-table-cell">Created</th>
                    <th data-priority="4">Status</th>
                    <th data-priority="5">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($links as $link)
                    <tr class="bg-white rounded shadow-sm">
                        <!-- User Column -->
                        <td class="align-middle text-left">
                            @if (isset($link->user_id))
                                <a href="{{ route('admin.users.show', $link->user_id) }}" class="text-decoration-none">
                                    {{ $link->user->email }}
                                </a>
                            @else
                                {{ $link->ip_address }}
                            @endif
                        </td>

                        <!-- Short URL Column -->
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <a href="https://{{ $link->domain->name }}/{{ $link->short_name }}"
                                    class="text-truncate text-primary" style="max-width: 180px;" target="_blank">
                                    {{ $link->domain->name }}/{{ $link->short_name }}
                                </a>
                                <button class="btn btn-link btn-sm text-secondary copy-button p-0"
                                    data-link="https://{{ $link->domain->name }}/{{ $link->short_name }}"
                                    title="Copy Short URL">
                                    <i class="bi bi-clipboard fs-6"></i>
                                </button>
                            </div>
                        </td>

                        <!-- Clicks Column -->
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-pill fs-7 px-3 py-2">
                                    {{ $link->link_histories_count }}
                                </span>
                                <small class="text-muted d-none d-sm-inline fs-7">
                                    / {{ $link->unique_ip_count }}
                                </small>
                            </div>
                        </td>

                        <!-- Created Column -->
                        <td class="align-middle d-none d-sm-table-cell">
                            <span class="text-muted small">{{ $link->created_at->format('d.m.Y') }}</span>
                        </td>

                        <td class="d-none d-sm-table-cell text-center">
                            @if ($link->available)
                                <span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm text-center"
                                    style="font-size: 0.875rem; transition: all 0.3s ease;">
                                    Active
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger text-white px-3 py-2 shadow-sm text-center"
                                    style="font-size: 0.875rem; transition: all 0.3s ease;">
                                    Inactive
                                </span>
                            @endif
                        </td>

                        <!-- Actions Column -->
                        <td class="align-middle">
                            <div class="d-flex gap-2">
                                <button class="btn btn-icon btn-sm btn-outline-secondary view-stats" data-bs-toggle="modal"
                                    data-bs-target="#fullScreenStatsModal" data-id="{{ $link->id }}" title="Statistics">
                                    <i class="bi bi-graph-up"></i>
                                </button>
                                <button class="btn btn-icon btn-sm btn-outline-secondary edit-link" data-bs-toggle="modal"
                                    data-bs-target="#editLinkModal" data-id="{{ $link->id }}"
                                    data-form-url="{{ route('admin.links.update', $link->id) }}"
                                    data-name="{{ $link->custom_name }}" data-url="{{ $link->destination }}"
                                    data-status="{{ $link->available }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.links.destroy', $link->id) }}" method="POST"
                                    id="delete-form-{{ $link->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-icon btn-sm btn-outline-danger delete-button" type="button"
                                        data-id="{{ $link->id }}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.links.modals.add')
    @include('admin.links.modals.edit')
    @include('admin.links.modals.delete')'
    @include('admin.links.modals.stats')'

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

    @vite(['resources/js/admin/links/index.js'])
@endsection