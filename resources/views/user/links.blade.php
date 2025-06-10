@extends('layouts.app')
@section('title', 'My Links - URL Shortening Service')
@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/links.css'])
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
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-3">Link Statistics</h1>
        <p class="lead mb-4">Analyze your shortened links and track detailed metrics.</p>
    </div>
</section>

<!-- Statistics Section -->
<section class="statistics-section">
    <div class="links-block shadow-lg bg-white rounded-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Your Shortened Links</h2>
            <div class="d-flex gap-2">
                <button class="btn btn-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createLinkModal">
                    <i class="bi bi-plus-lg me-1"></i>New Link
                </button>
            </div>
        </div>
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul>
                @foreach($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <table id="linksTable" class="table table-hover links-table">
            <thead class="bg-light">
                <tr>
                    <th data-priority="1">Name</th>
                    <th data-priority="2">Short URL</th>
                    <th class="d-none d-md-table-cell">Destination</th>
                    <th data-priority="3">Clicks</th>
                    <th class="d-none d-sm-table-cell">Status</th>
                    <th class="d-none d-sm-table-cell">Created</th>
                    <th data-priority="4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($links as $link)
                <tr class="bg-white rounded shadow-sm">
                    <!-- Name Column -->
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-link-45deg me-2 text-primary"></i>
                            <span class="text-truncate" title="{{ $link->custom_name }}">
                                {{ $link->custom_name }}
                            </span>
                        </div>
                    </td>

                    <!-- Short URL Column -->
                    <td class="align-middle">
                        <div class="d-flex align-items-center gap-2">
                            <a href="https://{{ $link->domain->name }}/{{ $link->short_name }}"
                                class="text-truncate text-primary" target="_blank">
                                {{ $link->domain->name }}/{{ $link->short_name }}
                            </a>
                            <button class="btn btn-link btn-sm text-secondary copy-button p-0"
                                data-link="https://{{ $link->domain->name }}/{{ $link->short_name }}"
                                title="Copy Short URL">
                                <i class="bi bi-clipboard fs-6"></i>
                            </button>
                        </div>
                    </td>

                    <!-- Destination Column (hidden on mobile) -->
                    <td class="align-middle d-none d-md-table-cell">
                        <div class="d-flex align-items-center gap-2">
                            <div class="destination-container">
                                <span class="destination-url text-truncate d-block" title="{{ $link->destination }}">
                                    {{ parse_url($link->destination, PHP_URL_HOST) }}<span class="destination-path">{{ parse_url($link->destination, PHP_URL_PATH) }}</span>
                                </span>
                            </div>
                            <button class="btn btn-link btn-sm text-secondary copy-button p-0"
                                data-link="{{ $link->destination }}"
                                title="Copy Destination URL">
                                <i class="bi bi-clipboard fs-6"></i>
                            </button>
                        </div>
                    </td>

                    <!-- Clicks Column -->
                    <td class="align-middle">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill fs-6 px-3 py-2">
                                {{ $link->total_clicks }}
                            </span>
                            <small class="text-muted d-none d-sm-inline fs-6">
                                / {{ $link->unique_clicks }}
                            </small>
                        </div>
                    </td>

                    <!-- Status -->
                    <td class="align-middle d-none d-sm-table-cell">
                        @if ($link->available)
                        <span class="badge rounded-pill bg-success text-white px-3 py-2 shadow-sm status-badge">
                            Active
                        </span>
                        @else
                        <span class="badge rounded-pill bg-danger text-white px-3 py-2 shadow-sm status-badge">
                            Inactive
                        </span>
                        @endif
                    </td>

                    <!-- Date Column (hidden on mobile) -->
                    <td class="align-middle d-none d-sm-table-cell">
                        <span class="text-muted small">
                            {{ \Carbon\Carbon::parse($link->created_at)->format('M d, Y') }}
                        </span>
                    </td>

                    <td class="align-middle">
                        <div class="d-flex gap-2">
                            @if ($link->total_clicks > 0)
                            <button class="btn btn-icon btn-md btn-outline-secondary view-stats"
                                data-bs-toggle="modal"
                                data-bs-target="#statsModal"
                                data-id="{{ $link->id }}"
                                data-url="{{ route('user.links.show') }}"
                                title="Statistics">
                                <i class="bi bi-graph-up"></i>
                            </button>
                            @endif

                            <button class="btn btn-icon btn-md btn-outline-secondary edit-link"
                                data-bs-toggle="modal"
                                data-bs-target="#editLinkModal"
                                data-id="{{ $link->id }}"
                                data-url="{{ route('user.links.edit', $link->id) }}"
                                title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>

                            <form action="{{ route('user.links.destroy', $link->id) }}" method="POST" id="delete-form-{{ $link->id }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-icon btn-md btn-outline-danger delete-button"
                                    type="button"
                                    data-id="{{ $link->id }}"
                                    title="Delete">
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
</section>


<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure want delete selected item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-custom" id="confirm-delete-button">Delete</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statsModal" tabindex="-1" aria-labelledby="statsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
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

<!-- Modal for Editing Links -->
<div class="modal fade" id="editLinkModal" tabindex="-1" aria-labelledby="editLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" id="editLinkForm">
                @csrf
                <input type="hidden" id="editLinkId" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editLinkModalLabel">Edit Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="editLinkId" name="id">
                    <div id="editLinkErrors" class="alert alert-danger d-none">
                        <ul class="mb-0" id="editLinkErrorsList"></ul>
                    </div>
                    <!-- Short Link Display -->
                    <div class="mb-4">
                        <h6 class="form-label">Short Link</h6>
                        <div class="d-flex align-items-center border rounded p-2 bg-light">
                            <p class="mb-0 text-primary fw-bold" id="editShortNameDisplay" style="flex: 1;"></p>
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2 copy-button" id="editLinkModalCopyButton" title="Copy Link">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editCustomName" class="form-label">Custom Name</label>
                        <input type="text" class="form-control" id="editCustomName" name="custom_name" placeholder="Enter link name" minlength="3" maxlength="255" pattern="^[a-zA-Z0-9_\- ]+$" title="Only letters, numbers, spaces, hyphens and underscores are allowed">
                    </div>

                    <div class="mb-3">
                        <label for="editSource" class="form-label">Source Link</label>
                        <input type="url" class="form-control" id="editSource" name="destination" placeholder="Enter source link" required title="Only links starts with http:// or https://">
                    </div>

                    <div class="mb-3">
                        <label for="editAccess" class="form-label">Enabled</label>
                        <select class="form-select" id="editAvailable" name="access" required>
                            <option value="1" selected>Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Create Link Modal -->
<div class="modal fade create-link-modal" id="createLinkModal" tabindex="-1" aria-labelledby="createLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('links.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createLinkModalLabel">Shorten New Link</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div id="createLinkErrors" class="alert alert-danger d-none">
                        <ul class="mb-0" id="createLinkErrorsList"></ul>
                    </div>

                    <!-- Custom Name Field -->
                    <div class="mb-4">
                        <label for="custom_name" class="form-label">Custom Name</label>
                            <input type="text"
                                class="form-control"
                                id="custom_name"
                                name="custom_name"
                                placeholder="My Special Link"
                                minlength="3"
                                maxlength="255"
                                pattern="^[a-zA-Z0-9_\- ]+$"
                                title="Only letters, numbers, spaces, hyphens and underscores are allowed">
                    </div>

                    <!-- Destination URL Field -->
                    <div class="mb-4">
                        <label for="destination" class="form-label">Destination URL</label>
                        <input type="url"
                            class="form-control"
                            id="url"
                            name="url"
                            placeholder="https://example.com"
                            title="Only links starting with http:// or https://"
                            required>
                    </div>
                    <input type="hidden" name="from_modal" value="1" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-custom">Shorten Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-annotation"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js" integrity="sha256-aTBve1VKWozmjo9Nb2E73RvP4t8xOWLn/IPPX2vl4IU=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js" integrity="sha256-U3zt3BGRnSBU8GxdNWp9CGmrMKBUthlz2ia7LERbiNc=" crossorigin="anonymous"></script>

<!-- Bootstrap JS (required for DataTables with Bootstrap) -->
<script src="https://cdn.datatables.net/buttons/3.2.0/js/dataTables.buttons.min.js" integrity="sha256-2SmX/IhbmVdeg5paUsa6SExqBi0iF2LF+Dh7tG/Uv7s=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/buttons/3.2.0/js/buttons.bootstrap5.min.js" integrity="sha256-dU1f0caF6eBMRglcDQ2xQ0oLhXNConpBNc2JSFeINL8=" crossorigin="anonymous"></script>

<!-- Responsive extension for DataTables -->
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js" integrity="sha256-fgKMuTMhdhI9JaA6XdUSrgkOY41nWaeN5eH1ctK4yog=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.min.js" integrity="sha256-Ml0aZ+BzTKuF3cSJIyCElyJjEVFJjqoFucYGL6/hsmI=" crossorigin="anonymous"></script>
@vite(['resources/js/links.js'])
@endsection
