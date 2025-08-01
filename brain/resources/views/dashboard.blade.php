@extends('layouts.app')

@section('content')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <div class="p-4 bg-gray-100">
{{-- Vici Leads Metrics --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
  <x-card class="text-center">
    <h4 class="text-sm text-secondary">Total Leads</h4>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_total'] }}</p>
  </x-card>
  <x-card class="text-center">
    <h4 class="text-sm text-secondary">New Leads</h4>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_new'] }}</p>
  </x-card>
  <x-card class="text-center">
    <h4 class="text-sm text-secondary">Contacted</h4>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_contacted'] }}</p>
  </x-card>
  <x-card class="text-center">
    <h4 class="text-sm text-secondary">Converted</h4>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_converted'] }}</p>
  </x-card>
</div>
{{-- Vici Traffic Metrics --}}
<h2 class="text-lg font-semibold text-primary mb-4">Vici Traffic</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <x-card class="text-center">
    <h5 class="text-sm text-secondary mb-2">Sent</h5>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_sent'] }}</p>
  </x-card>
  <x-card class="text-center">
    <h5 class="text-sm text-secondary mb-2">Converted</h5>
    <p class="text-2xl font-bold text-primary">{{ $metrics['vici_converted'] }}</p>
  </x-card>
</div>

{{-- SMS Analytics --}}
<h2 class="text-lg font-semibold text-primary mb-4">SMS Analytics</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <x-card>
    <h5 class="text-sm text-secondary mb-2">Sent</h5>
    <p class="text-xl font-bold">{{ $metrics['sms_sent'] }}</p>
  </x-card>
  <x-card>
    <h5 class="text-sm text-secondary mb-2">Delivered</h5>
    <p class="text-xl font-bold">{{ $metrics['sms_delivered'] }}</p>
  </x-card>
</div>

{{-- Ringba Traffic --}}
<h2 class="text-lg font-semibold text-primary mb-4">Ringba Traffic</h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <x-card>
    <h5 class="text-sm text-secondary mb-2">Leads Sent</h5>
    <p class="text-xl font-bold">{{ $metrics['ringba_sent'] }}</p>
  </x-card>
  <x-card>
    <h5 class="text-sm text-secondary mb-2">Converted</h5>
    <p class="text-xl font-bold">{{ $metrics['ringba_converted'] }}</p>
  </x-card>
</div>
        <!-- Date Range Filter -->
        <div class="max-w-4xl mx-auto mb-6 flex flex-col sm:flex-row sm:space-x-4 bg-white p-4 rounded-lg shadow">
            <div class="flex-1">
                <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input id="startDate" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div class="flex-1 mt-4 sm:mt-0">
                <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                <input id="endDate" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
            <div class="flex items-end mt-4 sm:mt-0">
                <button id="filterButton" class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">Filter</button>
            </div>
        </div>

        <!-- Leads Per Day Card -->
        <x-card class="mb-6 lg:mb-0 lg:w-1/3 border border-secondary">
            <h3 class="text-lg font-semibold text-primary mb-2">Leads Per Day</h3>
            <canvas id="leadsChart"></canvas>
        </x-card>

        <!-- Source Breakdown Card -->
        <x-card class="mb-6 lg:mb-0 lg:w-1/3 border border-secondary">
            <h3 class="text-lg font-semibold text-primary mb-2">Source Breakdown</h3>
            <canvas id="sourceChart"></canvas>
        </x-card>

        <!-- Conversion Rate Card -->
        <x-card class="lg:w-1/3 border border-secondary">
            <h3 class="text-lg font-semibold text-primary mb-2">Conversion Rate</h3>
            <canvas id="conversionChart"></canvas>
        </x-card>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            let leadsData = [];
            let leadsChart;
            const ctxLeads = document.getElementById('leadsChart').getContext('2d');
            const ctxSource = document.getElementById('sourceChart').getContext('2d');
            const ctxConversion = document.getElementById('conversionChart').getContext('2d');

            function renderLeadsChart(data) {
                const labels = data.map(item => item.date);
                const totals = data.map(item => item.total);
                if (leadsChart) leadsChart.destroy();
                leadsChart = new Chart(ctxLeads, {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'Leads', data: totals, borderColor: 'rgba(59, 130, 246, 1)', backgroundColor: 'rgba(59, 130, 246, 0.2)', fill: true, tension: 0.3 }] },
                    options: { responsive: true, plugins: { legend: { display: true, position: 'top' } }, scales: { x: { title: { display: true, text: 'Date' } }, y: { title: { display: true, text: 'Total Leads' } } } }
                });
            }

            fetch('/api/analytics/leads-per-day')
                .then(res => res.json())
                .then(data => { leadsData = data; renderLeadsChart(leadsData); });

            document.getElementById('filterButton').addEventListener('click', () => {
                const start = document.getElementById('startDate').value;
                const end = document.getElementById('endDate').value;
                if (start && end) renderLeadsChart(leadsData.filter(item => item.date >= start && item.date <= end));
            });

            fetch('/api/analytics/source-breakdown')
                .then(res => res.json())
                .then(data => new Chart(ctxSource, { type: 'pie', data: { labels: data.map(item => item.source), datasets: [{ label: 'Sources', data: data.map(item => item.total), backgroundColor: ['rgba(134, 239, 172, 0.6)', 'rgba(252, 165, 165, 0.6)', 'rgba(249, 115, 22, 0.6)', 'rgba(96, 165, 250, 0.6)'] }] }, options: { responsive: true, plugins: { legend: { display: true, position: 'bottom' } } } }));

            fetch('/api/analytics/conversion-rate')
                .then(res => res.json())
                .then(data => {
                    const total = data.total_leads;
                    const converted = data.converted_leads;
                    const rate = data.conversion_rate;
                    new Chart(ctxConversion, { type: 'doughnut', data: { labels: ['Converted', 'Not Converted'], datasets: [{ data: [converted, total - converted], backgroundColor: ['rgba(96, 165, 250, 0.6)', 'rgba(248, 113, 113, 0.6)'], conversionRate: rate }] }, options: { responsive: true, cutout: '70%', plugins: { legend: { display: true, position: 'bottom' } } }, plugins: [{ id: 'centerText', afterDraw: chart => { const { ctx, chartArea: { left, right, top, bottom } } = chart; ctx.save(); ctx.font = 'bold 24px sans-serif'; ctx.fillStyle = '#000'; ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.fillText(rate + '%', (left + right) / 2, (top + bottom) / 2); ctx.restore(); } }] });
                });
        });
        </script>
@endsection
