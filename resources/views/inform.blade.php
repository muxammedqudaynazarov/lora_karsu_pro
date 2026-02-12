<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lora EM500-SMTC-868M</title>
    <style>
        :root {
            --primary: #4a90e2;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .dashboard {
            background: var(--card);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            width: 380px;
            border: 1px solid #334155;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .status-dot {
            height: 10px;
            width: 10px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            box-shadow: 0 0 8px #22c55e;
        }

        .metric-card {
            background: #334155;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
        }

        .label {
            font-size: 0.9rem;
            color: #94a3b8;
            display: block;
            margin-bottom: 5px;
        }

        .value {
            font-size: 1.6rem;
            font-weight: bold;
        }

        .progress-bg {
            background: #1e293b;
            height: 8px;
            border-radius: 4px;
            margin-top: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 0%;
            transition: width 1.5s cubic-bezier(0.1, 0.7, 1.0, 0.1);
        }

        .temp-color {
            background: #f43f5e;
        }

        .moist-color {
            background: #3b82f6;
        }

        .elec-color {
            background: #eab308;
        }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 20px;
        }

        #error-msg {
            color: #ef4444;
            font-size: 0.8rem;
            text-align: center;
            display: none;
        }

        /* Avvalgi stillar ... */
        .pulse {
            animation: pulse-animation 2s infinite;
        }

        @keyframes pulse-animation {
            0% {
                box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.7);
            }
            100% {
                box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
            }
        }
    </style>
</head>
<body>

<div class="dashboard">
    <div class="header">
        <h2 id="device-name">Соединение...</h2>
        <div id="status-container">
            <span class="status-dot pulse"></span> <small id="dev-eui">Сканирование...</small>
        </div>
    </div>

    <p id="error-msg">Ошибка при получении информации из API!</p>

    <div class="metric-card">
        <span class="label">🌡️ Температура</span>
        <div class="value" id="temp-val">--°C</div>
        <div class="progress-bg">
            <div id="temp-fill" class="progress-fill temp-color"></div>
        </div>
    </div>

    <div class="metric-card">
        <span class="label">💧 Влажность</span>
        <div class="value" id="moist-val">--%</div>
        <div class="progress-bg">
            <div id="moist-fill" class="progress-fill moist-color"></div>
        </div>
    </div>

    <div class="metric-card">
        <span class="label">⚡ Электричество</span>
        <div class="value" id="elec-val">-- kWh</div>
        <div class="progress-bg">
            <div id="elec-fill" class="progress-fill elec-color"></div>
        </div>
    </div>

    <div class="footer">Последнее обновление: <span id="last-update">...</span></div>
</div>
<script>
    const API_URL = 'https://lora.nmtu.uz/api/lora/get';

    async function fetchData() {
        try {
            const response = await fetch(`${API_URL}?t=${new Date().getTime()}`);
            if (!response.ok) throw new Error('Сетевая ошибка');
            const data = await response.json();
            const actualData = Array.isArray(data) ? data[0] : data;
            updateUI(actualData);
            document.getElementById('error-msg').style.display = 'none';
        } catch (error) {
            console.error('Xatolik:', error);
            document.getElementById('error-msg').style.display = 'block';
            document.querySelector('.status-dot').style.backgroundColor = '#ef4444';
        }
    }

    function updateUI(data) {
        if (!data) return;
        document.getElementById('device-name').innerText = data.deviceName || "SMTC";
        document.getElementById('dev-eui').innerText = "EUI: " + (data.devEUI || "---");
        const temp = data.temperature ?? 0;
        const moist = data.moisture ?? 0;
        const elec = data.electricity ?? 0;
        document.getElementById('temp-val').innerText = temp + "°C";
        document.getElementById('moist-val').innerText = moist + "%";
        document.getElementById('elec-val').innerText = elec + " kWh";
        let tempWidth = (temp / 50) * 100;
        document.getElementById('temp-fill').style.width = Math.max(tempWidth, 2) + "%";
        document.getElementById('moist-fill').style.width = Math.max(moist, 2) + "%";
        document.getElementById('elec-fill').style.width = (elec > 0 ? 100 : 0) + "%";
        document.getElementById('last-update').innerText = new Date().toLocaleTimeString();
        document.querySelector('.status-dot').style.backgroundColor = '#22c55e';
    }

    fetchData();
    setInterval(fetchData, 30000);
</script>
</body>
</html>
