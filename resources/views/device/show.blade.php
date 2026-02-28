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
            --accent-purple: #a855f7; /* Yorug'lik uchun yangi rang */
            --accent-cyan: #06b6d4;   /* Chuqurlik uchun yangi rang */
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

        .back-btn:hover { color: var(--text); }

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
        .status-active { background-color: var(--accent-green); box-shadow: 0 0 10px rgba(34, 197, 94, 0.4); }
        .status-inactive { background-color: var(--text-muted); }

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

        .chart-title { margin: 0; font-size: 1.1rem; font-weight: 600; }

        .mini-charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .mini-charts-grid .chart-container { margin-bottom: 0; }

        .canvas-wrapper-main { position: relative; height: 40vh; min-height: 300px; width: 100%; }
        .canvas-wrapper-mini { position: relative; height: 20vh; min-height: 180px; width: 100%; }

        /* Table Section */
        .table-container {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 24px;
            overflow-x: auto;
        }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; padding: 12px 16px; border-bottom: 1px solid var(--border); }
        td { padding: 12px 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.02); font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255, 255, 255, 0.02); }

        @media (max-width: 768px) {
            .info-grid { grid-template-columns: 1fr 1fr; }
            .canvas-wrapper-main { height: 35vh; }
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
                    <a href="{{ $device->location }}" target="_blank" style="color: var(--primary); text-decoration: none;">На карте</a>
                @else
                    Нет данных
                @endif
            </h3>
        </div>
    </div>

    <div class="chart-container" id="main-chart-container" style="display: none;">
        <div class="chart-header">
            <h2 class="chart-title">Общая динамика показателей</h2>
        </div>
        <div class="canvas-wrapper-main">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <div class="mini-charts-grid" id="mini-charts-container">
    </div>

    <div class="table-container" id="table-container" style="display: none;">
        <h2 class="chart-title" style="margin-bottom: 16px;">История данных</h2>
        <table id="data-table">
            <thead>
            <tr>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

</div>

<script>
    // 1. Ma'lumotlarni qabul qilish
    const rawData = @json($device->data);

    if (!rawData || rawData.length === 0) {
        document.getElementById('mini-charts-container').innerHTML = '<div style="color: #94a3b8;">Нет данных для отображения</div>';
    } else {
        document.getElementById('main-chart-container').style.display = 'block';
        document.getElementById('table-container').style.display = 'block';

        // 2. CSS Ranglarni o'qish
        const style = getComputedStyle(document.body);
        const colorText = style.getPropertyValue('--text-muted').trim();
        const colorGrid = style.getPropertyValue('--border').trim();

        // 3. Metrikalar sozlamalari (Datchik tipiga qarab)
        const metricsConfig = {
            temperature: { label: 'Температура', unit: '°C', color: style.getPropertyValue('--accent-red').trim(), icon: '🌡️', axis: 'y' },
            moisture: { label: 'Влажность', unit: '%', color: style.getPropertyValue('--accent-blue').trim(), icon: '💧', axis: 'y' },
            electricity: { label: 'Проводимость', unit: 'µS/cm', color: style.getPropertyValue('--accent-yellow').trim(), icon: '⚡', axis: 'y1' },
            illumination: { label: 'Освещенность', unit: 'Lux', color: style.getPropertyValue('--accent-purple').trim(), icon: '☀️', axis: 'y1' },
            depth: { label: 'Глубина', unit: 'м', color: style.getPropertyValue('--accent-cyan').trim(), icon: '📏', axis: 'y' }
        };

        // 4. Ma'lumotlarni tozalash va bor metrikalarni aniqlash
        const activeMetrics = new Set();
        const processedData = rawData.map(item => {
            // Agar JSON array bo'lsa (yangi kiritilgan usul asosida) obj qilib olamiz
            let parsedData = {};
            if (typeof item.data === 'object' && item.data !== null) {
                parsedData = item.data;
            } else if (typeof item.data === 'string') {
                try { parsedData = JSON.parse(item.data); } catch(e){}
            }

            // Qiymatlarni tekshirib olish (bazadagi ustundan yeki data ichidan)
            const row = {
                created_at: item.created_at,
                temperature: item.temperature ?? parsedData.temperature,
                moisture: item.moisture ?? parsedData.moisture,
                electricity: item.electricity ?? parsedData.electricity,
                illumination: item.illumination ?? parsedData.illumination,
                depth: item.depth ?? parsedData.depth
            };

            // Qaysi metrikalar kelsa, shuni ro'yxatga qo'shamiz
            Object.keys(metricsConfig).forEach(key => {
                if (row[key] !== undefined && row[key] !== null) {
                    activeMetrics.add(key);
                }
            });

            return row;
        });

        const activeMetricsArray = Array.from(activeMetrics); // Masalan: ['illumination'] yoki ['temperature', 'moisture', 'electricity']

        // Global Chart.js sozlamalari
        Chart.defaults.color = colorText;
        Chart.defaults.font.family = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif";
        Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(30, 41, 59, 0.9)';
        Chart.defaults.plugins.tooltip.titleColor = '#f8fafc';
        Chart.defaults.plugins.tooltip.bodyColor = '#f8fafc';

        const labels = processedData.map(item => {
            let date = new Date(item.created_at);
            return date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});
        });

        // 5. KICHIK GRAFIKLARNI YARATISH
        const miniContainer = document.getElementById('mini-charts-container');
        activeMetricsArray.forEach(key => {
            const config = metricsConfig[key];
            const canvasId = `chart_${key}`;

            // HTML chizish
            miniContainer.innerHTML += `
                <div class="chart-container">
                    <div class="chart-header" style="justify-content: flex-start;">
                        <span style="font-size: 1.2rem;">${config.icon}</span>
                        <h2 class="chart-title" style="color: ${config.color}; font-size: 1rem;">${config.label}</h2>
                    </div>
                    <div class="canvas-wrapper-mini">
                        <canvas id="${canvasId}"></canvas>
                    </div>
                </div>
            `;
        });

        // HTML DOM ga yozilgandan so'ng, Chart larni initsializatsiya qilish
        setTimeout(() => {
            const mainDatasets = [];
            let useY1 = false; // O'ng tomon o'qini yoqish/o'chirish uchun

            activeMetricsArray.forEach(key => {
                const config = metricsConfig[key];
                const dataValues = processedData.map(item => item[key]);

                // Asosiy grafik uchun dataset
                mainDatasets.push({
                    label: `${config.label} (${config.unit})`,
                    data: dataValues,
                    borderColor: config.color,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: config.axis
                });

                if (config.axis === 'y1') useY1 = true;

                // Kichik grafikni chizish
                new Chart(document.getElementById(`chart_${key}`).getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: config.label,
                            data: dataValues,
                            borderColor: config.color,
                            backgroundColor: config.color + '15',
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 4,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: {mode: 'index', intersect: false},
                        plugins: { legend: {display: false}, tooltip: {displayColors: false} },
                        scales: {
                            x: {display: false},
                            y: { grid: {color: colorGrid, drawBorder: false}, ticks: {maxTicksLimit: 5} }
                        }
                    }
                });
            });

            // Asosiy (katta) chartni chizish
            const y1Scale = useY1 ? { type: 'linear', position: 'right', grid: {drawOnChartArea: false} } : { display: false };

            new Chart(document.getElementById('mainChart').getContext('2d'), {
                type: 'line',
                data: { labels: labels, datasets: mainDatasets },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    interaction: {mode: 'index', intersect: false},
                    plugins: { legend: {position: 'top', labels: {usePointStyle: true, boxWidth: 8}} },
                    scales: {
                        x: {grid: {color: colorGrid, drawBorder: false}},
                        y: { type: 'linear', position: 'left', grid: {color: colorGrid, drawBorder: false} },
                        y1: y1Scale
                    }
                }
            });
        }, 50);

        // 6. JADVALNI YARATISH (Faqat mavjud ma'lumotlar bilan)
        const theadTr = document.querySelector('#data-table thead tr');
        theadTr.innerHTML = '<th>Время</th>';
        activeMetricsArray.forEach(key => {
            theadTr.innerHTML += `<th style="text-align: center;">${metricsConfig[key].label} (${metricsConfig[key].unit})</th>`;
        });

        const tbody = document.querySelector('#data-table tbody');
        // Oxirgi 20 tasini jadval tepasida ko'rsatish uchun ro'yxatni teskari aylantiramiz
        const tableData = [...processedData].reverse().slice(0, 20);

        tableData.forEach(item => {
            let tr = document.createElement('tr');
            let date = new Date(item.created_at);
            let timeStr = date.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) + ' | ' + date.toLocaleDateString('ru-RU');

            let rowHtml = `<td>${timeStr}</td>`;

            activeMetricsArray.forEach(key => {
                let val = item[key] !== undefined && item[key] !== null ? item[key] : '--';
                rowHtml += `<td style="text-align: center; color: ${metricsConfig[key].color}; font-weight: 600;">${val}</td>`;
            });

            tr.innerHTML = rowHtml;
            tbody.appendChild(tr);
        });
    }
</script>

</body>
</html>
