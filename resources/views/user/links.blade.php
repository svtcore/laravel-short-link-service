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
    <div class="links-block">
        <h2 class="text-center mb-4 mt-1">Detailed Link Statistics</h2>
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
        <table id="linksTable" class="table table-striped w-100">
            <thead>
                <tr>
                    <th class="text-center">Name</th>
                    <th class="text-center align-middle w-25">Short Link</th>
                    <th class="text-center align-middle w-25">Source Link</th>
                    <th class="text-center">Clicks</th>
                    <th class="text-center">Unique</th>
                    <th class="text-center">Date</th>
                    <th class="text-center">Statistics</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($links as $link)
                <tr>
                    <td class="text-center align-middle w-25 text-truncate" style="max-width: 150px;" title="{{ $link->custom_name }}">
                        {{ $link->custom_name }}
                    </td>

                    <!-- Short Link Display -->
                    <td class="text-center align-middle w-25">
                        @isset($link->short_name)
                        <div class="d-flex align-items-center justify-content-start border rounded p-2 bg-light text-truncate">
                            <a href="https://{{ $link->domain->name }}/{{ $link->short_name }}"
                                class="text-primary fw-bold text-decoration-none"
                                target="_blank"
                                title="https://{{ $link->domain->name }}/{{ $link->short_name }}">
                                {{ $link->domain->name }}/{{ $link->short_name }}
                            </a>
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm ms-auto copy-button"
                                title="Copy Link"
                                data-link="https://{{ $link->domain->name }}/{{ $link->short_name }}">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                        @endisset
                    </td>

                    <td class="text-center align-middle w-25">
                        <div class="d-flex align-items-center border rounded p-2 bg-light" style="max-width: 250px;">
                            <!-- Container link -->
                            <div class="text-truncate" style="flex-grow: 1; overflow: hidden;">
                                <a href="{{ $link->destination }}"
                                    class="text-dark text-decoration-none"
                                    target="_blank"
                                    title="{{ $link->destination }}">
                                    {{ $link->destination }}
                                </a>
                            </div>
                            <!-- Copy button -->
                            <button type="button"
                                class="btn btn-outline-secondary btn-sm ms-2 flex-shrink-0 copy-button"
                                title="Copy Link"
                                data-link="{{ $link->destination }}">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </td>


                    <td class="text-center align-middle">{{ $link->total_clicks }}</td>
                    <td class="text-center align-middle">{{ $link->unique_clicks }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($link->created_at)->format('d.m.Y') }}</td>
                    <td class="text-center">
                        @if ($link->total_clicks > 0)
                        @csrf
                        <button class="btn btn-custom btn-sm view-stats" data-bs-toggle="modal" data-bs-target="#statsModal" data-id="{{ $link->id }}" data-url="{{ route('user.links.show') }}">View</button>
                        @else
                        <p class="text-center">No data</p>
                        @endif
                    </td>
                    <td class="text-center">
                        <form action="{{ route('user.links.destroy', $link->id) }}" method="POST" id="delete-form-{{ $link->id }}">
                            @csrf
                            @method('DELETE')
                            <div class="btn-group mr-2" role="group" aria-label="First group">
                                <a href="#" type="button" class="btn btn-warning edit-link" data-bs-toggle="modal" data-bs-target="#editLinkModal" data-id="{{ $link->id }}" data-url="{{ route('user.links.edit', $link->id) }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-danger delete-button" data-id="{{ $link->id }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </form>
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
                    <div class="d-flex justify-content-between mb-3">
                        <input type="hidden" id="selected-link" data-id="" />
                        <div class="input-group input-group-custom w-50">
                            <label class="mt-1">From Date:</label>
                            <input type="date" id="startDate" class="form-control" placeholder="Select start date" required max="{{ now()->toDateString() }}">
                        </div>
                        <div class="input-group input-group-custom w-50">
                            <label class="mt-1">To Date:</label>
                            <input type="date" id="endDate" class="form-control" placeholder="Select end date" required max="{{ now()->toDateString() }}">
                        </div>
                    </div>
                    <hr class="mb-4" />

                    <!-- Activity Block -->
                    <div class="row mb-4">
                        <h5 class="text-center">Click Activity Over Days</h5>
                        <div class="d-flex align-items-start">
                            <div style="flex: 1;">
                                <canvas id="activityDaysChart" style="max-width: 100%; max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <h5 class="text-center">Click Activity Over Time</h5>
                        <div class="d-flex align-items-start">
                            <div style="flex: 1;">
                                <canvas id="activityChart" style="max-width: 100%; max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-center">Country Distribution</h5>
                            <div class="chart-container">
                                <canvas id="countryChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center">Device Distribution</h5>
                            <div class="chart-container">
                                <canvas id="deviceChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <hr />
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-center">Browser Distribution</h5>
                            <div class="chart-container">
                                <canvas id="browserChart"></canvas>
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
                            <button type="button" class="btn btn-outline-secondary btn-sm ms-2" title="Copy Link" onclick="navigator.clipboard.writeText(document.getElementById('editShortNameDisplay').textContent);">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="editCustomName" class="form-label">Custom Name</label>
                        <input type="text" class="form-control" id="editCustomName" name="custom_name" placeholder="Enter link name" minlength="3" maxlength="255" title="Only letters, numbers, spaces, and hyphens are allowed">
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