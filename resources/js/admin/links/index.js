/**
 * Admin Links Management Module
 * 
 * Handles link management functionality in admin panel including:
 * - Links DataTable initialization
 * - Link statistics visualization (charts and metrics)
 * - Link editing and deletion
 * - Detailed analytics for each link
 * 
 * Features:
 * - Interactive charts for click activity (daily/hourly)
 * - Geographic and device breakdowns
 * - Bulk operations
 * - Clipboard functionality
 * - Date range filtering
 * 
 * Dependencies:
 * - jQuery
 * - DataTables
 * - Chart.js
 * - Bootstrap modals
 */
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = "#6c757d";
Chart.defaults.borderColor = "rgba(0, 0, 0, 0.05)";

$(document).ready(function () {
    $("#manageLinksTable").DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: true,
        searching: true,
        paging: true,
        ordering: true,
        info: false,
        order: [[3, "desc"]],
    });
});

const CHART_COLORS = {
    primary: "rgba(0, 123, 255, 0.8)",
    secondary: "rgba(100, 181, 246, 0.8)",
    warning: "rgba(247, 184, 48, 0.8)",
    success: "rgba(40, 167, 69, 0.8)",
    danger: "rgba(220, 53, 69, 0.8)",
    gradientFill: "rgba(100, 181, 246, 0.15)",
};

const commonChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "top",
            labels: {
                padding: 15,
                boxWidth: 15,
                font: { size: 13 },
            },
        },
        tooltip: {
            backgroundColor: "rgba(255, 255, 255, 0.95)",
            titleColor: "#212529",
            bodyColor: "#6c757d",
            borderColor: "rgba(0, 0, 0, 0.1)",
            borderWidth: 1,
            padding: 12,
            boxShadow: "0 8px 24px rgba(0, 0, 0, 0.05)",
            usePointStyle: true,
        },
    },
};

const activityDaysChartConfig = {
    type: "line",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks per day",
                data: [],
                borderColor: CHART_COLORS.primary,
                backgroundColor: CHART_COLORS.gradientFill,
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: "#fff",
                pointBorderColor: CHART_COLORS.primary,
                tension: 0.4,
                fill: { target: "origin", above: CHART_COLORS.gradientFill },
            },
        ],
    },
    options: {
        ...commonChartOptions,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: "Clicks" },
                ticks: { stepSize: 1 },
            },
            x: {
                title: { display: true, text: "Date" },
                ticks: { autoSkip: true, maxTicksLimit: 12 },
            },
        },
    },
};

const activityChartConfig = {
    type: "line",
    data: {
        labels: Array.from(
            { length: 24 },
            (_, i) => `${String(i).padStart(2, "0")}:00`
        ),
        datasets: [
            {
                label: "Clicks per hour",
                data: Array(24).fill(0),
                borderColor: CHART_COLORS.primary,
                backgroundColor: CHART_COLORS.gradientFill,
                borderWidth: 3,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: "#fff",
                pointBorderColor: CHART_COLORS.primary,
                tension: 0.4,
                fill: { target: "origin", above: CHART_COLORS.gradientFill },
            },
        ],
    },
    options: {
        ...commonChartOptions,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: (value) => (value % 1 === 0 ? value : null),
                },
            },
            x: { title: { display: true, text: "Time (hours)" } },
        },
    },
};

const countryChartConfig = {
    type: "bar",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks",
                data: [],
                backgroundColor: Object.values(CHART_COLORS),
                borderColor: "rgba(255, 255, 255, 0.3)",
                borderWidth: 1,
                borderRadius: 8,
                borderSkipped: false,
                maxBarThickness: 40,
            },
        ],
    },
    options: {
        ...commonChartOptions,
        indexAxis: "y",
        plugins: { legend: { display: false } },
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 } },
            y: { grid: { display: false } },
        },
    },
};

const deviceChartConfig = {
    type: "doughnut",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks",
                data: [],
                backgroundColor: Object.values(CHART_COLORS),
                borderColor: "rgba(255, 255, 255, 0.3)",
                borderWidth: 2,
                spacing: 5,
                hoverOffset: 15,
            },
        ],
    },
    options: {
        ...commonChartOptions,
        cutout: "60%",
        plugins: { legend: { position: "right" } },
    },
};

