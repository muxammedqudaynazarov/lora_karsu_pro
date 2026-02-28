<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>Устройство: {{ $device->deviceName }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4a90e2;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --text-muted: #94a3b8;
            --accent-red: #f43f5e;
            --accent-blue: #3b82f6;
            --accent-yellow: #eab308;
            --accent-purple: #a855f7;
            --accent-cyan: #06b6d4;
            --accent-green: #22c55e;
            --border: rgba(255, 255, 255, 0.05);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            min-height: 100vh;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 8px;
            color: var(--text-muted); text-decoration: none;
            margin-bottom: 24px; font-size: 0.9rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }

        .info-card {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 20px; padding: 20px;
        }

        .info-label { font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; }
        .info-value { font-size: 1.25rem; font-weight: 700; margin: 0; }

        .status-dot { display: inline-block; height: 10px; width: 10px; border-radius: 50%; margin-right: 6px; }
        .status-active { background-color: var(--accent-green); box-shadow: 0 0 10px rgba(34, 197, 94, 0.4); }

        .chart-container {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; padding: 24px; margin-bottom: 24px;
        }

        .chart-title { margin: 0; font-size: 1.1rem; font-weight: 600; }

        .mini-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px; margin-bottom: 24px;
        }

        .canvas-wrapper-main { position: relative; height: 40vh; min-height: 300px; width: 100%; }
        .canvas-wrapper-mini { position: relative; height: 20vh; min-height: 180px; width: 100%; }

        .table-container {
            background: var(--card); border: 1px solid var(--border);
            border-radius: 24px; padding: 24px; overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid var(--border); }
        td { padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.02); font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="container">
    <a href="javascript:history.back()" class="back-btn">← Назад к списку</a>

    <div class="info-grid">
        <div class="info-card">
            <span class="info-label">Устройство</span>
            <h3 class="info-value">{{ $device->deviceName }}</h3>
        </div>
        <div class="info-card">
            <span class="info-label">DevEUI / ID</span>
            <h3 class="info-value">{{ $device->devEUI }}</h3>
        </div>
        <div class="info-card">
            <span class="info-label">Статус</span>
            <h3 class="info-value">
                <span class="status-dot status-active"></span>
                Online
            </h3>
        </div>
        <div class="info-card">
            <span class="info-label">Локация</span>
            <h3 class="info-value">
                @if($device->location)
                    <a href="{{ $device->location }}" target="_blank" style="color: var(--primary); text-decoration: none;">На карте</a>
                @else
                    Нет данных
                @endif
            </h3>
        </div>
    </div>

    <div id="chartsSection">
        <div class="chart-container">
            <div class="chart-header"><h2 class="chart-title">Общая динамика</h2></div>
            <div class="canvas-wrapper-main"><canvas id="mainChart"></canvas></div>
        </div>
        <div class="mini-charts-grid" id="miniChartsGrid"></div>
    </div>

    <div class="table-container">
        <h2 class="chart-title" style="margin-bottom: 16px;">История данных</h2>
        <table id="historyTable">
            <thead>
            <tr id="tableHeader">
                <th>Время</th>
            </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table>
    </div>
</div>

<script>
    // Laravel'dan kelgan ma'lumotni to'g'ri formatga o'tkazamiz
    // API'dan kelgan har bir $device->data ichida aslida {datum: {data: {...}}} bor
    const rawData = @json($device->data).map(item => {
        return {
            timestamp: item.created_at,
            values: (item.datum && item.datum.data) ? item.datum.data : item.data
        };
    }).reverse(); // Eng oxirgisi oxirida bo'lishi uchun (chart uchun)

    const labels = rawData.map(d => new Date(d.timestamp).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}));

    // Mavjud sensorlarni aniqlash
    const availableKeys = [];
    if (rawData.length > 0) {
        const firstData = rawData[rawData.length - 1].values;
        if ('temperature' in firstData) availableKeys.push({key: 'temperature', label: 'Температура', unit: '°C', color: '#f43f5e'});
        if ('moisture' in firstData) availableKeys.push({key: 'moisture', label: 'Влажность', unit: '%', color: '#3b82f6'});
        if ('electricity' in firstData) availableKeys.push({key: 'electricity', label: 'Проводимость', unit: 'µS/cm', color: '#eab308'});
        if ('illumination' in firstData) availableKeys.push({key: 'illumination', label: 'Освещенность', unit: 'Lux', color: '#a855f7'});
        if ('depth' in firstData) availableKeys.push({key: 'depth', label: 'Глубина', unit: 'м', color: '#06b6d4'});
    }

    // 1. Jadvalni to'ldirish
    const tableHeader = document.getElementById('tableHeader');
    availableKeys.forEach(s => {
        const th = document.createElement('th');
        th.innerText = `${s.label} (${s.unit})`;
        th.style.textAlign = 'center';
        tableHeader.appendChild(th);
    });

    const tableBody = document.getElementById('tableBody');
    [...rawData].reverse().slice(0, 20).forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${new Date(row.timestamp).toLocaleString()}</td>`;
        availableKeys.forEach(s => {
            const val = row.values[s.key] !== undefined ? row.values[s.key] : '-';
            tr.innerHTML += `<td style="text-align:center; color:${s.color}; font-weight:600">${val}</td>`;
        });
        tableBody.appendChild(tr);
    });

    // 2. Kichik Chartlarni yaratish
    const miniChartsGrid = document.getElementById('miniChartsGrid');
    availableKeys.forEach(s => {
        const container = document.createElement('div');
        container.className = 'chart-container';
        container.innerHTML = `
            <div class="chart-header">
                <h2 class="chart-title" style="color:${s.color}">${s.label}</h2>
            </div>
            <div class="canvas-wrapper-mini"><canvas id="chart-${s.key}"></canvas></div>
        `;
        miniChartsGrid.appendChild(container);

        new Chart(document.getElementById(`chart-${s.key}`).getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: rawData.map(d => d.values[s.key]),
                    borderColor: s.color,
                    backgroundColor: s.color + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });
    });

    // 3. Umumiy Asosiy Chart
    const datasets = availableKeys.map(s => ({
        label: s.label,
        data: rawData.map(d => d.values[s.key]),
        borderColor: s.color,
        tension: 0.4,
        yAxisID: (s.key === 'electricity' || s.key === 'illumination') ? 'y1' : 'y'
    }));

    new Chart(document.getElementById('mainChart').getContext('2d'), {
        type: 'line',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { type: 'linear', position: 'left', grid: { color: 'rgba(255,255,255,0.05)' } },
                y1: { type: 'linear', position: 'right', grid: { display: false } }
            }
        }
    });
</script>

</body>
</html>
