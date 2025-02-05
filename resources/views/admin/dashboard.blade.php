@extends('layouts.admin')

@section('title', 'Admin Dashboard - URL Shortening Service')

@section('styles')
@vite(['resources/css/admin/styles.css'])
@vite(['resources/css/admin/dashboard.css'])
@endsection

@section('content')


<!-- General Statistics Block -->
<div class="general-statistics-container">
    <h2 class="general-statistics-title">General Statistics</h2>
    <div class="stats-container">
        <div class="stat-card">
            <h3>Total links</h3>
            <p id="totalLinks">{{ $total_links }}</p>
        </div>
        <div class="stat-card">
            <h3>Active links</h3>
            <p id="activeLinks">{{ $total_active_links }}</p>
        </div>
        <div class="stat-card">
            <h3>Users</h3>
            <p id="newUsersToday">{{ $total_users }}</p>
        </div>
        <div class="stat-card">
            <h3>Clicks</h3>
            <p id="totalClicks">{{ $total_clicks }}</p>
        </div>
        <div class="stat-card">
            <h3>Unique clicks</h3>
            <p id="uniqueClicks">{{ $total_unique_clicks }}</p>
        </div>
        <div class="stat-card">
            <h3>Avg clicks per link</h3>
            <p id="avgClicksPerLink">{{ $total_avg_clicks }}</p>
        </div>
    </div>
</div>

@php
use Carbon\Carbon;

$startDate = Carbon::now()->subDays(1)->toDateString();
$endDate = Carbon::now()->toDateString();
@endphp

<!-- Date Filter Block -->
<div class="date-filter-container">
    <h2 class="date-filter-title">Date Filter</h2>
    <form id="dateFilterForm" class="date-filter-form">
        <input type="hidden" id="chart-update-route" data-route="{{ route('admin.dashboard.show') }}" />
        @csrf
        <div class="date-input-group">
            <label for="startDate" class="date-label">Start Date:</label>
            <input type="date" id="startDate" name="startDate" class="date-input" value="{{ $startDate }}" required>
        </div>
        <div class="date-input-group">
            <label for="endDate" class="date-label">End Date:</label>
            <input type="date" id="endDate" name="endDate" class="date-input" value="{{ $endDate }}" required>
        </div>
    </form>
</div>

<div id="loading-placeholder" class="loading-placeholder">
    <div class="spinner"></div>
    <p>Loading detailed statistics. It might take a few minutes.</p>
</div>

<div id="detailed-stats-container">
    <div class="stats-date-container">
        <div class="stat-card">
            <h3>Created links</h3>
            <p id="totalLinksByDate"></p>
        </div>
        <div class="stat-card">
            <h3>Clicks</h3>
            <p id="totalClicksByDate"></p>
        </div>
        <div class="stat-card">
            <h3>Unique clicks</h3>
            <p id="totalUniqueClicksByDate"></p>
        </div>
        <div class="stat-card">
            <h3>New users</h3>
            <p id="totalUsersByDate"></p>
        </div>
    </div>


    <div class="charts-container">
        <div class="full-width-chart chart-last-container">
            <h3 class="chart-block-title">Click Activity Over Days</h3>
            <hr>
            <textarea id="chartDaysActivityData" hidden></textarea>
            <canvas id="activityDaysChart" style="max-height:300px;"></canvas>
        </div>

        <div class="full-width-chart chart-last-container">
            <h3 class="chart-block-title">Click Activity Over Time</h3>
            <hr>
            <textarea id="chartTimeActivityData" hidden></textarea>
            <canvas id="activityTimeChart" style="max-height:300px;"></canvas>
        </div>
    </div>
    <div class="chart-last-container-wrapper">
        <div class="chart-last-container">
            <h3 class="chart-block-title">Countries</h3>
            <hr>
            <textarea id="chartGeoData" hidden></textarea>
            <canvas id="geoChart" style="height:100% !important;"></canvas>
        </div>
        <div class="chart-last-container">
            <h3 class="chart-block-title">Platform</h3>
            <hr>
            <textarea id="chartPlatformData" hidden></textarea>
            <canvas id="platformChart"></canvas>
        </div>
        <div class="chart-last-container">
            <h3 class="chart-block-title">Browsers</h3>
            <hr>
            <textarea id="chartBrowserData" hidden></textarea>
            <canvas id="browserChart"></canvas>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite(['resources/js/admin/dashboard.js'])
@endsection