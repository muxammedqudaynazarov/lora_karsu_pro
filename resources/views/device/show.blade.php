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
            -webkit-tap-highlight-color: transparent;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            margin-bottom: 24px;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: var(--text);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .info-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .info-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: block;
        }

        .info-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        .status-dot {
            display: inline-block;
            height: 10px;
            width: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-active {
            background-color: var(--accent-green);
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);
        }

        .status-inactive {
            background-color: var(--text-muted);
        }

        /* Chart Sections */
        .chart-container {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            margin-bottom: 24px;
        }

        .chart-header {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: space-between;
        }

        .chart-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .mini-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .mini-charts-grid .chart-container {
            margin-bottom: 0;
        }

        .canvas-wrapper-main {
            position: relative;
            height: 40vh;
            min-height: 300px;
            width: 100%;
        }

        .canvas-wrapper-mini {
            position: relative;
            height: 20vh;
            min-height: 180px;
            width: 100%;
        }

        .table-container {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.02);
            font-size: 0.9rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .t-red {
            color: var(--accent-red);
            font-weight: 600;
        }

        .t-blue {
            color: var(--accent-blue);
            font-weight: 600;
        }

        .t-yellow {
            color: var(--accent-yellow);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }

            .canvas-wrapper-main {
                height: 35vh;
            }
        }
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
                <span class="status-dot {{ $device->status == '1' ? 'status-active' : 'status-inactive' }}"></span>
                {{ ucfirst($device->status == '1' ? 'Online' : 'Offline') }}
            </h3>
        </div>
        <div class="info-card">
            <span class="info-label">Локация</span>
            <h3 class="info-value">
                @if($device->location)
                    <a href="{{ $device->location }}" target="_blank"
                       style="color: var(--primary); text-decoration: none;">На карте</a>
                @else
                    Нет данных
                @endif
            </h3>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-header">
            <h2 class="chart-title">Общая динамика показателей</h2>
        </div>
        <div class="canvas-wrapper-main">
            <canvas id="mainChart"></canvas>
        </div>
    </div>
    <div class="mini-charts-grid">
        <div class="chart-container">
            <div class="chart-header" style="justify-content: flex-start;">
                <span style="font-size: 1.2rem;">🌡️</span>
                <h2 class="chart-title" style="color: var(--accent-red); font-size: 1rem;">Температура</h2>
            </div>
            <div class="canvas-wrapper-mini">
                <canvas id="tempChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header" style="justify-content: flex-start;">
                <span style="font-size: 1.2rem;">💧</span>
                <h2 class="chart-title" style="color: var(--accent-blue); font-size: 1rem;">Влажность</h2>
            </div>
            <div class="canvas-wrapper-mini">
                <canvas id="moistChart"></canvas>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-header" style="justify-content: flex-start;">
                <span style="font-size: 1.2rem;">⚡</span>
                <h2 class="chart-title" style="color: var(--accent-yellow); font-size: 1rem;">Проводимость</h2>
            </div>
            <div class="canvas-wrapper-mini">
                <canvas id="elecChart"></canvas>
            </div>
        </div>

    </div>
    <div class="table-container">
        <h2 class="chart-title" style="margin-bottom: 16px;">История данных</h2>
        <table>
            <thead>
            <tr>
                <th>Время</th>
                <th style="text-align: center; width: 15%">Температура (°C)</th>
                <th style="text-align: center; width: 15%">Влажность (%)</th>
                <th style="text-align: center; width: 15%">Проводимость (µS/cm)</th>
            </tr>
            </thead>
            <tbody>
            @foreach($device->data->reverse()->take(20) as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('H:i | d.m.Y') }}</td>
                    <td class="t-red" style="text-align: center">{{ $item->temperature }}</td>
                    <td class="t-blue" style="text-align: center">{{ $item->moisture }}</td>
                    <td class="t-yellow" style="text-align: center">{{ $item->electricity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

</div>

<script>
    const rawData = @json($device->data);

    // CSS ranglarni o'qish
    const style = getComputedStyle(document.body);
    const colorRed = style.getPropertyValue('--accent-red').trim();
    const colorBlue = style.getPropertyValue('--accent-blue').trim();
    const colorYellow = style.getPropertyValue('--accent-yellow').trim();
    const colorText = style.getPropertyValue('--text-muted').trim();
    const colorGrid = style.getPropertyValue('--border').trim();
    const colorBg = style.getPropertyValue('--card').trim();

    // Data tahlili
    const labels = rawData.map(item => {
        let date = new Date(item.created_at);
        return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
    });
    const tempData = rawData.map(item => item.temperature);
    const moistData = rawData.map(item => item.moisture);
    const elecData = rawData.map(item => item.electricity);

    // Global Chart sozlamalari
    Chart.defaults.color = colorText;
    Chart.defaults.font.family = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(30, 41, 59, 0.9)';
    Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
    Chart.defaults.plugins.tooltip.bodyColor = '#f8fafc';

    // ==========================================
    // 1. KATTA UMUMIY CHART (MAIN)
    // ==========================================
    new Chart(document.getElementById('mainChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Температура (°C)',
                    data: tempData,
                    borderColor: colorRed,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y' // Chap o'q
                },
                {
                    label: 'Влажность (%)',
                    data: moistData,
                    borderColor: colorBlue,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y' // Chap o'q
                },
                {
                    label: 'Проводимость (EC)',
                    data: elecData,
                    borderColor: colorYellow,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y1' // O'ng o'q (kattaroq raqamlar uchun)
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {mode: 'index', intersect: false},
            plugins: {
                legend: {position: 'top', labels: {usePointStyle: true, boxWidth: 8}}
            },
            scales: {
                x: {grid: {color: colorGrid, drawBorder: false}},
                y: {
                    type: 'linear', position: 'left',
                    grid: {color: colorGrid, drawBorder: false},
                    title: {display: true, text: 'Темп. / Влажн.'}
                },
                y1: {
                    type: 'linear', position: 'right',
                    grid: {drawOnChartArea: false},
                    title: {display: true, text: 'Проводимость'}
                }
            }
        }
    });

    // ==========================================
    // 2. KICHIK ALOHIDA CHARTLAR UCHUN FUNKSIYA
    // ==========================================
    function createMiniChart(ctx, label, data, color) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: data,
                    borderColor: color,
                    backgroundColor: color + '15', // Shaffofroq bo'yash (15%)
                    borderWidth: 2,
                    pointRadius: 0, // Kichik chartda nuqtalar chalg'itmasligi uchun yashiramiz
                    pointHoverRadius: 4,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {mode: 'index', intersect: false},
                plugins: {
                    legend: {display: false}, // Legend keraksiz
                    tooltip: {displayColors: false}
                },
                scales: {
                    x: {display: false}, // Kichik chartda pastki vaqt yozuvlari kerak emas, toza turadi
                    y: {
                        grid: {color: colorGrid, drawBorder: false},
                        ticks: {maxTicksLimit: 5} // O'qdagi raqamlar sonini kamaytirish
                    }
                }
            }
        });
    }

    createMiniChart(document.getElementById('tempChart').getContext('2d'), 'Темп.', tempData, colorRed);
    createMiniChart(document.getElementById('moistChart').getContext('2d'), 'Влажн.', moistData, colorBlue);
    createMiniChart(document.getElementById('elecChart').getContext('2d'), 'Пров.', elecData, colorYellow);

</script>

</body>
</html>
