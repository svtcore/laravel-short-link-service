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
                                        <div class="statistics-item">
                                            <span class="statistics-label">IP Address:</span>
                                            <span class="statistics-value" id="statIPAddress"></span>
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