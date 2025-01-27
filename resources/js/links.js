$("#linksTable").DataTable({
    responsive: true,
    lengthChange: false,
    autoWidth: true,
    searching: true,
    paging: true,
    ordering: true,
    info: false,
    order: [[5, "desc"]],
});

const CHART_COLORS = {
    border: "rgba(54, 162, 235, 1)",
    background: "rgba(54, 162, 235, 0.2)",
    countryColors: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6f42c1"],
    deviceColors: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6f42c1"],
    browserColors: ["#007bff", "#28a745", "#ffc107", "#dc3545", "#6f42c1"],
};


const activityDaysChartConfig = {
    type: "line",
    data: {
        labels: [],
        datasets: [
            {
                label: "Activity",
                data: [],
                borderColor: CHART_COLORS.border,
                backgroundColor: CHART_COLORS.background,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: "Clicks" },
            },
            x: {
                title: { display: true, text: "Date" },
                ticks: {
                    autoSkip: true,
                    maxTicksLimit: 12,
                },
            },
        },
        plugins: { legend: { display: true } },
        interaction: {
            mode: "nearest",
            intersect: false,
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
                label: "Activity",
                data: Array(24).fill(0),
                borderColor: CHART_COLORS.border,
                backgroundColor: CHART_COLORS.background,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            },
        ],
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false,
                title: { display: true, text: "Clicks" },
                ticks: {
                    callback: (value) => (value % 1 === 0 ? value : null),
                },
            },
            x: {
                title: { display: true, text: "Time (hours)" },
            },
        },
        plugins: { legend: { display: true } },
    },
};

const countryChartConfig = {
    type: "bar",
    data: {
        labels: [],
        datasets: [
            {
                label: "Amount of unique clicks",
                data: [],
                backgroundColor: CHART_COLORS.countryColors,
            },
        ],
    },
    options: {
        indexAxis: "y",
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                beginAtZero: true,
                ticks: { stepSize: 1 },
            },
        },
    },
};

const deviceChartConfig = {
    type: "doughnut",
    data: {
        labels: [],
        datasets: [
            {
                label: "Device Distribution",
                data: [],
                backgroundColor: CHART_COLORS.deviceColors,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: "top",
            },
        },
    },
};

const browserChartConfig = {
    type: "pie",
    data: {
        labels: [],
        datasets: [
            {
                label: "Browser Distribution",
                data: [],
                backgroundColor: CHART_COLORS.browserColors,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: "top",
            },
        },
    },
};

function createChart(ctx, config) {
    return new Chart(ctx, config);
}

const activityDaysChart = createChart(
    $("#activityDaysChart")[0].getContext("2d"),
    activityDaysChartConfig
);
const activityChart = createChart(
    $("#activityChart")[0].getContext("2d"),
    activityChartConfig
);
const countryChart = createChart(
    $("#countryChart")[0].getContext("2d"),
    countryChartConfig
);
const deviceChart = createChart(
    $("#deviceChart")[0].getContext("2d"),
    deviceChartConfig
);
const browserChart = createChart(
    $("#browserChart")[0].getContext("2d"),
    browserChartConfig
);

function updateActivityChart(activeHours) {
    const nonZeroData = activeHours.filter((val) => val > 0);
    const minValue = nonZeroData.length ? Math.min(...nonZeroData) - 1 : 0;

    activityChart.options.scales.y.min = minValue;
    activityChart.data.datasets[0].data = activeHours;
    activityChart.update();
}

function updateActivityDaysChart(data) {
    const labels = Object.keys(data);
    const chartData = Object.values(data);

    if (labels.length > 30) {
        const groupedData = groupByMonth(labels, chartData);
        activityDaysChart.data.labels = Object.keys(groupedData);
        activityDaysChart.data.datasets[0].data = Object.values(groupedData);
    } else {
        activityDaysChart.data.labels = labels;
        activityDaysChart.data.datasets[0].data = chartData;
    }

    activityDaysChart.update();
}

function groupByMonth(dates, data) {
    const months = {};

    dates.forEach((date, index) => {
        const month = new Date(date).toLocaleString("default", {
            month: "short",
            year: "numeric",
        });
        if (!months[month]) {
            months[month] = 0;
        }
        months[month] += data[index];
    });

    return months;
}

function updateChart(chart, data, labelKey, dataKey) {
    const labels = data.map((item) => item[labelKey]);
    const chartData = data.map((item) => item[dataKey]);

    chart.data.labels = labels;
    chart.data.datasets[0].data = chartData;

    if (chart.options.plugins.annotation) {
        delete chart.options.plugins.annotation.annotations.noData;
    }

    chart.update();
}

