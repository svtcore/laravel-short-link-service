Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = "#6c757d";
Chart.defaults.borderColor = "rgba(0, 0, 0, 0.05)";

const CHART_COLORS = {
    primary: "rgba(0, 123, 255, 0.8)",
    secondary: "rgba(100, 181, 246, 0.8)",
    warning: "rgba(247, 184, 48, 0.8)",
    success: "rgba(40, 167, 69, 0.8)",
    danger: "rgba(220, 53, 69, 0.8)",
    gradientFill: "rgba(100, 181, 246, 0.15)",
    countriesFill: [
        "rgba(0, 123, 255, 0.8)",
        "rgba(100, 181, 246, 0.8)",
        "rgba(247, 184, 48, 0.8)",
        "rgba(40, 167, 69, 0.8)",
        "rgba(220, 53, 69, 0.8)",
    ],
    tooplitCountriesFill: "rgba(255, 255, 255, 0.95)",
    browsersFill: [
        "rgba(255, 87, 34, 0.8)",
        "rgba(0, 123, 255, 0.8)",
        "rgba(255, 69, 0, 0.8)",
        "rgba(40, 167, 69, 0.8)",
        "rgba(0, 56, 70, 0.8)",
        "rgba(0, 123, 255, 0.8)",
        "rgba(66, 133, 244, 0.8)",
        "rgba(0, 46, 104, 0.8)",
        "rgba(0, 122, 204, 0.8)",
        "rgba(69, 92, 121, 0.8)",
        "rgba(240, 94, 91, 0.8)",
        "rgba(76, 175, 80, 0.8)",
        "rgba(0, 188, 212, 0.8)",
        "rgba(0, 0, 0, 0.8)",
        "rgba(253, 214, 44, 0.8)",
        "rgba(215, 58, 50, 0.8)",
        "rgba(72, 209, 204, 0.8)",
        "rgba(250, 102, 102, 0.8)",
        "rgba(92, 41, 99, 0.8)",
    ],
    platformFill: [
        "rgba(0, 123, 255, 0.8)",
        "rgba(100, 181, 246, 0.8)",
        "rgba(247, 184, 48, 0.8)",
        "rgba(40, 167, 69, 0.8)",
        "rgba(220, 53, 69, 0.8)",
    ],
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
    ...commonChartOptions,
    type: "line",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks within date",
                data: [],
                borderColor: CHART_COLORS.primary,
                backgroundColor: CHART_COLORS.gradientFill,
                borderWidth: 4,
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
        ...commonChartOptions.options,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: "Clicks" },
            },
            x: {
                title: { display: true, text: "Date" },
                ticks: { autoSkip: true, maxTicksLimit: 12 },
            },
        },
    },
};

