<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screen AI Overlay</title>
    <style>
        :root {
            --bg-glass: rgba(15, 23, 42, 0.88);
            --border-glass: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --dot-monitoring: #22c55e;
            --dot-paused: #eab308;
            --dot-stopped: #ef4444;
            --dot-analyzing: #3b82f6;
        }

        body {
            margin: 0;
            padding: 12px;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: var(--bg-glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            color: var(--text-main);
            height: 100vh;
            box-sizing: border-box;
            user-select: none;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            border: 1px solid var(--border-glass);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .status-container {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background-color: var(--dot-stopped);
            transition: background-color 0.3s;
        }

        .status-dot.monitoring {
            background-color: var(--dot-monitoring);
            box-shadow: 0 0 8px var(--dot-monitoring);
        }
        .status-dot.paused { background-color: var(--dot-paused); }
        .status-dot.stopped { background-color: var(--dot-stopped); }
        .status-dot.analyzing {
            background-color: var(--dot-analyzing);
            box-shadow: 0 0 8px var(--dot-analyzing);
        }

        .app-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .action-badge {
            font-size: 10px;
            text-transform: uppercase;
            background: rgba(255, 255, 255, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
            color: var(--text-muted);
        }

        .close-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            cursor: pointer;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .close-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .content-box {
            flex: 1;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-glass);
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.4;
            color: #e2e8f0;
            overflow-y: auto;
            word-break: break-word;
        }

        .footer {
            margin-top: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="status-container">
            <div id="statusDot" class="status-dot stopped"></div>
            <span class="app-title">Screen AI</span>
            <span id="actionBadge" class="action-badge">INTERPRET</span>
        </div>
        <button class="close-btn" onclick="window.close()" title="Fechar Overlay">✕</button>
    </div>

    <div id="contentBox" class="content-box">
        Aguardando primeira detecção ou análise...
    </div>

    <div class="footer">
        <span id="stateText">Status: Parado</span>
        <span id="timeText">--:--:--</span>
    </div>

    <script>
        async function updateOverlayData() {
            try {
                // 1. Obter estado do monitor
                const stateRes = await fetch('/api/monitor/state');
                const stateData = await stateRes.json();

                const state = (stateData.state || 'stopped').toLowerCase();
                const dot = document.getElementById('statusDot');
                const stateText = document.getElementById('stateText');
                const actionBadge = document.getElementById('actionBadge');

                dot.className = `status-dot ${state}`;
                stateText.innerText = `Status: ${state.toUpperCase()}`;

                if (stateData.config && stateData.config.action_type) {
                    actionBadge.innerText = stateData.config.action_type.toUpperCase();
                }

                // 2. Obter última resposta do histórico
                const historyRes = await fetch('/api/history?page=1&per_page=1');
                const historyData = await historyRes.json();

                const contentBox = document.getElementById('contentBox');
                const timeText = document.getElementById('timeText');

                if (historyData.items && historyData.items.length > 0) {
                    const last = historyData.items[0];
                    contentBox.innerText = last.response_text || last.error_message || 'Sem conteúdo disponível.';
                    if (last.created_at) {
                        timeText.innerText = new Date(last.created_at).toLocaleTimeString('pt-BR');
                    }
                }
            } catch (e) {
                // Polling silencioso
            }
        }

        setInterval(updateOverlayData, 2000);
        updateOverlayData();
    </script>
</body>
</html>
