<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lora EM500-SMTC-868M</title>
    <style>
        :root {
            --primary: #4a90e2;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 10px;
            box-sizing: border-box;
        }

        .dashboard {
            background: var(--card);
            padding: 1.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
            border: 1px solid #334155;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0 0 5px 0;
            font-size: 1.25rem;
            letter-spacing: 0.5px;
        }

        .status-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .status-dot {
            height: 8px;
            width: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 8px #22c55e;
        }

        .metric-card {
            background: #334155;
            padding: 16px;
            border-radius: 18px;
            margin-bottom: 12px;
            transition: transform 0.1s;
        }

        .metric-card:active {
            transform: scale(0.98);
        }

        .label {
            font-size: 0.8rem;
            color: #94a3b8;
            display: block;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .value {
            font-size: 1.8rem;
            font-weight: 800;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .progress-bg {
            background: #0f172a;
            height: 8px;
            border-radius: 10px;
            margin-top: 12px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .temp-color { background: linear-gradient(90deg, #f43f5e, #fb7185); }
        .moist-color { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .elec-color { background: linear-gradient(90deg, #eab308, #facc15); }

        .footer {
            text-align: center;
            font-size: 0.7rem;
            color: #64748b;
            margin-top: 20px;
            border-top: 1px solid #334155;
            padding-top: 15px;
        }

        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.5); }
            100% { box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        }

        #error-msg {
            color: #ef4444;
            font-size: 0.8rem;
            text-align: center;
            display: none;
            background: rgba(239, 68, 68, 0.1);
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="header">
        <h2 id="device-name">Соединение...</h2>
        <div class="status-container">
            <span class="status-dot pulse" id="indicator"></span>
            <small id="dev-eui">Поиск устройства...</small>
        </div>
    </div>

    <p id="error-msg">Ошибка обновления данных!</p>

    <div class="metric-card">
        <span class="label">🌡️ Температура</span>
        <div class="value"><span id="temp-val">--</span><small style="font-size: 1rem;">°C</small></div>
        <div class="progress-bg"><div id="temp-fill" class="progress-fill temp-color"></div></div>
    </div>

    <div class="metric-card">
        <span class="label">💧 Влажность</span>
        <div class="value"><span id="moist-val">--</span><small style="font-size: 1rem;">%</small></div>
        <div class="progress-bg"><div id="moist-fill" class="progress-fill moist-color"></div></div>
    </div>

    <div class="metric-card">
        <span class="label">⚡ Электричество</span>
        <div class="value"><span id="elec-val">--</span><small style="font-size: 1rem;">kWh</small></div>
        <div class="progress-bg"><div id="elec-fill" class="progress-fill elec-color"></div></div>
    </div>

    <div class="footer">
        Обновлено: <span id="last-update">--:--:--</span>
    </div>
</div>

<script>
    const API_URL = 'https://lora.nmtu.uz/api/lora/get';

    async function fetchData() {
        try {
            const response = await fetch(`${API_URL}?nocache=${Date.now()}`);
            if (!response.ok) throw new Error();
            const data = await response.json();
            const actualData = Array.isArray(data) ? data[0] : data;
            updateUI(actualData);
            document.getElementById('error-msg').style.display = 'none';
        } catch (error) {
            console.error('API Error');
            document.getElementById('error-msg').style.display = 'block';
            document.getElementById('indicator').style.backgroundColor = '#ef4444';
            document.getElementById('indicator').classList.remove('pulse');
        }
    }

    function updateUI(data) {
        if (!data) return;

        document.getElementById('device-name').innerText = data.deviceName || "EM500-SMTC";
        document.getElementById('dev-eui').innerText = "EUI: " + (data.devEUI || "---");

        const t = data.temperature ?? 0;
        const m = data.moisture ?? 0;
        const e = data.electricity ?? 0;

        document.getElementById('temp-val').innerText = t;
        document.getElementById('moist-val').innerText = m;
        document.getElementById('elec-val').innerText = e;

        // Расчет прогресс-бара (Температура 0-50°C)
        const tWidth = Math.min(Math.max((t / 50) * 100, 0), 100);
        document.getElementById('temp-fill').style.width = tWidth + "%";
        document.getElementById('moist-fill').style.width = Math.min(m, 100) + "%";
        document.getElementById('elec-fill').style.width = (e > 0 ? 100 : 0) + "%";

        document.getElementById('last-update').innerText = new Date().toLocaleTimeString();
        document.getElementById('indicator').style.backgroundColor = '#22c55e';
        document.getElementById('indicator').classList.add('pulse');
    }

    fetchData();
    setInterval(fetchData, 30000); // 30 секунд
</script>

</body>
</html>
