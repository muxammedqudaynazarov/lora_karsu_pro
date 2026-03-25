<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали устройства</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --accent-yellow: #f59e0b;
            --accent-purple: #8b5cf6;
            --accent-cyan: #06b6d4;
        }

        body {
            font-family: system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .back-btn {
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-card {
            background: var(--card);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            display: block;
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 0;
        }

        .chart-container {
            background: var(--card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border);
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            margin: 0 0 20px 0;
            font-size: 1.1rem;
            color: var(--text);
        }

        .mini-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .canvas-wrapper-main {
            height: 350px;
        }

        .canvas-wrapper-mini {
            height: 180px;
        }

        .table-container {
            background: var(--card);
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            text-align: left;
            padding: 12px;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border);
            font-size: 0.8rem;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        tr:hover {
            background: #f8fafc;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>
<body>

<div class="container">
    <a href="{{ route('main') }}"
       style="border: 1px solid var(--border); border-radius: 10px; padding: 5px 10px; background-color: var(--card)"
       class="back-btn">← Назад</a>
    <a href="{{ route('data.edit', [$device->id, 'today']) }}" class="back-btn"
       style="border: 1px solid var(--border); border-radius: 10px; padding: 5px 10px; background-color: var(--card)"
       class="back-btn">Скачать .XLSX (сегодня)</a>
    <a href="{{ route('data.edit', [$device->id, 'yesterday']) }}" class="back-btn"
       style="border: 1px solid var(--border); border-radius: 10px; padding: 5px 10px; background-color: var(--card)"
       class="back-btn">Скачать .XLSX (вчера)</a>
    <a href="{{ route('data.edit', [$device->id, 'last7days']) }}" class="back-btn"
       style="border: 1px solid var(--border); border-radius: 10px; padding: 5px 10px; background-color: var(--card)"
       class="back-btn">Скачать .XLSX (за 7 дней)</a>
    <a href="{{ route('data.edit', [$device->id, 'lastmonths']) }}" class="back-btn"
       style="border: 1px solid var(--border); border-radius: 10px; padding: 5px 10px; background-color: var(--card)"
       class="back-btn">Скачать .XLSX (за месяц)</a>

    <div class="info-grid">
        <div class="info-card"><span class="info-label">ID устройства</span>
            <p class="info-value">{{ $device->devEUI }}</p></div>
        <div class="info-card"><span class="info-label">Имя</span>
            <p class="info-value">{{ $device->deviceName }}</p></div>
        <div class="info-card">
            <span class="info-label">Статус</span>
            <p class="info-value" style="color: {{ $device->status == '1' ? '#16a34a' : '#ef4444' }}">
                ● {{ $device->status == '1' ? 'Online' : 'Offline' }}
            </p>
        </div>
    </div>
    {{--@if($device->location)
        <div class="map-card" style="background: var(--card); border-radius: 12px; border: 1px solid var(--border); padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <div style="position: relative; width: 100%; height: 350px; border-radius: 8px; overflow: hidden; border: 1px solid var(--border); background: var(--bg);">
                <iframe
                    src="https://maps.google.com/maps?q={{ urlencode($device->location) }}&output=embed"
                    width="100%"
                    height="100%"
                    frameborder="0"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
            </div>
        </div>
    @endif--}}
    <div class="chart-container" id="main-chart-sec" style="display:none">
        <h2 class="chart-title">Динамика показателей</h2>
        <div class="canvas-wrapper-main">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <div class="mini-charts-grid" id="mini-charts-container"></div>

    <div class="table-container" id="table-sec" style="display:none">
        <h2 class="chart-title">История записей</h2>
        <table id="data-table">
            <thead>
            <tr></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<script>
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "timeOut": "5000",
    };
    @if(session('error'))
    toastr.error("{{ session('error') }}", "Системная ошибка!");
    @endif
    @if(session('success'))
    toastr.success("{{ session('success') }}", "Успешная!");
    @endif
    const rawData = @json($device->data);
    if (rawData && rawData.length > 0) {
        document.getElementById('main-chart-sec').style.display = 'block';
        document.getElementById('table-sec').style.display = 'block';

        const metricsConfig = {
            temperature: {label: 'Температура', unit: '°C', color: '#ef4444'},
            moisture: {label: 'Влажность', unit: '%', color: '#3b82f6'},
            electricity: {label: 'Проводимость', unit: 'µS/cm', color: '#f59e0b'},
            illumination: {label: 'Освещенность', unit: 'Lux', color: '#8b5cf6'},
            depth: {label: 'Глубина', unit: 'м', color: '#06b6d4'}
        };

        const activeMetrics = [];
        const processedData = rawData.map(item => {
            const parsed = typeof item.data === 'string' ? JSON.parse(item.data) : item.data;
            const obj = {created_at: item.created_at};
            Object.keys(metricsConfig).forEach(k => {
                const val = item[k] ?? parsed[k];
                if (val !== undefined) {
                    obj[k] = val;
                    if (!activeMetrics.includes(k)) activeMetrics.push(k);
                }
            });
            return obj;
        });

        const labels = processedData.map(i => new Date(i.created_at).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        }));

        // Render Charts... (Logic remains same, colors updated to Light mode)
        const miniContainer = document.getElementById('mini-charts-container');
        activeMetrics.forEach(k => {
            const config = metricsConfig[k];
            miniContainer.innerHTML += `<div class="chart-container"><h3 class="chart-title">${config.label}</h3><div class="canvas-wrapper-mini"><canvas id="chart_${k}"></canvas></div></div>`;
            setTimeout(() => {
                new Chart(document.getElementById(`chart_${k}`), {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            data: processedData.map(i => i[k]),
                            borderColor: config.color,
                            backgroundColor: config.color + '10',
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {legend: {display: false}},
                        scales: {y: {beginAtZero: false, grid: {color: '#f1f5f9'}}, x: {display: false}}
                    }
                });
            }, 0);
        });

        // Main Chart
        new Chart(document.getElementById('mainChart'), {
            type: 'line',
            data: {
                labels,
                datasets: activeMetrics.map(k => ({
                    label: metricsConfig[k].label,
                    data: processedData.map(i => i[k]),
                    borderColor: metricsConfig[k].color,
                    tension: 0.4
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {y: {grid: {color: '#f1f5f9'}}, x: {grid: {color: '#f1f5f9'}}}
            }
        });

        // Table headers and data
        const tr = document.querySelector('#data-table thead tr');
        tr.innerHTML = '<th>Время</th>' + activeMetrics.map(k => `<th>${metricsConfig[k].label}</th>`).join('');
        const tbody = document.querySelector('#data-table tbody');
        processedData.slice().reverse().forEach(i => {
            tbody.innerHTML += `<tr><td>${new Date(i.created_at).toLocaleString()}</td>${activeMetrics.map(k => `<td>${i[k] ?? '--'}</td>`).join('')}</tr>`;
        });
    }
</script>
</body>
</html>
