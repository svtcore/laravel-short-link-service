@extends('layouts.app')

@section('title', 'Dashboard - URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/dashboard.css'])
@endsection

@section('content')
<!-- Hero Section (URL shortening block) -->
<section class="hero-section">
    <div class="container">
        <h1 class="display-4 mb-3">Welcome back, {{ $username }}</h1>
        <p class="lead mb-4">Shorten URLs quickly and manage your links.</p>

        <div class="input-group input-group-custom">
            @csrf
            <input type="url" class="form-control" data-route="{{ route('links.store') }}" name="url" placeholder="Enter URL to shorten" id="url-input" required title="Only links starts with http:// or https://">
            <button class="btn btn-light" id="shorten-btn">Shorten</button>
        </div>

        <div id="result-block" class="container mt-4" style="display: none;">
            <div class="result-container">
                <span id="shortened-link" class="result-link">https://short.url/example</span>
                <button class="copy-btn">Copy</button>
            </div>
        </div>
    </div>
</section>

<!-- User Dashboard Section -->
<section class="container user-dashboard">
    <h2 class="text-center mb-4">Your Dashboard</h2>

    @if ($links_count == 0)
    <!-- Empty Links Placeholder Block -->
    <div class="empty-links-placeholder text-center">
        <h4 class="mb-3">No Links Yet</h4>
        <p class="mb-4">It looks like you haven’t shortened any URLs yet. Start by entering a URL above to create your first shortened link!</p>
    </div>
    @else
    <!-- Statistics Cards -->
    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <h5>Total Links</h5>
                <p class="stat-value">{{ $links_count }}</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <h5>Total Clicks</h5>
                <p class="stat-value">{{ $clicks_count }}</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <h5>Total Unique Clicks</h5>
                <p class="stat-value">{{ $unique_clicks_count }}</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <h5>Clicks Today</h5>
                <p class="stat-value">{{ $links_today_count }}</p>
            </div>
        </div>
    </div>
    @if ($clicks_count == 0)
    <div class="empty-links-placeholder text-center">
        <p class="mb-4 mt-4">More detailed statistics will appear as the link gets more clicks.</p>
    </div>
    @endif

    @if (count($top_links) > 0)
    <!-- Graphs and Charts Section -->
    <div class="row g-4 mt-5">
        <!-- Top 5 Links Table -->
        <div class="col-md-6">
            <div class="chart-card">
                <h3 class="text-center mb-4">Top 5 Links</h3>
                <hr />
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Link</th>
                            <th scope="col" class="text-center">Clicks (Last 24h)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($top_links as $link)
                        <tr>
                            <th scope="row">{{ $loop->iteration }}</th>
                            <td class="text-truncate" style="max-width: 200px;"><a href="{{ $link['url'] }}" target="_blank">{{ $link['url'] }}</a></td>
                            <td class="text-center">{{ $link['click_count'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Hours Section -->
        <div class="col-md-6">
            <div class="chart-card">
                <h3 class="text-center mb-4">Activity Hours</h3>
                <hr />
                <textarea id="timeData" class="d-none">@json($hours_activity)</textarea>
                <canvas id="timeChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4 mt-4">
        <!-- Top 5 Countries Graph -->
        @if ($top_countries != "[]")
        <div class="col-md-4">
            <div class="chart-card">
                <h3 class="text-center mb-4">Top Countries</h3>
                <hr />
                <textarea id="countriesData" class="d-none">@json($top_countries)</textarea>
                <canvas id="countriesChart" height="300"></canvas>
            </div>
        </div>
        @endif

        @if ($top_browsers != "[]")
        <!-- User Agents Graph -->
        <div class="col-md-4">
            <div class="chart-card">
                <h3 class="text-center mb-4">Top Browsers</h3>
                <hr />
                <textarea id="browsersData" class="d-none">@json($top_browsers)</textarea>
                <canvas id="browsersChart" height="300"></canvas>
            </div>
        </div>
        @endif

        @if ($top_os != "[]")
        <!-- Operating Systems Graph -->
        <div class="col-md-4">
            <div class="chart-card">
                <h3 class="text-center mb-4">Top Operating Systems</h3>
                <hr />
                <textarea id="osData" class="d-none">@json($top_os)</textarea>
                <canvas id="osChart" height="200"></canvas>
            </div>
        </div>
        @endif
    </div>
    @endif
</section>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite(['resources/js/dashboard.js'])
@vite(['resources/js/home.js'])
@endsection