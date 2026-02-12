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
            --accent-red: #f43f5e;
            --accent-blue: #3b82f6;
            --accent-yellow: #eab308;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 16px; /* Mobil chekkalari uchun bo'shliq */
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent; /* Mobil teginish effektini tozalash */
        }

        .dashboard {
            background: var(--card);
            padding: 20px;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px; /* Kompyuterda juda keng bo'lib ketmaydi */
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h2 {
            margin: 0 0 8px 0;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .status-container {
            display: inline-flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            gap: 8px;
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .status-dot {
            height: 8px;
            width: 8px;
            background-color: #22c55e;
            border-radius: 50%;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 18px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.2s;
        }

        /* Mobil bosish effekti */
        .metric-card:active {
            background: rgba(255, 255, 255, 0.08);
            transform: scale(0.99);
        }

        .label {
            font-size: 0.75rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .value {
            font-size: 2rem;
            font-weight: 800;
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .unit {
            font-size: 1rem;
            font-weight: 500;
            color: #64748b;
        }

        .progress-bg {
            background: #0f172a;
            height: 6px;
            border-radius: 10px;
            margin-top: 14px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .temp-color { background: var(--accent-red); box-shadow: 0 0 10px rgba(244, 63, 94, 0.3); }
        .moist-color { background: var(--accent-blue); box-shadow: 0 0 10px rgba(59, 130, 246, 0.3); }
        .elec-color { background: var(--accent-yellow); box-shadow: 0 0 10px rgba(234, 179, 8, 0.3); }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            color: #475569;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.4); }
            100% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        }

        #error-msg {
            color: #fda4af;
            font-size: 0.8rem;
            text-align: center;
            display: none;
            background: rgba(159, 18, 57, 0.2);
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        /* Kichik ekranlar uchun shriftni moslashtirish */
        @media (max-width: 360px) {
            .value { font-size: 1.6rem; }
            .dashboard { padding: 16px; }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="header">
        <h2 id="device-name">Соединение...</h2>
        <div class="status-container">
            <span class="status-dot pulse" id="indicator"></span>
            <small id="dev-eui">Поиск...</small>
        </div>
    </div>

    <p id="error-msg">⚠️ Ошибка обновления данных</p>

    <div class="metric-card">
        <span class="label">🌡️ Температура</span>
        <div class="value">
            <span id="temp-val">--</span>
            <span class="unit">°C</span>
        </div>
        <div class="progress-bg"><div id="temp-fill" class="progress-fill temp-color"></div></div>
    </div>

    <div class="metric-card">
        <span class="label">💧 Влажность</span>
        <div class="value">
            <span id="moist-val">--</span>
            <span class="unit">%</span>
        </div>
        <div class="progress-bg"><div id="moist-fill" class="progress-fill moist-color"></div></div>
    </div>

    <div class="metric-card">
        <span class="label">⚡ Электричество</span>
        <div class="value">
            <span id="elec-val">--</span>
            <span class="unit">kWh</span>
        </div>
        <div class="progress-bg"><div id="elec-fill" class="progress-fill elec-color"></div></div>
    </div>

    <div class="footer">
        Обновлено в <span id="last-update">--:--:--</span>
    </div>
</div>

<script>
    const API_URL = 'https://lora.nmtu.uz/api/lora/get';

    async function fetchData() {
        try {
            // Mobil tarmoqlarda kesh muammosini oldini olish
            const response = await fetch(`${API_URL}?nocache=${Date.now()}`);
            if (!response.ok) throw new Error();
            const data = await response.json();
            const actualData = Array.isArray(data) ? data[0] : data;
            updateUI(actualData);
            document.getElementById('error-msg').style.display = 'none';
        } catch (error) {
            console.error('API Error');
            document.getElementById('error-msg').style.display = 'block';
            document.getElementById('indicator').style.backgroundColor = '#f43f5e';
            document.getElementById('indicator').classList.remove('pulse');
        }
    }

    function updateUI(data) {
        if (!data) return;

        document.getElementById('device-name').innerText = data.deviceName || "EM500-SMTC";
        document.getElementById('dev-eui').innerText = "ID: " + (data.devEUI || "---");

        const t = data.temperature ?? 0;
        const m = data.moisture ?? 0;
        const e = data.electricity ?? 0;

        // Raqamlarni animatsiya bilan yangilash (ixtiyoriy, oddiy matn almashtirish)
        document.getElementById('temp-val').innerText = t;
        document.getElementById('moist-val').innerText = m;
        document.getElementById('elec-val').innerText = e;

        // Progres barlar (Maksimal qiymatlar: Temp 50, Moist 100)
        const tWidth = Math.min(Math.max((t / 50) * 100, 0), 100);
        document.getElementById('temp-fill').style.width = tWidth + "%";
        document.getElementById('moist-fill').style.width = Math.min(m, 100) + "%";
        document.getElementById('elec-fill').style.width = (e > 0 ? 100 : 0) + "%";

        document.getElementById('last-update').innerText = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        document.getElementById('indicator').style.backgroundColor = '#22c55e';
        document.getElementById('indicator').classList.add('pulse');
    }

    fetchData();
    setInterval(fetchData, 30000);
</script>

</body>
</html>
