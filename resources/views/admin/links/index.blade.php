@extends('layouts.admin')

@section('title', 'Link Management - URL Shortening Service')

@section('styles')
@vite(['resources/css/admin/styles.css'])
@vite(['resources/css/admin/links/index.css'])
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

<div class="links-container">
    <div class="link-header">
        <h2 class="block-title">Manage Links</h2>
        <button class="add-link-btn" data-bs-toggle="modal" data-bs-target="#linkModal">
            <i class="bi bi-plus-circle"></i> Add Link
        </button>
    </div>
    <table id="manageLinksTable" class="table table-hover" style="border-collapse: separate; border-spacing: 0 1rem;">
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
                    {{ $link->user->email }}
                    @else
                    {{ $link->ip_address }}
                    @endif
                </td>

                <!-- Short URL Column -->
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <a href="https://{{ $link->domain->name }}/{{ $link->short_name }}" class="text-truncate text-primary" style="max-width: 180px;" target="_blank">
                            {{ $link->domain->name }}/{{ $link->short_name }}
                        </a>
                        <button class="btn btn-link btn-sm text-secondary copy-button p-0" data-link="https://{{ $link->domain->name }}/{{ $link->short_name }}" title="Copy Short URL">
                            <i class="bi bi-clipboard fs-6"></i>
                        </button>
                    </div>
                </td>

                <!-- Clicks Column -->
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">
                            {{ $link->link_histories_count }}
                        </span>
                        <small class="text-muted d-none d-sm-inline fs-6">
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
                    <span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm text-center" style="font-size: 0.875rem; transition: all 0.3s ease;">
                        Active
                    </span>
                    @else
                    <span class="badge rounded-pill bg-danger text-white px-3 py-2 shadow-sm text-center" style="font-size: 0.875rem; transition: all 0.3s ease;">
                        Inactive
                    </span>
                    @endif
                </td>

                <!-- Actions Column -->
                <td class="align-middle">
                    <div class="d-flex gap-2">
                        <button class="btn btn-icon btn-sm btn-outline-secondary view-stats" data-bs-toggle="modal" data-bs-target="#fullScreenStatsModal" data-id="{{ $link->id }}" title="Statistics">
                            <i class="bi bi-graph-up"></i>
                        </button>
                        <button class="btn btn-icon btn-sm btn-outline-secondary edit-link" data-bs-toggle="modal" data-bs-target="#editLinkModal" data-id="{{ $link->id }}" data-name="{{ $link->custom_name }}" data-url="{{ $link->destination }}" data-status="{{ $link->available }}" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('admin.links.destroy', $link->id) }}" method="POST" id="delete-form-{{ $link->id }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-icon btn-sm btn-outline-danger delete-button" type="button" data-id="{{ $link->id }}" title="Delete">
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

<!-- Add Link Modal -->
<div class="modal fade" id="linkModal" tabindex="-1" aria-labelledby="linkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkModalLabel">Add New Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLinkForm" action="{{ route('admin.links.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="mb-3">
                        <label for="custom_name" class="form-label">Custom name</label>
                        <input type="text" class="form-control" id="custom_name" name="custom_name" minlength="3" maxlength="50">
                        <div class="invalid-feedback">Custom name must be between 3 and 50 characters, using only letters, numbers, and dashes.</div>
                    </div>

                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required minlength="5" maxlength="2048" required>
                        <div class="invalid-feedback">Please enter a valid URL (5 to 2048 characters).</div>
                    </div>

                    <div class="mb-3">
                        <label for="user_email" class="form-label">Assign to user</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" placeholder="Enter email" maxlength="255">
                        <div class="form-text text-muted">Leave empty to assign to the current administrator.</div>
                        <div class="invalid-feedback">Please enter a valid email address (max 255 characters).</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-link-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-link-submit-btn">Shorten Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Link Modal -->
<div class="modal fade" id="editLinkModal" tabindex="-1" aria-labelledby="editLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLinkModalLabel">Edit Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLinkForm" action="" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="editLinkId" name="editLinkId">

                    <div class="mb-3">
                        <label for="edit_custom_name" class="form-label">Custom name</label>
                        <input type="text" class="form-control" id="editCustomName" name="editCustomName" minlength="3" maxlength="50">
                        <div class="invalid-feedback">Custom name must be between 3 and 50 characters, using only letters, numbers, and dashes.</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="editURL" name="editURL" required minlength="5" maxlength="2048">
                        <div class="invalid-feedback">Please enter a valid URL (5 to 2048 characters).</div>
                    </div>
                    <div class="mb-3">
                        <label for="editStatus" class="form-label">Status</label>
                        <select class="form-select" id="editStatus" name="editStatus">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary modal-link-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="modal-link-submit-btn">Update Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Fullscreen Statistics Modal -->
<div class="modal fade" id="fullScreenStatsModal" tabindex="-1" aria-labelledby="fullScreenStatsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content shadow-sm rounded-0">
            <div class="modal-header border-bottom">
                <h5 class="modal-title" id="statsModalLabel">Statistics for</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <!-- Date Filters -->
                    <div class="row mb-4 date-filters">
                        <input type="hidden" id="selected-link" data-id="" />
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="startDate" class="form-label">From Date:</label>
                                <div class="input-group date-input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                    <input type="date" id="startDate" class="form-control form-control-lg"
                                        placeholder="Select start date" required
                                        max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="endDate" class="form-label">To Date:</label>
                                <div class="input-group date-input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-calendar"></i>
                                    </span>
                                    <input type="date" id="endDate" class="form-control form-control-lg"
                                        placeholder="Select end date" required
                                        max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-4" />

                    <!-- Statistics Block -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="chart-card">
                                <h3>Detailed Statistics</h3>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="statistics-item d-flex justify-content-between align-items-center">
                                            <span class="statistics-label">URL:</span>
                                            <div class="d-flex align-items-center">
                                                <span class="statistics-value text-truncate" id="statDestination"></span>
                                                <button class="btn btn-link btn-sm text-secondary copy-button p-0" id="statDestinationCopyBtn" title="Copy Short URL">
                                                    <i class="bi bi-clipboard fs-6"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="statistics-item d-flex justify-content-between align-items-center">
                                            <span class="statistics-label">Short URL:</span>
                                            <div class="d-flex align-items-center">
                                                <span class="statistics-value" id="statShortURL"></span>
                                                <button class="btn btn-link btn-sm text-secondary copy-button p-0" id="statShortURLCopyBtn" title="Copy Short URL">
                                                    <i class="bi bi-clipboard fs-6"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="statistics-item">
                                            <span class="statistics-label">Creation date:</span>
                                            <span class="statistics-value" id="statCreatedAt"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="statistics-item">
                                            <span class="statistics-label">Total Clicks:</span>
                                            <span class="statistics-value" id="statTotalClicks"></span>
                                        </div>
                                        <div class="statistics-item">
                                            <span class="statistics-label">Unique Visitors:</span>
                                            <span class="statistics-value" id="statTotalUniqueClicks"></span>
                                        </div>
                                        <div class="statistics-item">
                                            <span class="statistics-label">Status:</span>
                                            <span class="statistics-value" id="statStatus"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="statistics-item">
                                            <span class="statistics-label">Top country:</span>
                                            <span class="statistics-value" id="statTopCountry"></span>
                                        </div>
                                        <div class="statistics-item">
                                            <span class="statistics-label">Top Browser:</span>
                                            <span class="statistics-value" id="statTopBrowser"></span>
                                        </div>
                                        <div class="statistics-item">
                                            <span class="statistics-label">Top Operation System:</span>
                                            <span class="statistics-value" id="statTopOS"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Activity Block -->

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="chart-card">
                                <h3>Click Activity Over Days</h3>
                                <hr>
                                <div class="chart-wrapper">
                                    <canvas id="activityDaysChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-card">
                                <h3>Click Activity Over Time</h3>
                                <hr>
                                <div class="chart-wrapper">
                                    <canvas id="activityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="mb-4" />

                    <!-- Distribution Blocks -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="chart-card">
                                <h3>Country Distribution</h3>
                                <hr>
                                <div class="chart-wrapper">
                                    <canvas id="countryChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-card">
                                <h3>Device Distribution</h3>
                                <hr>
                                <div class="chart-wrapper">
                                    <canvas id="deviceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="mb-4" />
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-card">
                                <h3>Browser Distribution</h3>
                                <hr>
                                <div class="chart-wrapper">
                                    <canvas id="browserChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                <p>Deleting the link will remove all related data (e.g. click statistics, history, etc.).</p>
                <p>Are you sure you want to proceed?</p>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary modal-link-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="modal-link-delete-btn" id="confirmDeleteButton">Delete</button>
                </div>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@vite(['resources/js/admin/links/index.js'])
@endsection