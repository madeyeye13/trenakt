//
import './install-prompt.js';
import './scroll-reveal';
import Chart from 'chart.js/auto';

// Compact ₦ formatting for axis ticks (e.g. 12000 -> "₦12k").
function formatNairaCompact(value) {
    const n = Number(value);
    if (Math.abs(n) >= 1000000) return '₦' + (n / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    if (Math.abs(n) >= 1000) return '₦' + (n / 1000).toFixed(0) + 'k';
    return '₦' + n.toLocaleString('en-NG');
}

// Full ₦ formatting for tooltips.
function formatNaira(value) {
    return '₦' + Number(value).toLocaleString('en-NG', { maximumFractionDigits: 0 });
}

// Registers the <x-chart> Alpine component. Reads brand colors straight off
// the CSS custom properties in app.css so the palette never drifts out of
// sync with the design system. Grid/tick/legend colors are theme-dependent
// (everything else — dataset colors — comes from the fixed brand palette and
// doesn't need to change), so the component watches the <html> element's
// data-theme attribute directly with a MutationObserver and repaints those
// colors on the live chart the moment it changes — regardless of which
// toggle (header, admin sidebar, mobile profile page, or any future one)
// changed it — instead of leaving the chart stuck at whatever theme was
// active when it was first drawn.
document.addEventListener('alpine:init', () => {
    Alpine.data('trenaktChart', (config) => ({
        themeObserver: null,
        themeListener: null,

        chartInstance() {
            return this.$el._trenaktChart || null;
        },

        themeColors() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(20, 19, 15, 0.06)',
                textColor: isDark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(20, 19, 15, 0.5)',
            };
        },

        updateTheme() {
            const chart = this.chartInstance();
            if (!chart) return;

            const colors = this.themeColors();
            Chart.defaults.color = colors.textColor;
            chart.options.color = colors.textColor;
            chart.options.plugins.legend.labels.color = colors.textColor;
            chart.options.plugins.tooltip.titleColor = colors.textColor;
            chart.options.plugins.tooltip.bodyColor = colors.textColor;
            chart.options.plugins.tooltip.footerColor = colors.textColor;
            chart.options.scales.x.ticks.color = colors.textColor;
            chart.options.scales.y.ticks.color = colors.textColor;
            chart.options.scales.y.grid.color = colors.gridColor;
            chart.resize();
            chart.update('none');
        },

        init() {
            const styles = getComputedStyle(document.documentElement);
            const palette = [
                styles.getPropertyValue('--color-trenakt-primary').trim(),
                styles.getPropertyValue('--color-trenakt-accent').trim(),
                styles.getPropertyValue('--color-trenakt-info').trim(),
                styles.getPropertyValue('--color-trenakt-warning').trim(),
            ];
            const { gridColor, textColor } = this.themeColors();
            const isLine = config.type === 'line';

            const datasets = config.datasets.map((dataset, index) => {
                const color = dataset.color || palette[index % palette.length];

                return {
                    label: dataset.label,
                    data: dataset.data,
                    borderColor: color,
                    backgroundColor: isLine ? color + '26' : color,
                    borderRadius: isLine ? 0 : 6,
                    borderWidth: 2,
                    tension: 0.35,
                    fill: isLine,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    maxBarThickness: 32,
                };
            });

            const chart = new Chart(this.$el, {
                type: config.type,
                data: { labels: config.labels, datasets },
                options: {
                    color: textColor,
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            display: datasets.length > 1,
                            labels: { color: textColor, boxWidth: 10, usePointStyle: true, pointStyle: 'circle' },
                        },
                        tooltip: {
                            callbacks: config.currency ? {
                                label: (ctx) => ` ${ctx.dataset.label}: ${formatNaira(ctx.parsed.y)}`,
                            } : undefined,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: textColor },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: config.currency ? (value) => formatNairaCompact(value) : undefined,
                            },
                        },
                    },
                },
            });
            this.$el._trenaktChart = chart;

            this.themeObserver = new MutationObserver(() => {
                this.updateTheme();
            });
            this.themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['data-theme'],
            });

            this.themeListener = () => this.updateTheme();
            window.addEventListener('trenakt-theme-changed', this.themeListener);
        },

        destroy() {
            if (this.themeObserver) {
                this.themeObserver.disconnect();
            }
            if (this.themeListener) {
                window.removeEventListener('trenakt-theme-changed', this.themeListener);
            }
            const chart = this.chartInstance();
            if (chart) {
                chart.destroy();
                delete this.$el._trenaktChart;
            }
        },
    }));
});

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
