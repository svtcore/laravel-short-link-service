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
    return canvas.getContext('2d');
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
        borderColor: color.replace('0.6', '1'),
    }));
}


(function initializeCountriesChart() {
    const countriesData = parseJSONData('#countriesData');
    const countryCodes = countriesData.map((country) => country.country);
    const clickCounts = countriesData.map((country) => country.click_count);
    const colors = [
        "rgba(0, 156, 21, 0.8)",
        "rgba(54, 162, 235, 0.6)",
        "rgba(255, 231, 14, 0.6)",
        "rgba(75, 192, 192, 0.6)",
        "rgba(153, 102, 255, 0.6)",
    ];
    const colorConfig = generateColors(colors, countryCodes.length);

    renderChart(getContext('countriesChart'), createChartConfig('bar', {
        labels: countryCodes,
        datasets: [{
            data: clickCounts,
            backgroundColor: colorConfig.map((c) => c.backgroundColor),
            borderColor: colorConfig.map((c) => c.borderColor),
            borderWidth: 1,
        }],
    }, {
        responsive: true,
        indexAxis: 'y',
        scales: {
            x: { beginAtZero: true },
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
        },
        plugins: { legend: { display: false } },
    }));
})();


(function initializeBrowsersChart() {
    const browsersData = parseJSONData('#browsersData');
    const browsers = browsersData.map((browser) => browser.browser);
    const clickCounts = browsersData.map((browser) => browser.click_count);
    const colors = [
        "rgba(12, 166, 228, 0.8)",
        "rgba(45, 250, 205, 0.6)",
        "rgba(255, 230, 8, 0.75)",
        "rgba(171, 58, 247, 0.6)",
        "rgba(243, 54, 95, 0.6)",
    ];
    const colorConfig = generateColors(colors, browsers.length);

    renderChart(getContext('browsersChart'), createChartConfig('bar', {
        labels: browsers,
        datasets: [{
            data: clickCounts,
            backgroundColor: colorConfig.map((c) => c.backgroundColor),
            borderColor: colorConfig.map((c) => c.borderColor),
            borderWidth: 1,
        }],
    }, {
        responsive: true,
        indexAxis: 'y',
        scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1 } },
            y: { beginAtZero: true },
        },
        plugins: { legend: { display: false } },
    }));
})();

(function initializeOSChart() {
    const osData = parseJSONData('#osData');
    const osNames = osData.map((os) => os.os);
    const clickCounts = osData.map((os) => os.click_count);
    const colors = [
        "rgba(88, 23, 240, 0.7)",
        "rgba(86, 179, 241, 0.7)",
        "rgba(255, 185, 8, 0.75)",
        "rgba(2, 255, 129, 0.74)",
        "rgba(23, 245, 245, 0.6)"
    ];
    const colorConfig = generateColors(colors, osNames.length);

    renderChart(getContext('osChart'), createChartConfig('doughnut', {
        labels: osNames,
        datasets: [{
            data: clickCounts,
            backgroundColor: colorConfig.map((c) => c.backgroundColor),
            borderColor: colorConfig.map((c) => c.borderColor),
            borderWidth: 1,
        }],
    }, {
        responsive: true,
        plugins: { legend: { position: 'top' } },
    }));
})();


(function initializeTimeChart() {
    const clicksByHour = parseJSONData('#timeData');
    //Generate time for chart
    const timeLabels = Array.from({ length: 24 }, (_, i) => `${i.toString().padStart(2, '0')}:00`);

    renderChart(getContext('timeChart'), createChartConfig('line', {
        labels: timeLabels,
        datasets: [{
            label: 'Clicks per hour',
            data: clicksByHour,
            borderColor: 'rgb(47, 130, 255)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderWidth: 2,
            fill: true,
        }],
    }, {
        responsive: true,
        plugins: {
            legend: { position: 'top' },
            tooltip: {
                callbacks: {
                    label: (tooltipItem) => `Clicks: ${tooltipItem.raw}`,
                },
            },
        },
        scales: {
            x: { ticks: { font: { size: 10 } } },
            y: { beginAtZero: true },
        },
    }));
})();
