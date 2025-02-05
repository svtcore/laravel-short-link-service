@extends('layouts.admin')

@section('title', 'Domain Management - URL Shortening Service')

@section('styles')
@vite(['resources/css/admin/styles.css'])
@vite(['resources/css/admin/domains/index.css'])
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI=" crossorigin="anonymous">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.min.css" integrity="sha256-rKBEfnQsmpf8CmrOAohl7rVNfVUf+8mtA/8AKfXN7YA=" crossorigin="anonymous">
<!-- DataTables Responsive extension CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.min.css" integrity="sha256-LliUrn7vT0PjsdPWfsGcQdDPMI0faaUPWzVGQahZkCQ=" crossorigin="anonymous">
<!-- DataTables Buttons extension CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/3.2.0/css/buttons.bootstrap5.min.css" integrity="sha256-inPB99Z0Y2Ijp7YHBoE4K2W7CoUcM6YZ+aK4CgjrPY8=" crossorigin="anonymous">
@endsection

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; padding: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724;">
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
        <li style="background-color: #fecaca; border-radius: 8px; margin-bottom: 8px; padding: 10px; font-weight: 500; color: #7f1d1d;">
            {{ $error }}
        </li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif
<div class="domains-container">
    <div class="domain-header">
        <h2 class="block-title">Manage Domains</h2>
        <button class="add-domain-btn" data-bs-toggle="modal" data-bs-target="#domainModal">
            <i class="bi bi-plus-circle"></i> Add Domain
        </button>
    </div>
    <table id="manageDomainsTable" class="table table-hover table-striped" style="border-collapse: separate; border-spacing: 0 1rem;">
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
            @foreach($domains as $domain)
            <tr class="bg-white rounded shadow-sm">
                <td class="align-middle text-left">
                    <i class="bi bi-globe me-2 text-primary"></i>
                    {{ $domain->name }}
                </td>
                <td class="align-middle text-center">{{ $domain->links_count }}</td>
                <td class="d-none d-md-table-cell text-center">{{ $domain->total_link_histories }}</td>
                <td class="align-middle d-none d-sm-table-cell">
                    <span class="text-muted small">{{ $domain->created_at->format('d.m.Y') }}</span>
                </td>
                <td class="d-none d-sm-table-cell text-center">
                    @if ($domain->available)
                    <span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm text-center" style="font-size: 0.875rem; transition: all 0.3s ease;">
                        Active
                    </span>
                    @else
                    <span class="badge rounded-pill bg-danger text-white px-3 py-2 shadow-sm text-center" style="font-size: 0.875rem; transition: all 0.3s ease;">
                        Inactive
                    </span>
                    @endif
                </td>
                <td class="align-middle">
                    <button class="btn btn-icon btn-sm btn-outline-secondary edit-domain"
                        data-bs-toggle="modal"
                        data-bs-target="#editDomainModal"
                        data-domain-id="{{ $domain->id }}"
                        data-domain-name="{{ $domain->name }}"
                        data-domain-status="{{ $domain->available }}"
                        title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button class="btn btn-icon btn-sm btn-outline-danger delete-button"
                        type="button"
                        data-domain-id="{{ $domain->id }}"
                        title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- Add Domain Modal -->
<div class="modal fade" id="domainModal" tabindex="-1" aria-labelledby="domainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="domainModalLabel">Add New Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addDomainForm" action="{{ route('admin.domains.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="domainName" class="form-label">Domain Name</label>
                        <input type="text" class="form-control" id="domainName" name="domainName" required>
                    </div>
                    <div class="mb-3">
                        <label for="domainStatus" class="form-label">Status</label>
                        <select class="form-select" id="domainStatus" name="domainStatus">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-domain-submit-btn">Add Domain</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Domain Modal -->
<div class="modal fade" id="editDomainModal" tabindex="-1" aria-labelledby="editDomainModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDomainModalLabel">Edit Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDomainForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editDomainId" name="id">
                    <div class="mb-3">
                        <label for="editDomainName" class="form-label">Domain Name</label>
                        <input type="text" class="form-control" id="editDomainName" name="domainName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editDomainStatus" class="form-label">Status</label>
                        <select class="form-select" id="editDomainStatus" name="domainStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="modal-footer modal-custom-footer">
                        <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-domain-submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Deleting the domain will remove <b>ALL</b></p>
                <ul>
                    <li><b>Links</b></li>
                    <li><b>Links history</b></li>
                </ul>
                <p>Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary modal-domain-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteDomainForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger modal-domain-delete-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts')
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js" integrity="sha256-aTBve1VKWozmjo9Nb2E73RvP4t8xOWLn/IPPX2vl4IU=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js" integrity="sha256-U3zt3BGRnSBU8GxdNWp9CGmrMKBUthlz2ia7LERbiNc=" crossorigin="anonymous"></script>

<!-- Bootstrap JS (required for DataTables with Bootstrap) -->
<script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js" integrity="sha256-2SmX/IhbmVdeg5paUsa6SExqBi0iF2LF+Dh7tG/Uv7s=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.bootstrap5.min.js" integrity="sha256-dU1f0caF6eBMRglcDQ2xQ0oLhXNConpBNc2JSFeINL8=" crossorigin="anonymous"></script>

<!-- Responsive extension for DataTables -->
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js" integrity="sha256-fgKMuTMhdhI9JaA6XdUSrgkOY41nWaeN5eH1ctK4yog=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js" integrity="sha256-Ml0aZ+BzTKuF3cSJIyCElyJjEVFJjqoFucYGL6/hsmI=" crossorigin="anonymous"></script>
@vite(['resources/js/admin/domains/index.js'])
@endsection