const browserChartConfig = {
    type: "pie",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks",
                data: [],
                backgroundColor: Object.values(CHART_COLORS),
                borderColor: "rgba(255, 255, 255, 0.3)",
                borderWidth: 2,
                spacing: 5,
                hoverOffset: 15,
            },
        ],
    },
    options: {
        ...commonChartOptions,
        plugins: { legend: { position: "right" } },
    },
};

//init charts
const charts = {
    activityDaysChart: new Chart(
        document.getElementById("activityDaysChart").getContext("2d"),
        activityDaysChartConfig
    ),
    activityChart: new Chart(
        document.getElementById("activityChart").getContext("2d"),
        activityChartConfig
    ),
    countryChart: new Chart(
        document.getElementById("countryChart").getContext("2d"),
        countryChartConfig
    ),
    deviceChart: new Chart(
        document.getElementById("deviceChart").getContext("2d"),
        deviceChartConfig
    ),
    browserChart: new Chart(
        document.getElementById("browserChart").getContext("2d"),
        browserChartConfig
    ),
};

function updateActivityChart(activeHours) {
    const nonZeroData = activeHours.filter((val) => val > 0);
    const minValue = nonZeroData.length ? Math.min(...nonZeroData) - 1 : 0;

    charts.activityChart.options.scales.y.min = minValue;
    charts.activityChart.data.datasets[0].data = activeHours;
    charts.activityChart.update();
}

function updateActivityDaysChart(data) {
    const labels = Object.keys(data);
    const chartData = Object.values(data);

    if (labels.length > 30) {
        const groupedData = groupByMonth(labels, chartData);
        charts.activityDaysChart.data.labels = Object.keys(groupedData);
        charts.activityDaysChart.data.datasets[0].data =
            Object.values(groupedData);
    } else {
        charts.activityDaysChart.data.labels = labels;
        charts.activityDaysChart.data.datasets[0].data = chartData;
    }

    charts.activityDaysChart.update();
}

function groupByMonth(dates, data) {
    const months = {};
    dates.forEach((date, index) => {
        const month = new Date(date).toLocaleString("default", {
            month: "short",
            year: "numeric",
        });
        months[month] = (months[month] || 0) + data[index];
    });
    return months;
}

function updateChart(chartName, data, labelKey, dataKey) {
    const chart = charts[chartName];
    chart.data.labels = data.map((item) => item[labelKey]);
    chart.data.datasets[0].data = data.map((item) => item[dataKey]);
    chart.update();
}

function updateChartZeroData(chartName) {
    const chart = charts[chartName];
    chart.data.labels = [];
    chart.data.datasets[0].data = [];

    if (chartName !== "countryChart") {
        chart.options.plugins.annotation = {
            annotations: {
                noData: {
                    type: "label",
                    content: "No data available",
                    font: { size: 16, weight: "bold" },
                    color: "#6c757d",
                    textAlign: "center",
                    position: "center",
                },
            },
        };
    }
    chart.update();
}

async function fetchData(dataId, route, startDate, endDate) {
    try {
        const response = await $.ajax({
            headers: { "X-CSRF-TOKEN": $('input[name="_token"]').val() },
            url: route,
            type: "GET",
            data: { startDate, endDate },
        });

        if (response) {
            if (response.active_days) {
                updateActivityDaysChart(response.active_days);
                const dates = Object.keys(response.active_days).sort();
            }
            if (response.link) {
                updateStatTextBlock(response);
            }

            if (response.active_hours?.length === 24) {
                updateActivityChart(response.active_hours);
            }

            if (response.top_countries) {
                updateChart(
                    "countryChart",
                    response.top_countries,
                    "country_name",
                    "click_count"
                );
            }
            if (response.top_devices) {
                updateChart(
                    "deviceChart",
                    response.top_devices,
                    "os",
                    "click_count"
                );
            }
            if (response.top_browsers) {
                updateChart(
                    "browserChart",
                    response.top_browsers,
                    "browser",
                    "click_count"
                );
            }
        }
    } catch (error) {
        console.error("AJAX Error:", error);
    }
}

