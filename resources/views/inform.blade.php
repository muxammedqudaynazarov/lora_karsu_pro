<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>loraWAN Monitoring</title>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --accent-red: #ef4444;
            --accent-blue: #3b82f6;
            --accent-yellow: #f59e0b;
            --accent-purple: #8b5cf6;
            --accent-cyan: #06b6d4;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .app-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard {
            background: var(--card);
            padding: 24px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            min-height: 400px;
        }

        .dashboard:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        .header { text-align: center; margin-bottom: 24px; }
        .header h2 { margin: 0 0 8px 0; font-size: 1.25rem; font-weight: 700; color: var(--text); }

        .status-container {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 20px;
            gap: 8px;
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .status-dot { height: 8px; width: 8px; background-color: #22c55e; border-radius: 50%; }
        .pulse { animation: pulse-animation 2s infinite; }
        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.4); }
            100% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        }

        .metrics-container { display: flex; flex-direction: column; gap: 12px; flex-grow: 1; justify-content: center; }

        .metric-card {
            background: #f8fafc;
            padding: 16px;
            border-radius: 16px;
            border: 1px solid var(--border);
        }

        .label { font-size: 0.75rem; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 8px; margin-bottom: 6px; text-transform: uppercase; }
        .value { font-size: 1.75rem; font-weight: 800; color: var(--text); }
        .unit { font-size: 0.9rem; color: var(--text-muted); margin-left: 4px; }

        .progress-bg { background: #e2e8f0; height: 8px; border-radius: 10px; margin-top: 12px; }
        .progress-fill { height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }

        .color-temp { background: var(--accent-red); }
        .color-moist { background: var(--accent-blue); }
        .color-elec { background: var(--accent-yellow); }
        .color-illum { background: var(--accent-purple); }
        .color-depth { background: var(--accent-cyan); }

        .footer { text-align: center; font-size: 0.75rem; color: var(--text-muted); margin-top: 20px; border-top: 1px solid var(--border); padding-top: 12px; }
        .geo-link { color: var(--primary); text-decoration: none; font-weight: 600; }

        #global-error-msg {
            display: none; background: #fee2e2; color: #b91c1c; padding: 12px;
            border-radius: 12px; margin-bottom: 20px; text-align: center; border: 1px solid #fecaca;
        }
    </style>
</head>
<body>
<div id="global-error-msg">⚠️ Ошибка обновления данных</div>
<div id="app-container" class="app-container"></div>

<script>
    const API_URL = 'https://wan.nmtu.uz/api/lora/get';
    const container = document.getElementById('app-container');

    async function fetchData() {
        try {
            const response = await fetch(`${API_URL}?nocache=${Date.now()}`);
            const data = await response.json();
            updateUI(Array.isArray(data) ? data : [data]);
            document.getElementById('global-error-msg').style.display = 'none';
        } catch (e) {
            document.getElementById('global-error-msg').style.display = 'block';
        }
    }

    function buildMetricCard(label, icon, value, unit, colorClass, maxVal) {
        let percent = Math.min((parseFloat(value) / maxVal) * 100, 100);
        return `
            <div class="metric-card">
                <span class="label">${icon} ${label}</span>
                <div class="value">${value}<span class="unit">${unit}</span></div>
                <div class="progress-bg"><div class="progress-fill ${colorClass}" style="width: ${percent}%;"></div></div>
            </div>`;
    }

    function updateUI(devices) {
        devices.forEach(device => {
            const sensorData = (device.datum && device.datum.data) ? device.datum.data : {};
            const eui = device.devEUI;
            let metricsHTML = '';

            if (sensorData.temperature !== undefined) metricsHTML += buildMetricCard('Температура', '🌡️', sensorData.temperature, '°C', 'color-temp', 50);
            if (sensorData.moisture !== undefined) metricsHTML += buildMetricCard('Влажность', '💧', sensorData.moisture, '%', 'color-moist', 100);
            if (sensorData.electricity !== undefined) metricsHTML += buildMetricCard('Проводимость', '⚡', sensorData.electricity, 'µS/cm', 'color-elec', 1000);
            if (sensorData.illumination !== undefined) metricsHTML += buildMetricCard('Освещенность', '☀️', sensorData.illumination, 'Lux', 'color-illum', 2000);
            if (sensorData.depth !== undefined) metricsHTML += buildMetricCard('Глубина', '📏', sensorData.depth, 'м', 'color-depth', 5);

            let card = document.getElementById(`device-${eui}`);
            if (!card) {
                card = document.createElement('div');
                card.className = 'dashboard';
                card.id = `device-${eui}`;
                card.onclick = () => window.location.href = `/data/${eui}`;
                container.appendChild(card);
            }

            card.innerHTML = `
                <div class="header">
                    <h2>${device.deviceName || 'Устройство'}</h2>
                    <div class="status-container"><span class="status-dot pulse"></span>ID: ${eui}</div>
                </div>
                <div class="metrics-container">${metricsHTML || '<div style="text-align:center;color:#94a3b8">Нет данных</div>'}</div>
                <div class="footer">Обновлено: ${new Date(device.datum?.created_at).toLocaleTimeString()}
                ${device.location ? ` | <a href="${device.location}" target="_blank" class="geo-link">Местоположение</a>` : ''}</div>`;
        });
    }
    fetchData(); setInterval(fetchData, 15000);
</script>
</body>
</html>
