import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// Site-wide defaults so every chart shares readable typography/tooltip
// styling instead of Chart.js's low-contrast defaults.
Chart.defaults.font.family = "'Noto Sans JP', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.color = '#64748b'; // slate-500
Chart.defaults.borderColor = '#e2e8f0'; // slate-200 gridlines
Chart.defaults.plugins.title.padding = { top: 4, bottom: 16 };
Chart.defaults.plugins.title.font = { size: 14, weight: '600' };
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.92)'; // slate-900
Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
Chart.defaults.plugins.tooltip.bodyColor = '#e2e8f0';
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;
Chart.defaults.plugins.tooltip.displayColors = true;
Chart.defaults.plugins.tooltip.boxPadding = 4;

function initTrendCharts() {
    document.querySelectorAll('[data-trend-chart]').forEach((el) => {
        const config = JSON.parse(el.dataset.trendChart);
        new Chart(el, config);
    });
}

document.addEventListener('DOMContentLoaded', initTrendCharts);
