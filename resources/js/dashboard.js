Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = "#6c757d";
Chart.defaults.borderColor = "rgba(0, 0, 0, 0.05)";

function parseJSONData(selector) {
    try {
        return JSON.parse($(selector).text().trim());
    } catch (error) {
        console.error(`Failed to parse data from ${selector}`, error);
        return [];
    }
}

function getContext(selector) {
    const canvas = document.getElementById(selector);
    if (!canvas) {
        console.error(`Canvas with ID '${selector}' not found.`);
        return null;
    }
    return canvas.getContext("2d");
}

function createChartConfig(type, data, options) {
    return { type, data, options };
}

function renderChart(ctx, config) {
    if (ctx) {
        new Chart(ctx, config);
    }
}

function generateColors(baseColors, length) {
    return baseColors.slice(0, length).map((color) => ({
        backgroundColor: color,
        borderColor: color.replace("0.6", "1"),
    }));
}

(function initializeCountriesChart() {
    const countriesData = parseJSONData("#countriesData");
    const countryCodes = countriesData.map((country) => country.country);
    const clickCounts = countriesData.map((country) => country.click_count);

    renderChart(
        getContext("countriesChart"),
        createChartConfig(
            "bar",
            {
                labels: countryCodes,
                datasets: [
                    {
                        data: clickCounts,
                        backgroundColor: [
                            "rgba(0, 123, 255, 0.8)",
                            "rgba(100, 181, 246, 0.8)",
                            "rgba(247, 184, 48, 0.8)",
                            "rgba(40, 167, 69, 0.8)",
                            "rgba(220, 53, 69, 0.8)",
                        ],
                        borderColor: "rgba(255, 255, 255, 0.3)",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 40,
                    },
                ],
            },
            {
                responsive: true,
                indexAxis: "y",
                scales: {
                    x: { beginAtZero: true },
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                },
                plugins: {
                    legend: {
                        display: false,
                        position: 'top',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#212529',
                        bodyColor: '#6c757d',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxShadow: '0 8px 24px rgba(0, 0, 0, 0.05)',
                        usePointStyle: true
                    }
                }
            }
        )
    );
})();

(function initializeBrowsersChart() {
    const browsersData = parseJSONData("#browsersData");
    const browsers = browsersData.map((browser) => browser.browser);
    const clickCounts = browsersData.map((browser) => browser.click_count);

    renderChart(
        getContext("browsersChart"),
        createChartConfig(
            "bar",
            {
                labels: browsers,
                datasets: [
                    {
                        data: clickCounts,
                        backgroundColor: [
                            "rgba(0, 123, 255, 0.8)",
                            "rgba(100, 181, 246, 0.8)",
                            "rgba(247, 184, 48, 0.8)",
                            "rgba(40, 167, 69, 0.8)",
                            "rgba(220, 53, 69, 0.8)",
                        ],
                        borderColor: "rgba(255, 255, 255, 0.3)",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 40,
                    },
                ],
            },
            {
                responsive: true,
                indexAxis: "y",
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } },
                    y: { beginAtZero: true },
                },
                plugins: {
                    legend: {
                        display: false,
                        position: 'top',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#212529',
                        bodyColor: '#6c757d',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxShadow: '0 8px 24px rgba(0, 0, 0, 0.05)',
                        usePointStyle: true
                    }
                }
            }
        )
    );
})();

(function initializeOSChart() {
    const osData = parseJSONData("#osData");
    const osNames = osData.map((os) => os.os);
    const clickCounts = osData.map((os) => os.click_count);

    renderChart(
        getContext("osChart"),
        createChartConfig(
            "doughnut",
            {
                labels: osNames,
                datasets: [
                    {
                        data: clickCounts,
                        borderColor: "rgba(255, 255, 255, 0.3)",
                        borderWidth: 2,
                        spacing: 5,
                        hoverOffset: 15,
                        backgroundColor: [
                            "rgba(0, 123, 255, 0.8)",
                            "rgba(100, 181, 246, 0.8)",
                            "rgba(247, 184, 48, 0.8)",
                            "rgba(40, 167, 69, 0.8)",
                            "rgba(220, 53, 69, 0.8)",
                        ],
                    },
                ],
            },
            {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#212529',
                        bodyColor: '#6c757d',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxShadow: '0 8px 24px rgba(0, 0, 0, 0.05)',
                        usePointStyle: true
                    }
                }
            }
        )
    );
})();

(function initializeTimeChart() {
    const clicksByHour = parseJSONData("#timeData");
    //Generate time for chart
    const timeLabels = Array.from(
        { length: 24 },
        (_, i) => `${i.toString().padStart(2, "0")}:00`
    );

    renderChart(
        getContext("timeChart"),
        createChartConfig(
            "line",
            {
                labels: timeLabels,
                datasets: [
                    {
                        label: "Clicks per hour",
                        data: clicksByHour,
                        borderColor: "rgba(0, 123, 255, 0.8)",
                        backgroundColor: "rgba(100, 181, 246, 0.15)",
                        borderWidth: 3,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: "#fff",
                        pointBorderColor: "rgba(0, 123, 255, 0.8)",
                        tension: 0.4,
                        fill: {
                            target: "origin",
                            above: "rgba(100, 181, 246, 0.05)",
                        },
                    },
                ],
            },
            {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            boxWidth: 15,
                            font: { size: 13 }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.95)',
                        titleColor: '#212529',
                        bodyColor: '#6c757d',
                        borderColor: 'rgba(0, 0, 0, 0.1)',
                        borderWidth: 1,
                        padding: 12,
                        boxShadow: '0 8px 24px rgba(0, 0, 0, 0.05)',
                        usePointStyle: true,
                        callbacks: {
                            label: (tooltipItem) =>
                                `Clicks: ${tooltipItem.raw}`,
                        },
                    }
                },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true },
                },
            }
        )
    );
})();
