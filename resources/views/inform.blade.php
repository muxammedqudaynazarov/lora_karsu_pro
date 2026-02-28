<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>loraWAN.nmtu.uz</title>
    <style>
        :root {
            --primary: #4a90e2;
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f8fafc;
            --accent-red: #f43f5e;
            --accent-blue: #3b82f6;
            --accent-yellow: #eab308;
            --accent-purple: #a855f7; /* Yorug'lik uchun yangi rang */
            --accent-cyan: #06b6d4;   /* Chuqurlik uchun yangi rang */
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

        .app-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard {
            background: var(--card);
            padding: 20px;
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-height: 420px; /* Barcha kartalar bir xil bo'yi saqlanishi uchun */
        }

        .dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7);
            border-color: rgba(255, 255, 255, 0.1);
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

        /* Metriclar ro'yxati joylashadigan joy */
        .metrics-container {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-grow: 1; /* Metriclar o'rtadagi bo'sh joyni to'ldirib turishi uchun */
            justify-content: center;
        }

        .metric-card {
            background: rgba(255, 255, 255, 0.03);
            padding: 18px;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.2s;
        }

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

        /* Maxsus ranglar */
        .color-temp { background: var(--accent-red); box-shadow: 0 0 10px rgba(244, 63, 94, 0.3); }
        .color-moist { background: var(--accent-blue); box-shadow: 0 0 10px rgba(59, 130, 246, 0.3); }
        .color-elec { background: var(--accent-yellow); box-shadow: 0 0 10px rgba(234, 179, 8, 0.3); }
        .color-illum { background: var(--accent-purple); box-shadow: 0 0 10px rgba(168, 85, 247, 0.3); }
        .color-depth { background: var(--accent-cyan); box-shadow: 0 0 10px rgba(6, 182, 212, 0.3); }

        .footer {
            text-align: center;
            font-size: 0.75rem;
            color: #475569;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .pulse { animation: pulse-animation 2s infinite; }

        @keyframes pulse-animation {
            0% { box-shadow: 0 0 0 0px rgba(34, 197, 94, 0.4); }
            100% { box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
        }

        #global-error-msg {
            color: #fda4af;
            font-size: 0.9rem;
            text-align: center;
            display: none;
            background: rgba(159, 18, 57, 0.2);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 24px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        @media (max-width: 360px) {
            .value { font-size: 1.6rem; }
            .dashboard { padding: 16px; min-height: auto; }
        }

        .geo-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin-left: 8px;
            transition: opacity 0.2s;
        }

        .geo-link:hover { opacity: 0.8; }
    </style>
</head>
<body>
<div id="global-error-msg">⚠️ Ошибка подключения к серверу или обновления данных</div>
<div id="app-container" class="app-container">
</div>

<script>
    const API_URL = 'https://wan.nmtu.uz/api/lora/get';
    const container = document.getElementById('app-container');
    const globalError = document.getElementById('global-error-msg');

    async function fetchData() {
        try {
            const response = await fetch(`${API_URL}?nocache=${Date.now()}`);
            if (!response.ok) throw new Error('API Error');
            const data = await response.json();
            const devices = Array.isArray(data) ? data : [data];
            updateUI(devices);
            globalError.style.display = 'none';
        } catch (error) {
            console.error('Data Fetch Error:', error);
            globalError.style.display = 'block';
            document.querySelectorAll('.status-dot').forEach(dot => {
                dot.style.backgroundColor = '#f43f5e';
                dot.classList.remove('pulse');
            });
        }
    }

    // Yordamchi funksiya: Maxsus parametrlar asosida bitta metrika kartasining HTML-kodini generatsiya qilish
    function buildMetricCard(label, icon, value, unit, colorClass, maxVal) {
        // qiymat maxVal dan oshib ketsa ham, progress bar 100% dan oshmasligi uchun
        let percent = (parseFloat(value) / maxVal) * 100;
        if (percent < 0) percent = 0;
        if (percent > 100) percent = 100;

        return `
            <div class="metric-card">
                <span class="label">${icon} ${label}</span>
                <div class="value">
                    <span>${value}</span>
                    <span class="unit">${unit}</span>
                </div>
                <div class="progress-bg">
                    <div class="progress-fill ${colorClass}" style="width: ${percent}%;"></div>
                </div>
            </div>
        `;
    }

    function updateUI(devices) {
        devices.forEach(device => {
            const name = device.deviceName || "Неизвестное... ";
            const eui = device.devEUI || "0000";
            const locUrl = device.location || null;

            // API strukturasi: device.datum.data ichida sensor qiymatlari bor
            // Agar datum yoki data bo'lmasa, bo'sh obyekt olamiz
            const sensorData = (device.datum && device.datum.data) ? device.datum.data : {};

            const cardId = `device-${eui}`;

            // Vaqtni olish (datum.created_at dan)
            let timeText = "--:--:--";
            if (device.datum && device.datum.created_at) {
                const dateObj = new Date(device.datum.created_at);
                timeText = dateObj.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
            }

            let metricsHTML = '';

            // 1. Harorat (SMTC qurilmalari uchun)
            if (sensorData.temperature !== undefined) {
                metricsHTML += buildMetricCard('Температура', '🌡️', sensorData.temperature, '°C', 'color-temp', 50);
            }

            // 2. Namlik (SMTC qurilmalari uchun)
            if (sensorData.moisture !== undefined) {
                metricsHTML += buildMetricCard('Влажность', '💧', sensorData.moisture, '%', 'color-moist', 100);
            }

            // 3. Elektr o'tkazuvchanlik (EC)
            if (sensorData.electricity !== undefined) {
                metricsHTML += buildMetricCard('Проводимость', '⚡', sensorData.electricity, 'µS/cm', 'color-elec', 1000);
            }

            // 4. Yorug'lik (LGT-1 qurilmasi uchun)
            if (sensorData.illumination !== undefined) {
                metricsHTML += buildMetricCard('Освещенность', '☀️', sensorData.illumination, 'Lux', 'color-illum', 2000);
            }

            // 5. Chuqurlik (SWL-1 qurilmasi uchun)
            if (sensorData.depth !== undefined) {
                metricsHTML += buildMetricCard('Глубина', '📏', sensorData.depth, 'м', 'color-depth', 5);
            }

            if(metricsHTML === '') {
                metricsHTML = `<div style="text-align:center; color: #64748b; padding: 20px;">Нет данных</div>`;
            }

            // Karta yaratish yoki yangilash
            let card = document.getElementById(cardId);
            if (!card) {
                card = document.createElement('div');
                card.className = 'dashboard';
                card.id = cardId;
                card.onclick = () => { window.location.href = `/data/${eui}`; };
                container.appendChild(card);
            }

            card.innerHTML = `
                <div class="header">
                    <h2 class="dev-name">${name}</h2>
                    <div class="status-container">
                        <span class="status-dot pulse"></span>
                        <small class="dev-eui">ID: ${eui}</small>
                    </div>
                </div>
                <div class="metrics-container">${metricsHTML}</div>
                <div class="footer">
                    Обновлено: <span>${timeText}</span>
                    ${locUrl ? ` | <a href="${locUrl}" target="_blank" class="geo-link" onclick="event.stopPropagation();">Локация</a>` : ''}
                </div>
            `;
        });
    }

    // Dastlabki ishga tushirish va har 15 soniyada yangilash
    fetchData();
    setInterval(fetchData, 15000);
</script>

</body>
</html>
