@extends('layouts.app')

@section('title', 'URL Shortening Service')

@section('styles')
@vite(['resources/css/styles.css'])
@vite(['resources/css/home.css'])
@endsection

@section('content')
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 mb-3">Welcome to the URL Shortening Service</h1>
        <p class="lead mb-4">Easily shorten links, share them, and track their statistics in just a few clicks.</p>

        <!-- Input group for URL shortening -->
        <div class="input-group input-group-custom">
            @csrf
            <input type="url" class="form-control" data-route="{{ route('links.store') }}" name="url" placeholder="Enter URL to shorten" id="url-input" required
                maxlength="2048" pattern="https?://.+|http://.+" title="Only links starts with http:// or https://">
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

<!-- Features Section -->
<section class="container my-5">
    <h2 class="text-center mb-4">Our Features</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <div class="card feature-card shadow-sm">
                <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Easy URL Shortening">
                <div class="card-body">
                    <h5 class="card-title">Easy URL Shortening</h5>
                    <p class="card-text">Paste your URL, and we will shorten it for you in just a few clicks.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card feature-card shadow-sm">
                <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Click Analytics">
                <div class="card-body">
                    <h5 class="card-title">Click Analytics</h5>
                    <p class="card-text">Get detailed insights on how many times your link was clicked and where it came from.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card feature-card shadow-sm">
                <img src="https://via.placeholder.com/400x250" class="card-img-top" alt="Simplicity and Speed">
                <div class="card-body">
                    <h5 class="card-title">Simplicity and Speed</h5>
                    <p class="card-text">Our service works quickly, no account required – just use it and enjoy.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
@vite(['resources/js/home.js'])
@endsection