async function getLinkData(link_id, route) {
    try {
        const response = await $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
            },
            url: route,
            type: "GET",
        });

        if (response) {
            if (response.link_data && typeof response.link_data === "object") {
                $("#editCustomName").val(response.link_data.custom_name || "");

                $("#editSource").val(response.link_data.destination);

                const shortNameText = response.link_data.short_name
                    ? response.link_data.short_name
                    : "";
                const domainName = response.link_data.domain.name
                    ? response.link_data.domain.name
                    : "";
                $("#editShortNameDisplay").text(
                    "https://" + domainName + "/" + shortNameText
                );
                $("#editLinkModalCopyButton").data(
                    "link",
                    "https://" + domainName + "/" + shortNameText
                );
                if (response.link_data.available === 1) {
                    $("#editAvailable").val("1");
                } else {
                    $("#editAvailable").val("0");
                }
            }
        } else {
            console.error("Empty response received");
        }
    } catch (error) {
        console.error("AJAX error:", error);
    }
}

function updateStatTextBlock(data) {
    $("#statDestination").text(data.link.destination).attr("title", data.link.destination);
    $("#statDestinationCopyBtn").data("link", data.link.destination);
    $("#statShortURL").text(
        data.link.domain.name.toString() + "/" + data.link.short_name.toString()
    ).attr("title", data.link.domain.name.toString() + "/" + data.link.short_name.toString());
    $("#statShortURLCopyBtn").data("link",
        data.link.domain.name.toString() + "/" + data.link.short_name.toString()
    );
    $("#statCreatedAt").text(new Date(data.link.created_at).toLocaleDateString("en-UK"));
    $("#statIPAddress").text((data.link.ip_address).toString() ?? "No data");
    if (data.link.available) {
        $("#statStatus").text('Active');
    } else $("#statStatus").text("Inactive");
    $("#statTotalClicks").text(data.total_clicks_by_date ?? 0);
    $("#statTotalUniqueClicks").text(data.total_unique_clicks_by_date ?? 0);
    $("#statTopCountry").text(
        data.top_countries.length > 0 ? data.top_countries[0].country_name : "No data"
    );
    $("#statTopOS").text(
        data.top_devices.length > 0 ? data.top_devices[0].os : "No data"
    );
    $("#statTopBrowser").text(
        data.top_browsers.length > 0 ? data.top_browsers[0].browser : "No data"
    );
}

$(".view-stats").on("click", function () {
    const dataId = $(this).data("id");
    const route = "/admin/links/" + dataId.toString();
    $("#selected-link").data("id", dataId).data("url", route);
    fetchData(dataId, route, null, null);
});

$("#startDate, #endDate").on("change", function () {
    const dataId = $("#selected-link").data("id");
    const route = $("#selected-link").data("url");
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();
    fetchData(dataId, route, startDate, endDate);
});

$(".edit-link").on("click", function () {
    const link_id = $(this).data("id");
    const custom_name = $(this).data("name");
    const url = $(this).data("url");
    const formUrl = $(this).data("form-url");
    const status = $(this).data("status");
    $("#editLinkId").val(link_id);
    $("#editCustomName").val(custom_name);
    $("#editURL").val(url);
    $("#editStatus").val(status);
    $("#editLinkForm").attr("action", formUrl);
    $("#editLinkModal").modal("show");
});

$(".delete-button").on("click", function () {
    var linkId = $(this).data("id");
    $("#confirmDeleteButton").data("formId", "delete-form-" + linkId);
    $("#deleteConfirmModal").modal("show");
});

$("#confirmDeleteButton").on("click", function () {
    var formId = $(this).data("formId");
    $("#" + formId).submit();
});

$(".copy-button").on("click", function () {
    const link = $(this).data("link");
    const $button = $(this);
    const $icon = $button.find("i");

    navigator.clipboard
        .writeText(link)
        .then(() => {
            $icon.removeClass("bi-clipboard").addClass("bi-check-lg");
            setTimeout(() => {
                $icon.removeClass("bi-check-lg").addClass("bi-clipboard");
            }, 10000);
        })
        .catch((err) => {
            console.error("Copy error: ", err);
        });
});
