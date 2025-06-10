@extends('layouts.admin')

@section('title', 'Search Domains Results')

@section('styles')
    @vite(['resources/css/admin/styles.css'])
    @vite(['resources/css/admin/domains/index.css'])
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
    <div class="domains-container">
        <div class="domain-header">
            <h2 class="block-title">Search results for domains</h2>
        </div>
        <div class="search-query-badge">
            <span class="badge bg-light text-dark fs-6 p-2 border">
                <i class="bi bi-search me-2"></i>"{{ $query }}"
            </span>
        </div>
        <table id="manageDomainsTable" class="table table-hover table-striped table-spaced">
            <thead class="bg-light">
                <tr>
                    <th data-priority="1" class="text-center">Domain Name</th>
                    <th data-priority="2" class="text-center">Links Created</th>
                    <th class="d-none d-md-table-cell text-center">Total Clicks</th>
                    <th class="d-none d-sm-table-cell text-center">Created</th>
                    <th class="d-none d-sm-table-cell text-center">Status</th>
                    <th data-priority="4" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $domain)
                    <tr class="bg-white rounded shadow-sm">
                        <td class="align-middle text-left">
                            <i class="bi bi-globe me-2 text-primary"></i>
                            {{ $domain->name }}
                        </td>
                        <td class="align-middle text-center">
                            <span class="badge bg-primary rounded-pill fs-7 px-3 py-2">
                                {{ $domain->links_count }}
                            </span>
                        </td>
                        <td class="d-none d-md-table-cell text-center"><span
                                class="badge bg-primary rounded-pill fs-7 px-3 py-2">{{ $domain->total_clicks }}</span></td>
                        <td class="align-middle d-none d-sm-table-cell">
                            <span class="text-muted small">{{ $domain->created_at->format('d.m.Y') }}</span>
                        </td>
                        <td class="d-none d-sm-table-cell text-center">
                            @if ($domain->available)
                                <span class="badge rounded-pill badge-status badge-status-success text-white px-3 py-2 shadow-sm text-center">
                                    Active
                                </span>
                            @else
                                <span class="badge rounded-pill badge-status badge-status-danger text-white px-3 py-2 shadow-sm text-center">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <a href="{{  route('admin.search.links.byDomainId', $domain->id) }}"
                                class="btn btn-icon btn-sm btn-outline-primary me-1" title="View domain links">
                                <i class="bi bi-link-45deg"></i>
                            </a>

                            <button class="btn btn-icon btn-sm btn-outline-secondary edit-domain" data-bs-toggle="modal"
                                data-bs-target="#editDomainModal" data-domain-id="{{ $domain->id }}"
                                data-domain-name="{{ $domain->name }}" data-domain-status="{{ $domain->available }}"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <button class="btn btn-icon btn-sm btn-outline-danger delete-button" type="button"
                                data-domain-id="{{ $domain->id }}" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.domains.modals.add')
    @include('admin.domains.modals.edit')
    @include('admin.domains.modals.delete')

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
    @vite(['resources/js/admin/domains/index.js'])
@endsection
