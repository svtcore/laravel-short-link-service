@extends('layouts.app')

@section('title', 'Dashboard - URL Shortening Service')

@section('styles')
    @vite(['resources/css/styles.css'])
    @vite(['resources/css/dashboard.css'])
@endsection

@section('content')
    <!-- Hero Section (URL shortening block) -->
    <section class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 mb-3">Welcome back, {{ $username }}</h1>
            <p class="lead mb-4">Shorten URLs quickly and manage your links.</p>

            <!-- Input group for URL shortening -->
            <div class="input-group input-group-custom">
                @csrf
                <input type="url" class="form-control" data-route="{{ route('links.store') }}" name="url"
                    placeholder="Enter URL to shorten" id="url-input" required maxlength="2048"
                    pattern="https?://.+|http://.+" title="Only links starts with http:// or https://">
                <button class="btn btn-primary" id="shorten-btn">Shorten</button>
            </div>

            <!-- Block to display the shortened URL -->
            <div id="result-block" class="container mt-4">
                <div class="result-container">
                    <span id="shortened-link" class="result-link">https://short.url/example</span>
                    <button class="copy-btn" id="copy-btn">Copy</button>
                </div>
            </div>
        </div>
    </section>

    <!-- User Dashboard Section -->
    <section class="container user-dashboard mt-4">
        @if ($links_count == 0)
            <!-- Empty Links Placeholder Block -->
            <div class="empty-links-placeholder shadow-lg bg-white rounded-3 p-4 text-center mb-4">
                <div class="empty-stats-icon mb-3">
                    <i class="bi bi-link-45deg fs-1 text-primary"></i>
                </div>
                <h4 class="mb-3">No Links Yet</h4>
                <p class="text-muted mb-0">
                    It looks like you haven't shortened any URLs yet. Start by entering a URL above to create your first
                    shortened link!
                    <br>
                    <small>Your new link will appear here immediately after creation</small>
                </p>
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
                <div class="empty-stats-card shadow-lg bg-white rounded-3 p-4 text-center mb-4">
                    <div class="empty-stats-icon mb-3">
                        <i class="bi bi-bar-chart fs-1 text-primary"></i>
                    </div>
                    <h4 class="mb-3">No Data Available Yet</h4>
                    <p class="text-muted mb-0">
                        Detailed statistics will appear here once your link starts receiving clicks.
                        <br>
                        <small>Share your link to generate traffic!</small>
                    </p>
                </div>
            @endif
            <!-- Graphs and Charts Section -->
            <div class="row g-4 mt-5">
                <!-- Top 5 Links Card -->
                <div class="col-md-6">
                    <div class="chart-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0">Top Links</h3>
                            <span class="badge bg-primary">Last 24h</span>
                        </div>
                        <hr class="mt-0">
                        <div class="top-links-list">
                            @if (count($top_links) > 0)
                                @foreach ($top_links as $link)
                                    <div class="top-link-item">
                                        <div class="link-rank">
                                            <span class="rank-badge">{{ $loop->iteration }}</span>
                                        </div>
                                        <div class="link-info">
                                            <a href="{{ $link['url'] }}" target="_blank" class="link-url">{{ $link['url'] }}</a>
                                            <div class="link-stats">
                                                <span class="click-count">{{ $link['click_count'] }}</span>
                                                <span class="click-label">clicks</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="no-data text-center">
                                    <p class="text-muted">No clicks detected for the last 24 hours.</p>
                                </div>
                            @endif
                        </div>
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