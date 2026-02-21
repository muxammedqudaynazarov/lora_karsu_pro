<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lora Devices Dashboard</title>
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
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
            min-height: 100vh;
        }

        /* Barcha qurilmalarni ushlab turuvchi asosiy konteyner */
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
            margin-top: auto;
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
            .dashboard { padding: 16px; }
        }
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

            // Kelayotgan ma'lumot massiv ekanligiga ishonch hosil qilish
            const devices = Array.isArray(data) ? data : [data];

            updateUI(devices);
            globalError.style.display = 'none';
        } catch (error) {
            console.error('Data Fetch Error:', error);
            globalError.style.display = 'block';

            // Xato bo'lganda barcha datchiklardagi yashil chiroqni qizilga o'tkazish
            document.querySelectorAll('.status-dot').forEach(dot => {
                dot.style.backgroundColor = '#f43f5e';
                dot.classList.remove('pulse');
            });
        }
    }

    function updateUI(devices) {
        devices.forEach(device => {
            // Laravel modeli orqali (latestDatum) kelsa yoki to'g'ridan-to'g'ri kelsa ham ishlaydigan mantiq
            const name = device.deviceName || device.name || "Неизвестное устройство";
            const eui = device.devEUI || device.id || "0000";

            // Datchik ko'rsatkichlarini ajratib olish
            const sensorData = device.latest_datum ? device.latest_datum : device;
            const t = sensorData.temperature ?? 0;
            const m = sensorData.moisture ?? 0;
            const e = sensorData.electricity ?? sensorData.value ?? 0; // 'value' deb nomlangan ustun bo'lishi ham mumkin

            // Har bir qurilma uchun unikal ID
            const cardId = `device-${eui}`;
            let card = document.getElementById(cardId);

            // Agar karta hali yaratilmagan bo'lsa (birinchi marta yuklanishi)
            if (!card) {
                card = document.createElement('div');
                card.className = 'dashboard';
                card.id = cardId;

                card.innerHTML = `
                    <div class="header">
                        <h2 class="dev-name">${name}</h2>
                        <div class="status-container">
                            <span class="status-dot pulse"></span>
                            <small class="dev-eui">ID: ${eui}</small>
                        </div>
                    </div>
                    <div class="metric-card">
                        <span class="label">🌡️ Температура</span>
                        <div class="value">
                            <span class="temp-val">--</span>
                            <span class="unit">°C</span>
                        </div>
                        <div class="progress-bg"><div class="progress-fill temp-color temp-fill"></div></div>
                    </div>
                    <div class="metric-card">
                        <span class="label">💧 Влажность</span>
                        <div class="value">
                            <span class="moist-val">--</span>
                            <span class="unit">%</span>
                        </div>
                        <div class="progress-bg"><div class="progress-fill moist-color moist-fill"></div></div>
                    </div>
                    <div class="metric-card">
                        <span class="label">⚡ Электричество</span>
                        <div class="value">
                            <span class="elec-val">--</span>
                            <span class="unit">kWh</span>
                        </div>
                        <div class="progress-bg"><div class="progress-fill elec-color elec-fill"></div></div>
                    </div>
                    <div class="footer">
                        Обновлено в <span class="last-update">--:--:--</span>
                    </div>
                `;
                container.appendChild(card);
            }

            // Endi karta ichidagi ma'lumotlarni topib, yangilab qo'yamiz
            card.querySelector('.dev-name').innerText = name;
            card.querySelector('.temp-val').innerText = t;
            card.querySelector('.moist-val').innerText = m;
            card.querySelector('.elec-val').innerText = e;

            // Progres barlarni hisoblash
            const tWidth = Math.min(Math.max((t / 50) * 100, 0), 100);
            card.querySelector('.temp-fill').style.width = tWidth + "%";
            card.querySelector('.moist-fill').style.width = Math.min(m, 100) + "%";
            card.querySelector('.elec-fill').style.width = (e > 0 ? 100 : 0) + "%";

            card.querySelector('.last-update').innerText = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});

            // Agar aloqa uzilgan bo'lsa qizargan chiroqni qayta yashil qilish
            const indicator = card.querySelector('.status-dot');
            indicator.style.backgroundColor = '#22c55e';
            indicator.classList.add('pulse');
        });
    }

    // Dastlabki ishga tushirish va har 30 soniyada takrorlash
    fetchData();
    setInterval(fetchData, 30000);
</script>

</body>
</html>
