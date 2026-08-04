import { Controller } from '@hotwired/stimulus';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default class extends Controller {
    static targets = ['canvas', 'monthButton', 'weekButton', 'subtitle'];

    static values = {
        labels: Array,
        weights: Array,
    };

    connect() {
        this.renderChart(this.labelsValue, this.weightsValue);
    }

    showMonth() {
        this.setPeriod('month');
    }

    showWeek() {
        this.setPeriod('week');
    }

    setPeriod(period) {
        const isWeek = period === 'week';
        const labels = isWeek ? this.labelsValue.slice(-7) : this.labelsValue;
        const weights = isWeek ? this.weightsValue.slice(-7) : this.weightsValue;

        this.chart.destroy();
        this.renderChart(labels, weights);
        this.subtitleTarget.textContent = isWeek ? '7 derniers jours' : '30 derniers jours';
        this.monthButtonTarget.classList.toggle('fit-period-active', !isWeek);
        this.weekButtonTarget.classList.toggle('fit-period-active', isWeek);
        this.monthButtonTarget.setAttribute('aria-pressed', String(!isWeek));
        this.weekButtonTarget.setAttribute('aria-pressed', String(isWeek));
    }

    renderChart(labels, weights) {
        this.chart = new Chart(this.canvasTarget, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    data: weights,
                    borderColor: '#047857',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    borderWidth: 2,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#047857',
                    fill: true,
                    tension: 0.42,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            label: (context) => `${context.parsed.y.toFixed(1)} kg`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            color: '#64748b',
                            font: { size: 8 },
                            maxTicksLimit: 6,
                            maxRotation: 0,
                        },
                    },
                    y: {
                        display: false,
                        suggestedMin: 73.5,
                        suggestedMax: 76.5,
                    },
                },
            },
        });
    }

    disconnect() {
        this.chart?.destroy();
    }
}