function updateChartZeroData(inputChart) {
    inputChart.data.labels = [];
    inputChart.data.datasets[0].data = [];

    if (inputChart != countryChart) {
        inputChart.options.plugins.annotation = {
            annotations: {
                noData: {
                    type: "label",
                    xValue: 0,
                    yValue: 0,
                    backgroundColor: "rgba(252, 252, 252, 0.33)",
                    content: "No data has been found",
                    font: {
                        size: 16,
                        weight: "bold",
                    },
                    textAlign: "center",
                    textBaseline: "middle",
                    padding: 10,
                    borderRadius: 4,
                },
            },
        };
    }

    inputChart.update();
}

async function fetchData(dataId, route, start_date, end_date) {
    try {
        const response = await $.ajax({
            headers: {
                "X-CSRF-TOKEN": $('input[name="_token"]').val(),
            },
            url: route,
            type: "GET",
            data: { id: dataId, startDate: start_date, endDate: end_date },
        });

        if (response) {
            if (
                response.active_days &&
                typeof response.active_days === "object"
            ) {
                updateActivityDaysChart(response.active_days);
                const activeDaysKeys = Object.keys(response.active_days).sort();
                const startDate = activeDaysKeys[0];
                const endDate = activeDaysKeys[activeDaysKeys.length - 1];
                $("#startDate").val(startDate);
                $("#endDate").val(endDate);
            }

            if (
                Array.isArray(response.active_hours) &&
                response.active_hours.length === 24
            ) {
                updateActivityChart(response.active_hours);
            }

            if (Array.isArray(response.top_countries)) {
                updateChart(
                    countryChart,
                    response.top_countries,
                    "country_name",
                    "click_count"
                );
            }
            if (Array.isArray(response.top_devices)) {
                updateChart(
                    deviceChart,
                    response.top_devices,
                    "os",
                    "click_count"
                );
            }
            if (Array.isArray(response.top_browsers)) {
                updateChart(
                    browserChart,
                    response.top_browsers,
                    "browser",
                    "click_count"
                );
            }
        } else {
            console.error("Empty response received");
        }
    } catch (error) {
        console.error("AJAX error:", error);
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

$(".view-stats").on("click", function () {
    const dataId = $(this).data("id");
    const route = $(this).data("url");
    $("#selected-link").attr("data-id", dataId);
    $("#startDate").val("");
    $("#endDate").val("");
    fetchData(dataId, route, null, null);
});

$("#startDate, #endDate").on("change", function () {
    const dataId = $("#selected-link").data("id");
    const route = $(".view-stats").data("url");
    const startDate = $("#startDate").val() || null;
    const endDate = $("#endDate").val() || null;
    fetchData(dataId, route, startDate, endDate);
});

$(".edit-link").on("click", function () {
    const link_id = $(this).data("id");
    const route = $(this).data("url");
    $("#editLinkId").val(link_id);
    $("#editCustomName").val("");
    $("#editSource").val("");
    $("#editShortNameDisplay").text("");
    getLinkData(link_id, route);
    $("#editLinkModal").modal("show");
});

$("#editLinkForm").on("submit", function (e) {
    e.preventDefault();

    $("#editLinkErrors").addClass("d-none");
    $("#editLinkErrorsList").empty();

    const link_id = $("#editLinkId").val();
    const route = "links/" + String(link_id);
    const formData = {
        custom_name: $("#editCustomName").val(),
        destination: $("#editSource").val(),
        access: $("#editAvailable").val(),
        _method: "PUT",
    };

    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('input[name="_token"]').val(),
        },
        url: route,
        method: "POST", //PUT through param
        data: formData,
        success: function (response) {
            if (response.status === 1) {
                location.reload();
            } else {
                $("#editLinkErrors").removeClass("d-none");
                $("#editLinkErrorsList").append(
                    "<li>Cannot uppdate link</li>"
                );
            }
        },
        error: function (xhr) {
            const errors = xhr.responseJSON?.errors || [
                "There is error, try agair later.",
            ];

            $("#editLinkErrors").removeClass("d-none");

            errors.forEach((error) => {
                $("#editLinkErrorsList").append(`<li>${error}</li>`);
            });
        },
    });
});

$(".delete-button").on('click', function() {
    const linkId = $(this).data('id');
    $('#confirm-delete-button').data('form-id', linkId);
    $('#confirmDeleteModal').modal('show');
});

$('#confirm-delete-button').on('click', function() {
    var linkId = $(this).data('form-id');
    var form = $('#delete-form-' + linkId);
    form.submit();
});

$(".copy-button").on('click', function() {
    const link = $(this).data('link');
    const $button = $(this);
    const $icon = $button.find('i');

    navigator.clipboard.writeText(link).then(() => {
        $icon.removeClass('bi-clipboard').addClass('bi-check-lg');

        $button.addClass('btn-success').removeClass('btn-outline-secondary');

        setTimeout(() => {
            $icon.removeClass('bi-check-lg').addClass('bi-clipboard');
            $button.removeClass('btn-success').addClass('btn-outline-secondary');
        }, 10000);
    }).catch(err => {
        console.error('Copy error: ', err);
    });
});
