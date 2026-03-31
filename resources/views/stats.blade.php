<x-navbar active='stats'/>

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="py-8 px-2 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold dark:text-white">System Statistics</h1>
            <p class="text-gray-400 mt-1 text-sm">Live ingestion and performance metrics - refreshes every 5 seconds</p>
        </div>
        <div class="flex items-center gap-2 mt-2 self-end">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
            </span>
            <span class="text-green-400 text-xs font-medium" id="last-updated">Connecting...</span>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="stat-cards">
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center animate-pulse">
            <div class="h-8 bg-gray-700 rounded mb-2"></div>
            <div class="h-4 bg-gray-700 rounded"></div>
        </div>
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center animate-pulse">
            <div class="h-8 bg-gray-700 rounded mb-2"></div>
            <div class="h-4 bg-gray-700 rounded"></div>
        </div>
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center animate-pulse">
            <div class="h-8 bg-gray-700 rounded mb-2"></div>
            <div class="h-4 bg-gray-700 rounded"></div>
        </div>
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center animate-pulse">
            <div class="h-8 bg-gray-700 rounded mb-2"></div>
            <div class="h-4 bg-gray-700 rounded"></div>
        </div>
        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center animate-pulse">
            <div class="h-8 bg-gray-700 rounded mb-2"></div>
            <div class="h-4 bg-gray-700 rounded"></div>
        </div>
    </div>

    {{-- Events Per Minute --}}
    <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-white">Events Ingested Per Minute</h2>
        <p class="text-gray-400 text-xs mt-0.5 mb-5">Rolling window - last 60 minutes</p>
        <canvas id="eventsPerMinuteChart" height="90"></canvas>
    </div>

    {{-- Events Per Hour --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-white">Events Per Hour</h2>
            <p class="text-gray-400 text-xs mt-0.5 mb-5">Last 12 hours</p>
            <canvas id="eventsPerHourChart" height="200"></canvas>
        </div>

        <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-white">Events by Platform</h2>
            <p class="text-gray-400 text-xs mt-0.5 mb-5">Distribution of MQTT message sources</p>
            <canvas id="entityTypeChart" height="200"></canvas>
        </div>

    </div>

    {{-- Top 10 most active devices --}}
    <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-white">Most Active Devices</h2>
        <p class="text-gray-400 text-xs mt-0.5 mb-5">Top 10 by total event count</p>
        <canvas id="topDevicesChart" height="120"></canvas>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.borderColor = 'rgba(255,255,255,0.07)';

    const PALETTE = ['#6366f1','#22d3ee','#a78bfa','#34d399','#f472b6','#fbbf24','#f87171','#60a5fa'];

    const makeChart = (id, type, opts) => new Chart(
        document.getElementById(id).getContext('2d'),
        { type, ...opts }
    );

    const epmChart = makeChart('eventsPerMinuteChart', 'line', {
        data: {
            labels: [],
            datasets: [{
                label: 'Events/min',
                data: [],
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99,102,241,0.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { 
                    grid: { display: false },
                    ticks: { 
                        maxRotation: 0, 
                        minRotation: 0, 
                        autoSkip: false,
                        callback: function(val, index, ticks) {
                            return (ticks.length - 1 - index) % 10 === 0 ? this.getLabelForValue(val) : '';
                        }
                    }
                }
            }
        }
    });

    const ephChart = makeChart('eventsPerHourChart', 'bar', {
        data: {
            labels: [],
            datasets: [{
                label: 'Events',
                data: [],
                backgroundColor: 'rgba(34,211,238,0.65)',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { beginAtZero: true, ticks: { precision: 0 } },
                x: { 
                    grid: { display: false },
                    ticks: { 
                        maxRotation: 0, 
                        minRotation: 0, 
                        autoSkip: false,
                        callback: function(val, index, ticks) {
                            return this.getLabelForValue(val);
                        }
                    }
                }
            }
        }
    });

    const etChart = makeChart('entityTypeChart', 'doughnut', {
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: PALETTE,
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            animation: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } } }
        }
    });

    const tdChart = makeChart('topDevicesChart', 'bar', {
        data: {
            labels: [],
            datasets: [{
                label: 'Events',
                data: [],
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderRadius: 5,
            }]
        },
        options: {
            responsive: true,
            animation: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    function statCard(label, value, sub, colorClass) {
        return `
            <div class="card bg-card-light dark:bg-card-dark rounded-3xl border border-transparent dark:border-gray-700 shadow-sm p-5 text-center">
                <div class="text-3xl font-bold ${colorClass}">${typeof value === 'number' ? value.toLocaleString() : value}</div>
                <div class="text-white font-medium text-sm mt-1">${label}</div>
                ${sub ? `<div class="text-gray-500 text-xs mt-0.5">${sub}</div>` : ''}
            </div>`;
    }

    function refresh() {
        fetch('/api/stats')
            .then(r => r.json())
            .then(({ cards, events_per_minute, events_per_hour, by_entity_type, top_devices }) => {

                document.getElementById('stat-cards').innerHTML =
                    statCard('Total Events', cards.total_events, null, 'text-white') +
                    statCard('Active Devices', cards.total_devices, null, 'text-white') +
                    statCard('Events Today', cards.events_today, null, 'text-white') +
                    statCard('Events This Hour', cards.events_last_hour, null, 'text-white') +
                    statCard('Ingestion Rate', cards.ingestion_rate, 'events / min avg', 'text-white');

                epmChart.data.labels = events_per_minute.map(r => r.label);
                epmChart.data.datasets[0].data = events_per_minute.map(r => r.count);
                epmChart.update();

                ephChart.data.labels = events_per_hour.map(r => r.label);
                ephChart.data.datasets[0].data = events_per_hour.map(r => r.count);
                ephChart.update();

                etChart.data.labels = by_entity_type.map(r => r.label);
                etChart.data.datasets[0].data = by_entity_type.map(r => r.count);
                etChart.update();

                tdChart.data.labels = top_devices.map(r => r.label);
                tdChart.data.datasets[0].data = top_devices.map(r => r.count);
                tdChart.update();

                document.getElementById('last-updated').textContent = 'Live · updated at ' + new Date().toLocaleTimeString();
            })
            .catch(() => {
                document.getElementById('last-updated').textContent = 'Error fetching data';
            });
    }

    refresh();
    setInterval(refresh, 5000);
</script>