const activityTimeChartConfig = {
    ...commonChartOptions,
    type: "line",
    data: {
        labels: Array.from(
            { length: 24 },
            (_, i) => `${String(i).padStart(2, "0")}:00`
        ),
        datasets: [
            {
                label: "Clicks within hours",
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
        ...commonChartOptions.options,
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

const geoChartConfig = {
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
            x: { beginAtZero: true },
            y: { grid: { display: false } },
        },
    },
};

const browserChartConfig = {
    ...commonChartOptions,
    type: "doughnut",
    data: {
        labels: [],
        datasets: [
            {
                label: "Clicks",
                data: [],
                backgroundColor: CHART_COLORS.browsersFill,
                borderColor: "rgba(255, 255, 255, 0.3)",
                borderWidth: 2,
                spacing: 5,
                hoverOffset: 15,
            },
        ],
    },
    options: {
        ...commonChartOptions.options,
        plugins: {
            legend: {
                position: "bottom",
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
    },
};

const platformChartConfig = {
    type: "doughnut",
    data: {
        labels: [],
        datasets: [
            {
                data: [],
                borderColor: "rgba(255, 255, 255, 0.3)",
                borderWidth: 2,
                spacing: 5,
                hoverOffset: 15,
                backgroundColor: CHART_COLORS.platformFill,
            },
        ],
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "bottom",
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
    },
};

const charts = {
    activityDaysChart: new Chart(
        document.getElementById("activityDaysChart").getContext("2d"),
        activityDaysChartConfig
    ),
    activityTimeChart: new Chart(
        document.getElementById("activityTimeChart").getContext("2d"),
        activityTimeChartConfig
    ),
    geoChart: new Chart(
        document.getElementById("geoChart").getContext("2d"),
        geoChartConfig
    ),
    browserChart: new Chart(
        document.getElementById("browserChart").getContext("2d"),
        browserChartConfig
    ),
    platformChart: new Chart(
        document.getElementById("platformChart").getContext("2d"),
        platformChartConfig
    ),
};

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

function updateActivityChart(activeHours) {
    const nonZeroData = activeHours.filter((val) => val > 0);
    const minValue = nonZeroData.length ? Math.min(...nonZeroData) - 1 : 0;

    charts.activityTimeChart.options.scales.y.min = minValue;
    charts.activityTimeChart.data.datasets[0].data = activeHours;
    charts.activityTimeChart.update();
}

function updateBasicChart(
    data,
    inputChart,
    inputChartConfig,
    key_name,
    value_name
) {
    const inputKey = data.map((item) => item[key_name]);
    const inputValue = data.map((item) => item[value_name]);

    inputChartConfig.data.labels = inputKey;
    inputChartConfig.data.datasets[0].data = inputValue;

    inputChart.update();
}

function groupByMonth(dates, data) {
    const months = {};
    dates.forEach((date, index) => {
        const [year, month, day] = date.split("-");
        const monthName = new Date(year, month - 1, day).toLocaleString(
            "default",
            {
                month: "short",
                year: "numeric",
            }
        );
        months[monthName] = (months[monthName] || 0) + data[index];
    });
    return months;
}

async function fetchNewData(route, startDate, endDate) {
    try {
        const response = await $.ajax({
            headers: { "X-CSRF-TOKEN": $('input[name="_token"]').val() },
            url: route,
            type: "GET",
            data: { startDate, endDate },
            dataType: "json",
            timeout: 10000,
        });

        if (!response || typeof response !== "object") {
            throw new Error("Invalid response format");
        }

        if (
            !("total_links_by_date" in response) ||
            !("total_clicks_by_date" in response) ||
            !("total_unique_clicks_by_date" in response) ||
            !("total_users_by_date" in response)
        ) {
            throw new Error("Missing required fields in response");
        }

        $("#totalLinksByDate").text(response.total_links_by_date);
        $("#totalClicksByDate").text(response.total_clicks_by_date);
        $("#totalUniqueClicksByDate").text(
            response.total_unique_clicks_by_date
        );
        $("#totalUsersByDate").text(response.total_users_by_date);

        if (response.chart_days_activity_data) {
            updateActivityDaysChart(response.chart_days_activity_data);
        }
        if (response.chart_time_activity_data) {
            updateActivityChart(response.chart_time_activity_data);
        }
        if (response.chart_geo_data) {
            updateBasicChart(
                response.chart_geo_data,
                charts.geoChart,
                geoChartConfig,
                "country",
                "click_count"
            );
        }
        if (response.chart_browser_data) {
            updateBasicChart(
                response.chart_browser_data,
                charts.browserChart,
                browserChartConfig,
                "browser",
                "click_count"
            );
        }
        if (response.chart_platform_data) {
            updateBasicChart(
                response.chart_platform_data,
                charts.platformChart,
                platformChartConfig,
                "os",
                "click_count"
            );
        }
    } catch (error) {
        console.error("AJAX Error:", error);
        showNotification(
            "Error fetching data. Please try again later.",
            "error"
        );
    }
}

$("#startDate, #endDate").on("change", function () {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();
    const route = $("#chart-update-route").data("route");
    fetchNewData(route, startDate, endDate);
});

const chartDaysActivityData = JSON.parse($("#chartDaysActivityData").val());
const chartTimeActivityData = JSON.parse($("#chartTimeActivityData").val());
const chartGeoData = JSON.parse($("#chartGeoData").val());
const chartBrowserData = JSON.parse($("#chartBrowserData").val());
const chartPlatformData = JSON.parse($("#chartPlatformData").val());

updateActivityDaysChart(chartDaysActivityData);
updateActivityChart(chartTimeActivityData);
updateBasicChart(
    chartGeoData,
    charts.geoChart,
    geoChartConfig,
    "country",
    "click_count"
);
updateBasicChart(
    chartBrowserData,
    charts.browserChart,
    browserChartConfig,
    "browser",
    "click_count"
);
updateBasicChart(
    chartPlatformData,
    charts.platformChart,
    platformChartConfig,
    "os",
    "click_count"
);
