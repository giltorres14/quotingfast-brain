import Chart from 'chart.js/auto';

// Default chart options using extended blue/gray theme
const defaultOptions = {
  elements: {
    line: { tension: 0.3, borderWidth: 2, borderColor: '#1E3A8A' }
  },
  plugins: {
    legend: { labels: { color: '#4B5563' } }
  },
  scales: {
    x: { grid: { color: '#E5E7EB' } },
    y: { beginAtZero: true, grid: { color: '#E5E7EB' } }
  }
};

export function initDashboardCharts(leadsData, sourceData, conversionData) {
  new Chart(document.getElementById('leadsChart'), {
    type: 'line',
    data: leadsData,
    options: { ...defaultOptions }
  });

  new Chart(document.getElementById('sourceChart'), {
    type: 'pie',
    data: sourceData,
    options: { ...defaultOptions, plugins: { legend: { position: 'right' } } }
  });

  new Chart(document.getElementById('conversionChart'), {
    type: 'doughnut',
    data: conversionData,
    options: { ...defaultOptions, cutout: '70%', plugins: { legend: { display: false }, tooltip: { enabled: false } } }
  });
} 